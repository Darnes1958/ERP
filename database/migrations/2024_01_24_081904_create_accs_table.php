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
        Schema::connection('Hala')->create('accs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('balance',12,3)->default(0);
            $table->decimal('raseed',12,3);
            $table->string('acc')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accs');
    }
};
