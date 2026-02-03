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
                Schema::connection($key)->table('dbo.inventories', function (Blueprint $table) {
                    $table->decimal('its_value',10,2)->after('difference')->default(0);
                    $table->foreignIdFor(\App\Models\Place_stock::class)->after('place_id')->default(0);

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
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('its_value');
            $table->dropColumn('place_stock_id');
        });
    }
};
