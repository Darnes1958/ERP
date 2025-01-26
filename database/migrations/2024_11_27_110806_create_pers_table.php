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
        Schema::connection('Hala')->create('pers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_from')->constrained(
                table: 'places'
            );
            $table->foreignId('place_to')->constrained(
                table: 'places'
            );
            $table->date('per_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pers');
    }
};
