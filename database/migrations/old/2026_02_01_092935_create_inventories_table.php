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
                    Schema::connection($key)->create('dbo.inventories', function (Blueprint $table) {
                        $table->id();
                        $table->decimal('book_balance',10,2);
                        $table->decimal('actual_balance',10,2);
                        $table->decimal('difference',10,2);
                        $table->foreignIdFor(\App\Models\InventoryData::class);
                        $table->foreignIdFor(\App\Models\Place::class);
                        $table->foreignIdFor(\App\Models\Item::class);
                        $table->foreignIdFor(\App\Models\User::class);
                        $table->timestamps();
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
        Schema::dropIfExists('inventories');
    }
};
