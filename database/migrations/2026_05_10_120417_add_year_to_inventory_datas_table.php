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
        foreach (config('database.connections') as $key => $connection) {
            if ($connection['driver'] == 'sqlsrv' && !in_array($key, ['other', 'sqlsrv'])) {
                try {
                    Schema::connection($key)->table('dbo.inventory_datas', function (Blueprint $table) {
                        $table->integer('year')->default(2025);
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
        Schema::table('inventory_datas', function (Blueprint $table) {
            //
        });
    }
};
