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
        Schema::create('hafitha_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Taj::class);
            $table->string('acc');
            $table->decimal('kst',12,3);
            $table->date('ksm_date');
            $table->string('ksm_notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hafitha_trans');
    }
};
