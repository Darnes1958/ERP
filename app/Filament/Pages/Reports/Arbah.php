<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\widget\RebhMonth;
use App\Models\Sell;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
    return Auth::user()->hasRole('admin');
  }

    protected static string $view = 'filament.pages.reports.arbah';

  protected ?string $heading="";

  public $year;
  public function mount(){
    $year=2024;
   $this->form->fill([
       'year' => $year,
   ]);
  }
public function form(Form $form): Form
{
    return $form
        ->schema([
           Select::make('year')
            ->options(Sell::selectraw('distinct year(order_date) as year')->pluck('year','year'))
            ->label('السنه')
            ->preload()
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state){
                $this->year=$state;
                $this->dispatch('updateyear',year: $this->year);
            })

        ])->columns(4);
}

    protected function getFooterWidgets(): array
  {
    return [

      RebhMonth::make([
        'year'=>$this->year,
      ]),



    ];
  }


}
