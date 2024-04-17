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
    return redirect(route('filament.admin.auth.login'));

});

Route::controller(\App\Http\Controllers\PdfController::class)->group(function (){
    route::get('/pdfbuy/{id}', 'PdfBuy')->name('pdfbuy') ;
    route::get('/pdfsell/{id}', 'PdfSell')->name('pdfsell') ;
    route::get('/pdfklasa/{repDate1?},{repDate2?}', 'PdfKlasa')->name('pdfklasa') ;
    route::get('/pdfdaily/{repDate1?},{repDate2?}', 'PdfDaily')->name('pdfdaily') ;
});
