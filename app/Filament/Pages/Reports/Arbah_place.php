<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\widget\RebhMonth;
use App\Livewire\widget\RebhMonthPlace;
use App\Models\Place;
use App\Models\Rebh_first_place;
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

class Arbah_place extends Page implements HasForms,HasActions
{
  use InteractsWithForms,InteractsWithActions;
  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'الارباح حسب الصالات';
  protected static ?string $navigationGroup = 'الارباح';
  protected static ?int $navigationSort=3;

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

    protected static string $view = 'filament.pages.reports.arbah-place';

  protected ?string $heading="";

  public $year;
  public $place;
  public function mount(){
    $year=2024;
    $this->place=Place::first()->id;
   $this->form->fill([
       'year' => $year,'place' => $this->place,
   ]);
  }
public function form(Form $form): Form
{
    return $form
        ->schema([
           Select::make('year')
            ->options(Rebh_first_place::selectraw('distinct wyear as year')->pluck('year','year'))
            ->label('السنه')
            ->preload()
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state){
                $this->year=$state;
                $this->dispatch('updateyearplace',year: $this->year,place: $this->place);
            }),
            Select::make('place')
                ->options(Place::all()->pluck('name','id'))
                ->label('المكان')
                ->preload()
                ->searchable()
                ->live()
                ->afterStateUpdated(function ($state){
                    $this->place=$state;
                    $this->dispatch('updateyearplace',year: $this->year,place: $this->place);
                }),

        ])->columns(4);
}

    protected function getFooterWidgets(): array
  {
    return [

      RebhMonthPlace::make([
        'year'=>$this->year,'place' => $this->place,
      ]),



    ];
  }


}
