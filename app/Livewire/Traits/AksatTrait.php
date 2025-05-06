<?php
namespace App\Livewire\Traits;



use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;

trait AksatTrait {

  public function RetMonthName($month){
      switch ($month) {
          case 1:
              return 'يناير';
              break;
          case 1:
              $name= 'فبراير';
              break;
          case 1:
              $name= 'مارس';
              break;
          case 1:
              $name= 'ابريل';
              break;
          case 1:
              $name= 'مايو';
              break;
          case 1:
              $name= 'يونيو';
              break;
          case 1:
              $name= 'يويلو';
              break;
          case 1:
              $name= 'اغسطس';
              break;
          case 1:
              $name= 'سبتمبر';
              break;
          case 1:
              $name= 'اكتوبر';
              break;
          case 1:
              $name= 'نوفمبر';
              break;
          case 1:
              $name= 'ديسمبر';
              break;


      }
      return $name;

  }

  public function TarseedTrans(){
    $res=Salary::all();
    foreach ($res as $item)
      Salary::find($item->id)->update([
        'raseed'=>
            Salarytran::where('salary_id',$item->id)->where('tran_type','سحب')->sum('val')+
            Salarytran::where('salary_id',$item->id)->where('tran_type','خصم')->sum('val')-
            Salarytran::where('salary_id',$item->id)->where('tran_type','مرتب')->sum('val')-
            Salarytran::where('salary_id',$item->id)->where('tran_type','اضافة')->sum('val')

        ]);
  }
    public function TarseedRents(){
        $res=Rent::all();
        foreach ($res as $item)
            Rent::find($item->id)->update([
                'raseed'=>
                    Renttran::where('rent_id',$item->id)->where('tran_type','سحب')->sum('val')-
                    Renttran::where('rent_id',$item->id)->where('tran_type','إيجار')->sum('val')
                    ,
            ]);
    }


}
