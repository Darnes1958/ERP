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
        Schema::connection('other')->create('buy_sells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_id')->constrained('buys');
            $table->integer('sell_id');
            $table->integer('sell_id2');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('q1');
            $table->integer('q2');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_sells');
    }
};
