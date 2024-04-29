<?php

namespace App\Http\Controllers;

use App\Exports\AccTranExl;
use App\Exports\CustTranExl;
use App\Exports\KazenaTranExl;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemTranExport;

class ExlController extends Controller
{
  public function ItemTranExl($item_id,$repDate)
  {
    return Excel::download(new ItemTranExport($item_id,$repDate),'item_tran.xlsx');
  }
  public function CustTranExl($cust_id,$repDate)
  {
    return Excel::download(new CustTranExl($cust_id,$repDate),'cust_tran.xlsx');
  }
    public function AccTranExl($acc_id,$repDate1,$repDate2)
    {
        return Excel::download(new AccTranExl($acc_id,$repDate1,$repDate2),'acc_tran.xlsx');
    }
    public function KazenaTranExl($kazena_id,$repDate1,$repDate2)
    {
        return Excel::download(new KazenaTranExl($kazena_id,$repDate1,$repDate2),'kazena_tran.xlsx');
    }
}
