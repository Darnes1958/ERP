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
                    Schema::connection($key)->create('dbo.inventory_datas', function (Blueprint $table) {
                        $table->id();
                        $table->string('data');
                        $table->string('notes')->nullable();
                        $table->boolean('active')->default(true);
                        $table->foreignIdFor(\App\Models\User::class);
                        $table->timestamps();
                        $table->date('end_at')->nullable();
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
        Schema::dropIfExists('inventory_datas');
    }
};
