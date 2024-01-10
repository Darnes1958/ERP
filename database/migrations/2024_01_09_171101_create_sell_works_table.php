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
        Schema::connection('other')->create('sell_works', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id2');
            $table->date('order_date');
            $table->foreignId('customer_id')->constrained('Customers');
            $table->foreignId('price_type')->constrained('price_types');
            $table->foreignId('place_id')->constrained('places');
            $table->binary('single');
            $table->float('tot');
            $table->float('pay')->default(0);
            $table->float('pay_after')->default(0);
            $table->float('morajeh')->default(0);
            $table->float('baky');
            $table->date('not_pay_date')->nullable();
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
        Schema::dropIfExists('sell_works');
    }
};
