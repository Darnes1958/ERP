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
        Schema::connection('other')->create('sell_tran_works', function (Blueprint $table) {
            $table->id();
          $table->foreignId('sell_work_id')->constrained('sell_works')->cascadeOnDelete();
          $table->bigInteger('sell_work_id2');
          $table->foreignId('item_id')->constrained('items')->cascadeOnUpdate();
          $table->foreignId('barcode_id')->constrained('barcodes')->cascadeOnUpdate();
          $table->integer('q1');
          $table->integer('q2')->default(0);
          $table->integer('qs1');
          $table->integer('qs2')->default(0);
          $table->float('price1');
          $table->float('price2')->default(0);
          $table->float('sub_tot');
          $table->foreignId('tar_sell_id')->constrained('tar_sells')->nullable();
          $table->date('profit');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_tran_works');
    }
};