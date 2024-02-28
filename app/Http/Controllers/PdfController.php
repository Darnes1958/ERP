<?php

namespace App\Http\Controllers;

use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\OurCompany;
use App\Models\Sell;
use App\Models\Sell_tran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    public function PdfBuy($id){

        $RepDate=date('Y-m-d');
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $res=Buy::find($id);
        $orderdetail=Buy_tran::where('buy_id',$id)->get();

        $html = view('PDF.rep-order-buy',
            ['res'=>$res,'cus'=>$cus,'RepDate'=>$RepDate,'orderdetail'=>$orderdetail])->toArabicHTML();

        $pdf = PDF::loadHTML($html)->output();
        $headers = array(
            "Content-type" => "application/pdf",
        );
        return response()->streamDownload(
            fn () => print($pdf),
            "invoice.pdf",
            $headers
        );


    }
    public function PdfSell($id){

        $RepDate=date('Y-m-d');
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $res=Sell::find($id);
        $orderdetail=Sell_tran::where('sell_id',$id)->get();

        $html = view('PDF.rep-order-sell',
            ['res'=>$res,'cus'=>$cus,'RepDate'=>$RepDate,'orderdetail'=>$orderdetail])->toArabicHTML();

        $pdf = PDF::loadHTML($html)->output();
        $headers = array(
            "Content-type" => "application/pdf",
        );
        return response()->streamDownload(
            fn () => print($pdf),
            "invoice.pdf",
            $headers
        );


    }
}
