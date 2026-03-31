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
                    Schema::connection($key)->table('dbo.sells', function (Blueprint $table) {
                        $table->decimal('ksm', 12, 3)->after('baky')->default(0);
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
        Schema::table('sells', function (Blueprint $table) {
            $table->dropColumn('ksm');
        });
    }
};
