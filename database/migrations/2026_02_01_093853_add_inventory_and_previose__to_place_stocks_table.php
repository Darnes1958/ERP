<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        foreach (config('database.connections') as $key=>$connection) {
            if ($connection['driver'] == 'sqlsrv' && !in_array($key, ['other', 'sqlsrv'])) {
                try {
                    Schema::connection($key)->table('dbo.place_stocks', function (Blueprint $table) {
                        $table->decimal('inventory_balance',10,2)->after('stock2')->default(0);
                        $table->decimal('previous_balance',10,2)->after('inventory_balance')->default(0);

                    });
                } catch (\Exception $e) {
                    info($e);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place_stocks', function (Blueprint $table) {
            $table->dropColumn('inventory_balance');
            $table->dropColumn('previous_balance');
        });
    }
};
