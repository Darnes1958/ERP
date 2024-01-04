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
        Schema::connection('other')->create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('barcode',50);
            $table->foreignId('item_type_id')->constrained('item_types')->cascadeOnDelete();
            $table->foreignId('unita_id')->constrained('unitas');
            $table->foreignId('unitb_id')->constrained('unitbs');
            $table->integer('count')->default(0);
            $table->foreignId('company_id')->constrained('companies')->nullable();
            $table->integer('stock1')->default(0);
            $table->integer('stock2')->default(0);
            $table->integer('ret_q1')->default(0);
            $table->integer('ret_q2')->default(0);
            $table->float('price1');
            $table->float('price2')->default(0);
            $table->foreignId('S_quant')->constrained('S_quants')->nullable();
            $table->binary('unitlevel');
            $table->binary('available');
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
