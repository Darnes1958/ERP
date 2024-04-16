<?php

namespace App\Http\Controllers;

use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\OurCompany;
use App\Models\Receipt;
use App\Models\Recsupp;
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
    public function PdfKlasa($repDate1,$repDate2){


        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $buy=Buy::when($repDate1,function ($q) use($repDate1){
          $q->where('order_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('order_date','<=',$repDate2);
          })
            ->join('places','place_id','places.id')

            ->selectRaw('places.name, sum(tot) as tot,sum(pay) as pay,sum(baky) as baky')
            ->groupBy('places.name')->get();
        $sell=Sell::when($repDate1,function ($q) use($repDate1){
          $q->where('order_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('order_date','<=',$repDate2);
          })
            ->join('places','place_id','places.id')

            ->selectRaw('places.name, sum(total) as total,sum(pay) as pay,sum(baky) as baky')
            ->groupBy('places.name')->get();

        $supp1=Recsupp::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })

            ->join('price_types','price_type_id','price_types.id')
            ->leftjoin('accs','acc_id','accs.id')
            ->where('imp_exp',0)
            ->selectRaw('rec_who,price_types.name,accs.name accName,0 as exp,sum(recsupps.val) as val')
          ->groupby('rec_who','price_types.name','accs.name');

        $supp=Recsupp::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->join('price_types','price_type_id','price_types.id')
          ->leftjoin('accs','acc_id','accs.id')
          ->where('imp_exp',1)
          ->selectRaw('rec_who,price_types.name,accs.name accName,sum(recsupps.val) as exp,0 as val')
          ->groupby('rec_who','price_types.name','accs.name')
            ->union($supp1)->get();

        $cust1=Receipt::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->join('price_types','price_type_id','price_types.id')

            ->leftjoin('accs','acc_id','accs.id')
            ->where('imp_exp',0)
            ->selectRaw('rec_who,price_types.name,accs.name accName,0 as exp,sum(receipts.val) as val')
            ->groupby('rec_who','price_types.name','accs.name');

        $cust=Receipt::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->join('price_types','price_type_id','price_types.id')
            ->leftjoin('accs','acc_id','accs.id')
            ->where('imp_exp',1)
            ->selectRaw('rec_who,price_types.name,accs.name accName,sum(receipts.val) as exp,0 as val')
            ->groupby('rec_who','price_types.name','accs.name')
            ->union($cust1)->get();

        $html = view('PDF.pdf-klasa',
            ['BuyTable'=>$buy,'SellTable'=>$sell,'SuppTable'=>$supp,'CustTable'=>$cust,
              'cus'=>$cus,'RepDate1'=>$repDate1,'RepDate2'=>$repDate2])->toArabicHTML();

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
