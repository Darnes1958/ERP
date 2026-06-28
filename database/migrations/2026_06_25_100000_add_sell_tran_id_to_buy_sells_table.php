<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (config('database.connections') as $key => $connection) {
            if ($connection['driver'] !== 'sqlsrv' || in_array($key, ['other', 'sqlsrv'])) {
                continue;
            }

            try {
                Schema::connection($key)->table('dbo.buy_sells', function (Blueprint $table) {
                    $table->unsignedBigInteger('sell_tran_id')->nullable()->after('sell_id');
                });

                DB::connection($key)->statement('
                    UPDATE bs
                    SET bs.sell_tran_id = st.id
                    FROM dbo.buy_sells bs
                    INNER JOIN dbo.sell_trans st
                        ON st.sell_id = bs.sell_id AND st.item_id = bs.item_id
                    WHERE bs.sell_tran_id IS NULL
                ');
            } catch (\Exception $e) {
                info($e);
            }
        }
    }

    public function down(): void
    {
        foreach (config('database.connections') as $key => $connection) {
            if ($connection['driver'] !== 'sqlsrv' || in_array($key, ['other', 'sqlsrv'])) {
                continue;
            }

            try {
                Schema::connection($key)->table('dbo.buy_sells', function (Blueprint $table) {
                    $table->dropColumn('sell_tran_id');
                });
            } catch (\Exception $e) {
                info($e);
            }
        }
    }
};
