<?php

namespace App\Services;

use App\Models\Barcode;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\BuySell;
use App\Models\Item;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Sell_tran;
use Illuminate\Database\Eloquent\Builder;

class FifoReconcileService
{
    private const QTY_PRECISION = 3;

    public function __construct(private ?string $connection = null) {}

    protected function roundQty(float $value): float
    {
        $rounded = round($value, self::QTY_PRECISION);

        if (abs($rounded) < pow(10, -self::QTY_PRECISION)) {
            return 0.0;
        }

        return $rounded;
    }

    /**
     * Rebuild buy_sells and buy_trans.qs1 for one item from purchase layers and sell_trans (FIFO).
     *
     * @return array{item_id: int, unallocated_sales: float, fifo_remaining: float, buy_sell_rows: int}
     */
    public function reconcileItem(int $itemId): array
    {
        $layers = $this->query(Buy_tran::class)
            ->where('item_id', $itemId)
            ->orderBy('created_at')
            ->orderBy('buy_id')
            ->get();

        $layerRemaining = [];
        foreach ($layers as $layer) {
            $layerRemaining[$this->layerKey($layer)] = $this->roundQty((float) $layer->q1);
        }

        $this->query(BuySell::class)->where('item_id', $itemId)->delete();

        $sellTrans = $this->query(Sell_tran::class)
            ->where('sell_trans.item_id', $itemId)
            ->join('sells', 'sells.id', '=', 'sell_trans.sell_id')
            ->orderBy('sells.order_date')
            ->orderBy('sell_trans.id')
            ->select('sell_trans.*')
            ->get();

        $unallocated = 0.0;
        $buySellRows = 0;

        foreach ($sellTrans as $sellTran) {
            $remaining = $this->roundQty((float) $sellTran->q1);
            $profit = 0.0;

            foreach ($layers as $layer) {
                if ($remaining <= 0) {
                    break;
                }

                $key = $this->layerKey($layer);
                $available = $layerRemaining[$key] ?? 0.0;
                if ($available <= 0) {
                    continue;
                }

                $alloc = $this->roundQty(min($remaining, $available));
                if ($alloc <= 0) {
                    continue;
                }

                $layerRemaining[$key] = $this->roundQty($available - $alloc);

                $this->create(BuySell::class, [
                    'buy_id' => $layer->buy_id,
                    'sell_id' => $sellTran->sell_id,
                    'sell_tran_id' => $sellTran->id,
                    'item_id' => $itemId,
                    'q1' => $alloc,
                    'q2' => 0,
                ]);
                $buySellRows++;

                $profit += ((float) $sellTran->price1 - (float) $layer->price_input) * $alloc;
                $remaining = $this->roundQty($remaining - $alloc);
            }

            $this->query(Sell_tran::class)->where('id', $sellTran->id)->update(['profit' => round($profit, 3)]);

            if ($remaining > 0) {
                $unallocated = $this->roundQty($unallocated + $remaining);
            }
        }

        foreach ($layers as $layer) {
            $key = $this->layerKey($layer);
            $this->query(Buy_tran::class)
                ->where('buy_id', $layer->buy_id)
                ->where('item_id', $itemId)
                ->update(['qs1' => $this->roundQty($layerRemaining[$key] ?? 0)]);
        }

        return [
            'item_id' => $itemId,
            'unallocated_sales' => $this->roundQty($unallocated),
            'fifo_remaining' => $this->roundQty(array_sum($layerRemaining)),
            'buy_sell_rows' => $buySellRows,
        ];
    }

    /**
     * @param  iterable<int>  $itemIds
     * @return array<int, array>
     */
    public function reconcileItems(iterable $itemIds): array
    {
        $results = [];
        foreach ($itemIds as $itemId) {
            $results[$itemId] = $this->reconcileItem((int) $itemId);
        }

        return $results;
    }

    /**
     * @return array{items: int, with_gaps: int, results: array<int, array>}
     */
    public function reconcileAll(): array
    {
        $itemIds = $this->query(Buy_tran::class)->distinct()->pluck('item_id')
            ->merge($this->query(Sell_tran::class)->distinct()->pluck('item_id'))
            ->unique()
            ->values();

        $results = $this->reconcileItems($itemIds);
        $withGaps = collect($results)->filter(fn (array $r) => $r['unallocated_sales'] > 0)->count();

        return [
            'items' => count($results),
            'with_gaps' => $withGaps,
            'results' => $results,
        ];
    }

