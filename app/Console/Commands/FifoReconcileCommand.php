<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Services\FifoReconcileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class FifoReconcileCommand extends Command
{
    protected $signature = 'fifo:reconcile
                            {company? : Company database connection name}
                            {--item=* : Specific item id(s) to reconcile}
                            {--repair : Rebuild buy_sells + qs1 for all sold items (with opening layers if needed)}
                            {--orphans-only : Repair only items with missing buy_sells}
                            {--dry-run : Show gaps without writing}';

    protected $description = 'Rebuild buy_sells and buy_trans.qs1 from purchases and sales (FIFO)';

    public function handle(): int
    {
        $company = $this->argument('company');

        if ($company) {
            if (! array_key_exists($company, Config::get('database.connections'))) {
                $this->error("Unknown connection: {$company}");

                return self::FAILURE;
            }
            $this->runForConnection($company);

            return self::SUCCESS;
        }

        foreach (Config::get('database.connections') as $name => $connection) {
            if (($connection['driver'] ?? '') !== 'sqlsrv' || in_array($name, ['other', 'sqlsrv'])) {
                continue;
            }

            $this->runForConnection($name);
        }

        return self::SUCCESS;
    }

    protected function runForConnection(string $connection): void
    {
        $this->info("=== Company: {$connection} ===");
        $service = new FifoReconcileService($connection);

        try {
            DB::connection($connection)->getPdo();
        } catch (\Throwable $e) {
            $this->warn("  Skipped (cannot connect): {$e->getMessage()}");

            return;
        }

        Config::set('database.default', $connection);

        $this->reportOrphanCounts($service, 'Before');

        if ($this->option('orphans-only')) {
            $this->repairOrphans($service);

            return;
        }

        $itemIds = $this->option('item');

        if ($this->option('dry-run')) {
            if (empty($itemIds)) {
                $itemIds = DB::connection($connection)->table('sell_trans')->distinct()->pluck('item_id')->all();
            }

            $this->table(
                ['item_id', 'name', 'purchased', 'sold', 'gap', 'sum(qs1)'],
                collect($itemIds)->map(function ($itemId) use ($connection, $service) {
                    $purchased = $service->purchasedTotal((int) $itemId);
                    $sold = $service->soldTotal((int) $itemId);
                    $qs = (float) DB::connection($connection)->table('buy_trans')->where('item_id', $itemId)->sum('qs1');
                    $name = Item::on($connection)->find($itemId)?->name ?? '-';

                    return [$itemId, $name, $purchased, $sold, max(0, $sold - $purchased), $qs];
                })->all()
            );

            return;
        }

        if ($this->option('repair') && empty($itemIds)) {
            $result = $service->repairDatabase(true);
            $this->newLine();
            $this->info("  Items repaired: {$result['items_repaired']}");
            $this->reportOrphanCounts($service, 'After');

            return;
        }

        if (empty($itemIds)) {
            $itemIds = DB::connection($connection)->table('buy_trans')->distinct()->pluck('item_id')
                ->merge(DB::connection($connection)->table('sell_trans')->distinct()->pluck('item_id'))
                ->unique()
                ->values()
                ->all();
        }

        $bar = $this->output->createProgressBar(count($itemIds));
        $bar->start();

        $gaps = 0;
        foreach ($itemIds as $itemId) {
            $result = $this->option('repair')
                ? $service->repairItem((int) $itemId, true)
                : $service->reconcileItem((int) $itemId);

            if ($result['unallocated_sales'] > 0) {
                $gaps++;
                $name = Item::on($connection)->find($itemId)?->name ?? $itemId;
                $this->newLine();
                $this->warn("  Item {$itemId} ({$name}): unallocated sales qty = {$result['unallocated_sales']}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('  Reconciled '.count($itemIds)." item(s). Items with remaining gaps: {$gaps}");
        $this->reportOrphanCounts($service, 'After');
    }

    protected function reportOrphanCounts(FifoReconcileService $service, string $label): void
    {
        $this->info("  [{$label}] User SQL orphans (sell+item): ".$service->userOrphanCount());
        $this->info("  [{$label}] Per-line orphans (sell_tran_id): ".$service->sellTranOrphanCount());
    }

    protected function repairOrphans(FifoReconcileService $service): void
    {
        $result = $service->repairOrphans();

        $this->info("  Items repaired: {$result['items_repaired']}");
        $this->reportOrphanCounts($service, 'After');

        if ($result['user_orphans_after'] > 0 || $result['sell_tran_orphans_after'] > 0) {
            $this->warn('  Some orphans remain — run with --repair to rebuild all sold items, or check place_id/barcode on those items.');
        }
    }
}
