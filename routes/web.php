<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect(route('filament.market.auth.login'));

});

Route::controller(\App\Http\Controllers\PdfController::class)->group(function (){
    route::get('/pdfbuy/{id}', 'PdfBuy')->name('pdfbuy') ;
    route::get('/pdfsell/{id}', 'PdfSell')->name('pdfsell') ;
    route::get('/pdfklasa/{repDate1?},{repDate2?},{place_id?}', 'PdfKlasa')->name('pdfklasa') ;
    route::get('/pdfdaily/{repDate1?},{repDate2?}', 'PdfDaily')->name('pdfdaily') ;
    route::get('/pdfcusttran/{tran_date?},{cust_id?}', 'PdfCusTtran')->name('pdfcusttran') ;
    route::get('/pdfsupptran/{tran_date?},{cust_id?}', 'PdfSuppTran')->name('pdfsupptran') ;
    route::get('/pdfrepmak', 'PdfRepMak')->name('pdfrepmak') ;
});

Route::controller(\App\Http\Controllers\ExlController::class)->group(function () {
  Route::get('itemtranexl/{item_id?},{repDate?}', 'ItemTranExl')->name('itemtranexl');
  Route::get('custtranexl/{cust_id?},{repDate?}', 'CustTranExl')->name('custtranexl');
  Route::get('acctranexl/{acc_id?},{repDate1?},{repDate2?}', 'AccTranExl')->name('acctranexl');
  Route::get('kazenatranexl/{kazena_id?},{repDate1?},{repDate2?}', 'KazenaTranExl')->name('kazenatranexl');
});
