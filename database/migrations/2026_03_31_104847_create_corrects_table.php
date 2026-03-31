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
        foreach (config('database.connections') as $key=>$connection) {
            if ($connection['driver'] == 'sqlsrv' && !in_array($key, ['other', 'sqlsrv'])) {
                try {
                    Schema::connection($key)->create('corrects', function (Blueprint $table) {
                        $table->id();
                        $table->string('name');
                        $table->foreignIdFor(\App\Models\Taj::class);
                        $table->string('acc');
                        $table->date('wrong_date');
                        $table->decimal('kst',12,3);
                        $table->integer('status');
                        $table->bigInteger('main_id')->nullable();
                        $table->bigInteger('haf_id');
                        $table->foreignIdFor(\App\Models\User::class);
                        $table->timestamps();
                    });

                } catch (\Exception $e) {
                    info($e);
                }
            }

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrects');
    }
};
