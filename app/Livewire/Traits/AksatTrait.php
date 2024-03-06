<?php
namespace App\Livewire\Traits;



use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;

trait AksatTrait {

  public function TarseedTrans(){
    $res=Salary::all();
    foreach ($res as $item)
      Salary::find($item->id)->update([
        'raseed'=>
          Salarytran::where('salary_id',$item->id)->where('tran_type','مرتب')->sum('val')+
          Salarytran::where('salary_id',$item->id)->where('tran_type','اضافة')->sum('val')-
          Salarytran::where('salary_id',$item->id)->where('tran_type','سحب')->sum('val')-
          Salarytran::where('salary_id',$item->id)->where('tran_type','خصم')->sum('val')
        ]);
  }



}
