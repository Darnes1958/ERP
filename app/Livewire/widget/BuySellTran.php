<?php

namespace App\Livewire\widget;

use App\Models\BuySell;
use App\Models\Setting;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuySellTran extends BaseWidget
{
    public $sell_id;

    protected static ?string $heading = '';

    public function mount($sell_id): void
    {
        $this->sell_id = $sell_id;
    }

    protected function baseQuery()
    {
        $subBuy = '(CASE WHEN items.two_unit = 1 '
            .'THEN (buy_trans.price_input * buy_sells.q1) + (buy_trans.price_input / NULLIF(items.count, 0) * buy_sells.q2) '
            .'ELSE buy_trans.price_input * buy_sells.q1 END)';

        $subSell = '(CASE WHEN items.two_unit = 1 '
            .'THEN (sell_trans.price1 * buy_sells.q1) + (sell_trans.price2 * buy_sells.q2) '
            .'ELSE sell_trans.price1 * buy_sells.q1 END)';

        return BuySell::query()
            ->where('buy_sells.sell_id', $this->sell_id)
            ->join('buy_trans', function ($join) {
                $join->on('buy_trans.buy_id', '=', 'buy_sells.buy_id')
                    ->on('buy_trans.item_id', '=', 'buy_sells.item_id');
            })
            ->join('sell_trans', function ($join) {
                $join->on('sell_trans.sell_id', '=', 'buy_sells.sell_id')
                    ->on('sell_trans.item_id', '=', 'buy_sells.item_id');
            })
            ->join('items', 'items.id', '=', 'buy_sells.item_id')
            ->with('Item')
            ->select([
                'buy_sells.*',
                'buy_trans.price_input',
                'sell_trans.price1',
                'sell_trans.price2',
                DB::raw("{$subBuy} as sub_buy"),
                DB::raw("{$subSell} as sub_sell"),
                DB::raw("({$subSell}) - ({$subBuy}) as profit"),
            ]);
    }

    public function table(Table $table): Table
    {
        $sumTotal = fn () => Sum::make()->label('الإجمالي')->numeric(2, '.', ',');
        $sumEmpty = fn () => Sum::make()->label('')->numeric(2, '.', ',');

        return $table
            ->query(fn () => $this->baseQuery())
            ->queryStringIdentifier('buy_sells')
            ->columns([
                TextColumn::make('buy_id')
                    ->label('رقم فاتورة الشراء')
                    ->sortable(),
                TextColumn::make('item_id')
                    ->label('رقم الصنف')
                    ->sortable(),
                TextColumn::make('Item.name')
                    ->label('اسم الصنف')
                    ->sortable(),
                TextColumn::make('q1')
                    ->label('الكمية'),
                TextColumn::make('q2')
                    ->label('صغري')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (string $state) {
                        if ($state == '0') {
                            return '';
                        }

                        return $state;
                    }),
                TextColumn::make('price_input')
                    ->label('سعر الشراء')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ','),
                TextColumn::make('price1')
                    ->label('سعر البيع')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ','),
                TextColumn::make('price2')
                    ->label('سعر الصغري')
                    ->visible(Setting::find(Auth::user()->company)->has_two)
                    ->formatStateUsing(function (?string $state) {
                        if ($state == '0' || $state == '0.0' || $state === null) {
                            return '';
                        }

                        return $state;
                    }),
                TextColumn::make('sub_buy')
                    ->label('إجمالي الشراء')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ',')
                    ->summarize($sumTotal()),
                TextColumn::make('sub_sell')
                    ->label('إجمالي البيع')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ',')
                    ->summarize($sumEmpty()),
                TextColumn::make('profit')
                    ->label('هامش الربح')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ',')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->summarize($sumEmpty()),
            ])
            ->emptyStateHeading('لا توجد بيانات ربط مع فواتير الشراء');
    }
}
