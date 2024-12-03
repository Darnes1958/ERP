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
        Schema::connection('Hala')->create('per_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Per::class);
            $table->foreignIdFor(\App\Models\Item::class);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('per_trans');
    }
};
