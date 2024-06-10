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
        Schema::connection('Hala')->create('money', function (Blueprint $table) {
            $table->id();
            $table->date('tran_date');
            $table->integer('rec_who');
            $table->bigInteger('price_type_id');
            $table->bigInteger('kazena_id')->nullable();
            $table->bigInteger('kazena2_id')->nullable();
            $table->bigInteger('acc_id')->nullable();
            $table->bigInteger('acc2_id')->nullable();
            $table->decimal('amount',12,3);
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
        Schema::dropIfExists('money');
    }
};
