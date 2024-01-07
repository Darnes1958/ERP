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
        Schema::create('price_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->binary('buy');
            $table->binary('sell');
            $table->binary('receipt');
            $table->integer('inc_dec');
            $table->integer('rate');
            $table->float('val');
            $table->binary('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_types');
    }
};
