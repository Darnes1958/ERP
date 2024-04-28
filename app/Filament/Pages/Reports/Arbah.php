<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\widget\RebhMonth;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Arbah extends Page implements HasForms,HasActions
{
  use InteractsWithForms,InteractsWithActions;
  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'الارباح';
  protected static ?string $navigationGroup = 'الارباح';
  protected static ?int $navigationSort=2;

  public function chkDate($repDate){
    try {
      Carbon::parse($repDate);
      return true;
    } catch (InvalidFormatException $e) {
      return false;
    }
  }
  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('Admin');
  }

    protected static string $view = 'filament.pages.reports.arbah';

  protected ?string $heading="";

  public $repDate1;
  public $repDate2;
  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();
    $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
  }

  protected function getFooterWidgets(): array
  {
    return [

      RebhMonth::make([
        'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
      ]),



    ];
  }


}
