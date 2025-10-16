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
                    if ( ! Schema::connection($key)->hasTable('jobs')) {

                        Schema::connection($key)->create('jobs', function (Blueprint $table) {
                            $table->id();
                            $table->string('name');
                            $table->timestamps();
                        });
                        Schema::connection($key)->table('mains', function($table) {
                            $table->bigInteger('job_id')->nullable();
                        });
                        DB::connection($key)->table('jobs')->insert(['name'=>'عام']);

                        DB::connection($key)->table('mains')->update(['job_id'=>1]);
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
        Schema::dropIfExists('jobs');
    }
};
