<?php

namespace App\Http\Controllers;

use App\Exports\CustTranExl;
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
}
