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
        Schema::connection('other')->create('tar_buys', function (Blueprint $table) {
            $table->id();
            $table->date('tar_date');
            $table->foreignId('buy_id')->constrained('buys')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('q1');
            $table->integer('q2')->default(0);
            $table->float('p1');
            $table->float('p2')->default(0);
            $table->float('sub_tot');
            $table->string('notes')->nullable();
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tar_buys');
    }
};
