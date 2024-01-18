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
        Schema::connection('other')->create('recsupps', function (Blueprint $table) {
          $table->id();
          $table->date('receipt_date');
          $table->foreignId('supplier_id')->constrained('suppliers');
          $table->foreignId('buy_id')->constrained('buys')->nullable();
          $table->foreignId('price_type_id')->constrained('price_types');
          $table->string('rec_who');
          $table->string('imp_exp');
          $table->float('val');
          $table->string('notes',255)->nullable();
          $table->bigInteger('user_id');
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recsupps');
    }
};