    public function createOpeningLayer(int $itemId, float $quantity, ?int $placeId = null): ?Buy_tran
    {
        $quantity = $this->roundQty($quantity);

        if ($quantity <= 0) {
            return null;
        }

        $item = $this->query(Item::class)->find($itemId);
        if (! $item) {
            return null;
        }

        $placeId = $placeId
            ?? $this->query(Place_stock::class)->where('item_id', $itemId)->value('place_id')
            ?? $this->query(Place::class)->value('id');

        if (! $placeId) {
            return null;
        }

        $price = (float) ($item->price_buy ?: 0);
        $sub = round($quantity * $price, 3);

        $buy = $this->create(Buy::class, [
            'supplier_id' => 1,
            'order_date' => now()->toDateString(),
            'place_id' => $placeId,
            'price_type_id' => 1,
            'tot' => $sub,
            'pay' => 0,
            'baky' => $sub,
            'notes' => 'رصيد افتتاحي - إصلاح FIFO',
            'user_id' => auth()->id() ?? 1,
        ]);

        return $this->create(Buy_tran::class, [
            'buy_id' => $buy->id,
            'item_id' => $itemId,
            'barcode_id' => $this->resolveBarcodeId($item),
            'q1' => $quantity,
            'qs1' => $quantity,
            'q2' => 0,
            'qs2' => 0,
            'price_input' => $price,
            'sub_input' => $sub,
            'user_id' => auth()->id() ?? 1,
        ]);
    }

    protected function resolveBarcodeId(Item $item): string|int
    {
        if (! empty($item->barcode)) {
            return $item->barcode;
        }

        $barcode = $this->query(Barcode::class)->where('item_id', $item->id)->value('id');

        return $barcode ?? $item->id;
    }

    public function repairItem(int $itemId, bool $createOpening = true): array
    {
        $result = $this->reconcileItem($itemId);

        if ($createOpening && $this->roundQty($result['unallocated_sales']) > 0) {
            $this->createOpeningLayer($itemId, $result['unallocated_sales']);
            $result = $this->reconcileItem($itemId);
            $result['opening_created'] = true;
        }

        return $result;
    }

    public function purchasedTotal(int $itemId): float
    {
        return $this->roundQty((float) $this->query(Buy_tran::class)->where('item_id', $itemId)->sum('q1'));
    }

    public function soldTotal(int $itemId): float
    {
        return $this->roundQty((float) $this->query(Sell_tran::class)->where('item_id', $itemId)->sum('q1'));
    }

    public function theoreticalFifoRemaining(int $itemId): float
    {
        return max(0, $this->roundQty($this->purchasedTotal($itemId) - $this->soldTotal($itemId)));
    }

    /**
     * When recorded sales exceed purchases, create an opening purchase layer for the gap.
     *
     * @return array{created: bool, quantity: float, buy_id: int|null}
     */
    public function ensureFifoBalance(int $itemId, ?int $placeId = null): array
    {
        $purchased = $this->purchasedTotal($itemId);
        $sold = $this->soldTotal($itemId);

        if ($sold <= $purchased) {
            return ['created' => false, 'quantity' => 0.0, 'buy_id' => null];
        }

        $deficit = $this->roundQty($sold - $purchased);
        if ($deficit <= 0) {
            return ['created' => false, 'quantity' => 0.0, 'buy_id' => null];
        }

        $layer = $this->createOpeningLayer($itemId, $deficit, $placeId);

        return [
            'created' => $layer !== null,
            'quantity' => $deficit,
            'buy_id' => $layer?->buy_id,
        ];
    }

    public function syncItem(int $itemId, ?int $placeId = null): array
    {
        $opening = $this->ensureFifoBalance($itemId, $placeId);
        $result = $this->reconcileItem($itemId);
        $result['opening'] = $opening;

        return $result;
    }

    protected function layerKey(Buy_tran $layer): string
    {
        return $layer->buy_id.'_'.$layer->item_id;
    }

    /** @return Builder */
    protected function query(string $modelClass): Builder
    {
        return $this->connection
            ? $modelClass::on($this->connection)->newQuery()
            : $modelClass::query();
    }

    protected function create(string $modelClass, array $attributes): mixed
    {
        return $this->connection
            ? $modelClass::on($this->connection)->create($attributes)
            : $modelClass::create($attributes);
    }
}
