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
        Schema::connection('Hala')->create('mony_trans', function (Blueprint $table) {
            $table->id();
            $table->integer('tran_type');
            $table->foreignIdFor(\App\Models\Kazena::class,'from_kazena_id')->nullable();
            $table->foreignIdFor(\App\Models\Kazena::class,'to_kazena_id')->nullable();
            $table->foreignIdFor(\App\Models\Acc::class,'from_acc_id')->nullable();
            $table->foreignIdFor(\App\Models\Acc::class,'to_acc_id')->nullable();
            $table->date('date');
            $table->decimal('amount',12,3);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mony_trans');
    }
};
