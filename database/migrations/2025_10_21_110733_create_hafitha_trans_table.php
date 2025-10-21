<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (config('database.connections') as $key=>$connection) {
            if ($connection['driver']=='sqlsrv' && !in_array($key,['other','sqlsrv']))
            {
                try {
                    if ( ! Schema::connection($key)->hasTable('dbo.hafitha_trans')) {
                        Schema::connection($key)->create('dbo.hafitha_trans', function (Blueprint $table) {
                            $table->id();
                            $table->foreignIdFor(\App\Models\Hafitha::class)->constrained();
                            $table->string('acc');
                            $table->decimal('kst',12,3);
                            $table->date('ksm_date');
                            $table->string('ksm_notes')->nullable();
                            $table->timestamps();
                        });

                        Schema::connection($key)->table('hafithas', function($table) {
                            $table->boolean('status')->nullable();
                            $table->boolean('auto')->nullable();

                        });
                        DB::connection($key)->table('hafithas')->update(['status'=>1,'auto'=>1]);
                    }

                } catch (\Exception $e) {

                    info( $e);
                }

            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hafitha_trans');
    }
};
