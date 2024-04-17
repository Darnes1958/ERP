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
        Schema::connection('Hala')->create('masrofats', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Masr_type::class);
            $table->date('masr_date');
            $table->decimal('val',12,3);
            $table->string('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masrofats');
    }
};
