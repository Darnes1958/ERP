<?php
namespace App\Livewire\Traits;



use App\Enums\AccLevel;
use App\Models\OurCompany;
use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait PublicTrait {




  public static function ret_spatie_header(){
      return       $headers = [
          'Content-Type' => 'application/pdf',
      ];

  }
  public static function ret_spatie($res,$blade,$arr=[])
  {
      $cus=OurCompany::where('Company',Auth::user()->company)->first();
      \Spatie\LaravelPdf\Facades\Pdf::view($blade,
          ['res'=>$res,'arr'=>$arr,'cus'=>$cus])
          ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
      return public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

  }
    public static function ret_spatie_land($res,$blade,$arr=[])
    {
        \Spatie\LaravelPdf\Facades\Pdf::view($blade,
            ['res'=>$res,'arr'=>$arr])
            ->landscape()
            ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
        return public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

    }

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




}
