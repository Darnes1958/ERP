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
        Schema::connection('other')->create('buy_trans', function (Blueprint $table) {
            $table->id();
            $table->integer('sort');
            $table->foreignId('buy_id')->constrained('buys')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnUpdate();
            $table->foreignId('barcode_id')->constrained('barcodes')->cascadeOnUpdate();
            $table->integer('q1');
            $table->integer('q2')->default(0);
            $table->integer('qs1');
            $table->integer('qs2')->default(0);
            $table->float('price_input');
            $table->float('price_avg');
            $table->float('sub_input');
            $table->float('sub_avg');
            $table->foreignId('tar_buy_id')->constrained('tar_buys')->nullable();
            $table->date('exp_date')->nullable();
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_trans');
    }
};
