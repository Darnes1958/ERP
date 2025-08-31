<?php

namespace App\Http\Controllers;

use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Cust_tran;
use App\Models\Masr_view;
use App\Models\OurCompany;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Salarytran;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Supp_tran;
use App\Models\Tar_buy;
use App\Models\Tar_sell;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PdfController extends Controller
{
    use PublicTrait;
    public function PdfRepMak(){

        $RepDate=date('Y-m-d');
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $res=Place_stock::
        withSum('Item as buy_cost',DB::raw('stock1 * price_buy'))
            ->withSum('Item as sell_cost',DB::raw('stock1 * price1'))
            ->where('stock1','!=',0)->get();


        $html = view('PDF.pdf-mak',
            ['res'=>$res,'cus'=>$cus,'RepDate'=>$RepDate])->toArabicHTML();

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
    public function PdfCusTtran(Request $request){

    $RepDate=date('Y-m-d');
    $cus=OurCompany::where('Company',Auth::user()->company)->first();
    $RepTable=Cust_tran::
    where('customer_id',$request->cust_id)
      ->where('repDate','>=',$request->tran_date)->get();
    $mden=Cust_tran::where('customer_id',$request->cust_id)->where('repDate','>=',$request->tran_date)->sum('mden');
    $daen=Cust_tran::where('customer_id',$request->cust_id)->where('repDate','>=',$request->tran_date)->sum('daen');
    $raseed=$mden-$daen;

    $html = view('PDF.pdf-jeha-tran',
      ['RepTable'=>$RepTable,'cus'=>$cus,'RepDate'=>$RepDate,'tran_date'=>$request->tran_date,
        'mden'=>$mden,'daen'=>$daen,'raseed'=>$raseed])->toArabicHTML();

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
    public function PdfSuppTran(Request $request){

    $RepDate=date('Y-m-d');
    $cus=OurCompany::where('Company',Auth::user()->company)->first();
    $RepTable=Supp_tran::
    where('supplier_id',$request->cust_id)
      ->where('repDate','>=',$request->tran_date)->get();
    $mden=Supp_tran::where('supplier_id',$request->cust_id)->where('repDate','>=',$request->tran_date)->sum('mden');
    $daen=Supp_tran::where('supplier_id',$request->cust_id)->where('repDate','>=',$request->tran_date)->sum('daen');
    $raseed=$mden-$daen;

    $html = view('PDF.pdf-supp-tran',
      ['RepTable'=>$RepTable,'cus'=>$cus,'RepDate'=>$RepDate,'tran_date'=>$request->tran_date,
        'mden'=>$mden,'daen'=>$daen,'raseed'=>$raseed])->toArabicHTML();

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
    public function PdfKlasa($repDate1,$repDate2,$place_id){

        if ($place_id==0) $place_id=null;
        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        $buy=Buy::when($repDate1,function ($q) use($repDate1){
          $q->where('order_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('order_date','<=',$repDate2);
          })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
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
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
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
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->join('price_types','price_type_id','price_types.id')
            ->leftjoin('accs','acc_id','accs.id')
            ->leftjoin('kazenas','kazena_id','kazenas.id')
            ->where('imp_exp',0)
            ->selectRaw('rec_who,price_types.name,accs.name accName,kazenas.name kazenaName,0 as exp,sum(recsupps.val) as val')
          ->groupby('rec_who','price_types.name','accs.name','kazenas.name');

        $supp=Recsupp::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->join('price_types','price_type_id','price_types.id')
          ->leftjoin('accs','acc_id','accs.id')
          ->leftjoin('kazenas','kazena_id','kazenas.id')
          ->where('imp_exp',1)
          ->selectRaw('rec_who,price_types.name,accs.name accName,kazenas.name kazenaName,sum(recsupps.val) as exp,0 as val')
          ->groupby('rec_who','price_types.name','accs.name','kazenas.name')
            ->union($supp1)->get();

        $cust1=Receipt::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->join('price_types','price_type_id','price_types.id')
            ->leftjoin('accs','acc_id','accs.id')
            ->leftjoin('kazenas','kazena_id','kazenas.id')
            ->where('imp_exp',0)
            ->selectRaw('rec_who,price_types.name,accs.name accName,kazenas.name kazenaName,0 as exp,sum(receipts.val) as val')
            ->groupby('rec_who','price_types.name','accs.name','kazenas.name');

        $cust=Receipt::when($repDate1,function ($q) use($repDate1){
          $q->where('receipt_date','>=',$repDate1);
        })
          ->when($repDate2,function ($q) use($repDate2){
            $q->where('receipt_date','<=',$repDate2);
          })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->join('price_types','price_type_id','price_types.id')
            ->leftjoin('accs','acc_id','accs.id')
            ->leftjoin('kazenas','kazena_id','kazenas.id')
            ->where('imp_exp',1)
            ->selectRaw('rec_who,price_types.name,accs.name accName,kazenas.name kazenaName,sum(receipts.val) as exp,0 as val')
            ->groupby('rec_who','price_types.name','accs.name','kazenas.name')
            ->union($cust1)->get();

        $masr=Masr_view::when($repDate1,function ($q) use($repDate1){
            $q->where('masr_date','>=',$repDate1);
        })
            ->when($repDate2,function ($q)  use($repDate2){
                $q->where('masr_date','<=',$repDate2);
            })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->selectRaw('name, acc_name,sum(val) as val')
            ->groupBy('name','acc_name')->get();
        $salary=Salarytran::join('salaries','salarytrans.salary_id','salaries.id')
        ->when($repDate1,function ($q) use($repDate1){
            $q->where('tran_date','>=',$repDate1);
        })
            ->when($repDate2,function ($q)  use($repDate2){
                $q->where('tran_date','<=',$repDate2);
            })
            ->when($place_id,function ($q) use($place_id){
                return $q->where('place_id',$place_id);
            })

            ->selectRaw('tran_type,sum(val) as val')
            ->groupBy('tran_type')->get();


      $tar_sell=Tar_sell::whereBetween('tar_date',[$repDate1,$repDate2])
        ->selectRaw('tar_date,sum(sub_tot) as sub_tot')
        ->groupBy('tar_date')->get();
      $tar_buy=Tar_buy::whereBetween('tar_date',[$repDate1,$repDate2])
        ->selectRaw('tar_date,sum(sub_tot) as sub_tot')
        ->groupBy('tar_date')->get();
        if ($place_id) $place_name=Place::find($place_id)->name;
        else $place_name=' ';



        return Response::download(self::ret_spatie($buy,
            'PDF.pdf-klasa',['SellTable'=>$sell,'SuppTable'=>$supp,'CustTable'=>$cust,
                'RepDate1'=>$repDate1,'RepDate2'=>$repDate2,'masr'=>$masr,'salary'=>$salary,
                'tar_buy'=>$tar_buy,'tar_sell'=>$tar_sell,
                'place_name'=>$place_name],

        ), 'filename.pdf', self::ret_spatie_header());



    }
    public function PdfDaily(Request $request){


        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        if ($request->repDate1 && !$request->repDate2)
            $buy=Buy::where('order_date','>=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate2 && !$request->repDate1)
            $buy=Buy::where('order_date','=<',$request->repDate1)->get()->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            });
        if ($request->repDate1 && $request->repDate2)
            $buy=Buy::whereBetween('order_date',[$request->repDate1,$request->repDate2])->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();


        if ($request->repDate1 && !$request->repDate2)
            $sell=Sell::where('order_date','>=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate2 && !$request->repDate1)
            $sell=Sell::where('order_date','<=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate1 && $request->repDate2)
            $sell=Sell::whereBetween('order_date',[$request->repDate1,$request->repDate2])->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();

        if ($request->repDate1 && !$request->repDate2)
            $supp=Recsupp::where('receipt_date','>=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate2 && !$request->repDate1)
            $supp=Recsupp::where('receipt_date','<=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate1 && $request->repDate2)
            $supp=Recsupp::whereBetween('receipt_date',[$request->repDate1,$request->repDate2])->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();

        if ($request->repDate1 && !$request->repDate2)
            $cust=Receipt::where('receipt_date','>=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate2 && !$request->repDate1)
            $cust=Receipt::where('receipt_date','<=',$request->repDate1)->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();
        if ($request->repDate1 && $request->repDate2)
            $cust=Receipt::whereBetween('receipt_date',[$request->repDate1,$request->repDate2])->when($request->place_id,function ($q) use($request){
                return $q->where('place_id',$request->place_id);
            })->get();

        if ($request->place_id!=null) $place_name=Place::find($request->place_id)->name;
        else $place_name=' ';


        return Response::download(self::ret_spatie($buy,
            'PDF.pdf-daily',['SellTable'=>$sell,'SuppTable'=>$supp,'CustTable'=>$cust,
                'RepDate1'=>$request->repDate1,'RepDate2'=>$request->repDate2,
                'place_name'=>$place_name],

        ), 'filename.pdf', self::ret_spatie_header());


    }
}
