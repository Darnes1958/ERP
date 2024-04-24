<?php
namespace App\Livewire\Traits;



use App\Models\Rent;
use App\Models\Renttran;
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
    public function TarseedRents(){
        $res=Rent::all();
        foreach ($res as $item)
            Rent::find($item->id)->update([
                'raseed'=>
                    Renttran::where('rent_id',$item->id)->where('tran_type','إيجار')->sum('val')-
                    Renttran::where('rent_id',$item->id)->where('tran_type','سحب')->sum('val'),
            ]);
    }


}
