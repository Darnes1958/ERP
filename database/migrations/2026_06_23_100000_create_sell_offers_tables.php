<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (config('database.connections') as $key => $connection) {
            if ($connection['driver'] == 'sqlsrv' && ! in_array($key, ['other', 'sqlsrv'])) {
                try {
                    Schema::connection($key)->create('dbo.sell_offers', function (Blueprint $table) {
                        $table->id();
                        $table->date('order_date')->nullable();
                        $table->unsignedBigInteger('customer_id')->nullable();
                        $table->unsignedBigInteger('price_type_id')->nullable();
                        $table->unsignedBigInteger('place_id')->nullable();
                        $table->boolean('single')->default(true);
                        $table->decimal('tot', 12, 3)->default(0);
                        $table->decimal('total', 12, 3)->default(0);
                        $table->decimal('pay', 12, 3)->default(0);
                        $table->decimal('pay_after', 12, 3)->default(0);
                        $table->decimal('morajeh', 12, 3)->default(0);
                        $table->decimal('rate', 12, 3)->default(0);
                        $table->decimal('differ', 12, 3)->default(0);
                        $table->decimal('cost', 12, 3)->default(0);
                        $table->decimal('baky', 12, 3)->default(0);
                        $table->decimal('ksm', 12, 3)->default(0);
                        $table->date('not_pay_date')->nullable();
                        $table->string('notes')->nullable();
                        $table->unsignedBigInteger('user_id')->nullable();
                        $table->timestamps();
                    });

                    Schema::connection($key)->create('dbo.sell_offer_trans', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('sell_id');
                        $table->unsignedBigInteger('item_id');
                        $table->string('barcode_id')->nullable();
                        $table->decimal('q1', 12, 3)->default(0);
                        $table->decimal('q2', 12, 3)->default(0);
                        $table->decimal('price1', 12, 3)->default(0);
                        $table->decimal('price2', 12, 3)->default(0);
                        $table->decimal('sub_tot', 12, 3)->default(0);
                        $table->unsignedBigInteger('tar_sell_id')->nullable();
                        $table->decimal('profit', 12, 3)->default(0);
                        $table->unsignedBigInteger('user_id')->nullable();
                        $table->timestamps();
                    });

                    Schema::connection($key)->create('dbo.sell_offer_works', function (Blueprint $table) {
                        $table->unsignedBigInteger('id')->primary();
                        $table->date('order_date')->nullable();
                        $table->unsignedBigInteger('customer_id')->nullable();
                        $table->unsignedBigInteger('price_type_id')->nullable();
                        $table->unsignedBigInteger('place_id')->nullable();
                        $table->boolean('single')->default(true);
                        $table->decimal('tot', 12, 3)->default(0);
                        $table->decimal('total', 12, 3)->default(0);
                        $table->decimal('pay', 12, 3)->default(0);
                        $table->decimal('pay_after', 12, 3)->default(0);
                        $table->decimal('morajeh', 12, 3)->default(0);
                        $table->decimal('rate', 12, 3)->default(0);
                        $table->decimal('differ', 12, 3)->default(0);
                        $table->decimal('cost', 12, 3)->default(0);
                        $table->decimal('baky', 12, 3)->default(0);
                        $table->decimal('ksm', 12, 3)->default(0);
                        $table->date('not_pay_date')->nullable();
                        $table->string('notes')->nullable();
                        $table->unsignedBigInteger('user_id')->nullable();
                        $table->timestamps();
                    });

                    Schema::connection($key)->create('dbo.sell_offer_tran_works', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('sell_id');
                        $table->unsignedBigInteger('item_id');
                        $table->string('barcode_id')->nullable();
                        $table->decimal('q1', 12, 3)->default(0);
                        $table->decimal('q2', 12, 3)->default(0);
                        $table->decimal('price1', 12, 3)->default(0);
                        $table->decimal('price2', 12, 3)->default(0);
                        $table->decimal('sub_tot', 12, 3)->default(0);
                        $table->unsignedBigInteger('tar_sell_id')->nullable();
                        $table->decimal('profit', 12, 3)->default(0);
                        $table->unsignedBigInteger('user_id')->nullable();
                        $table->timestamps();
                    });
                } catch (\Exception $e) {
                    info($e);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (config('database.connections') as $key => $connection) {
            if ($connection['driver'] == 'sqlsrv' && ! in_array($key, ['other', 'sqlsrv'])) {
                try {
                    Schema::connection($key)->dropIfExists('dbo.sell_offer_tran_works');
                    Schema::connection($key)->dropIfExists('dbo.sell_offer_works');
                    Schema::connection($key)->dropIfExists('dbo.sell_offer_trans');
                    Schema::connection($key)->dropIfExists('dbo.sell_offers');
                } catch (\Exception $e) {
                    info($e);
                }
            }
        }
    }
};
