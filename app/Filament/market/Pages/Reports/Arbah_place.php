<?php

namespace App\Filament\market\Pages\Reports;

use App\Livewire\widget\ChartArbah;
use App\Livewire\widget\RebhMonthPlace;
use App\Models\Place;
use App\Models\Rebh_first_place;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class Arbah_place extends Page implements HasForms,HasActions
{
  use InteractsWithForms,InteractsWithActions;
  protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel = 'الارباح حسب الصالات';
  protected static string | \UnitEnum | null $navigationGroup = 'الارباح';
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

    protected string $view = 'filament.market.pages.reports.arbah-place';

  protected ?string $heading="";

  public $year;
  public $place;
    public $amma;
  public function mount(){
    $year=Rebh_first_place::first()->year;
    $this->place=Place::first()->id;
      $this->amma=Rebh_first_place::where('wyear',$this->year)->where('place_id',null)->sum('profit');
      if (!$this->amma) $this->amma=0;
   $this->form->fill([
       'year' => $year,'place' => $this->place,'amma' => $this->amma,
   ]);
  }
public function form(Schema $schema): Schema
{
    return $schema
        ->components([
           Select::make('year')
            ->options(Rebh_first_place::selectraw('distinct wyear as year')->pluck('year','year'))
            ->label('السنه')
            ->preload()
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state,Set $set){
                $this->year=$state;
                $this->dispatch('updateyearplace',year: $this->year,place: $this->place);
                $this->amma=Rebh_first_place::where('wyear',$this->year)->where('place_id',null)->sum('profit');
                $set('amma',$this->amma);
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
            TextInput::make('amma')
             ->label('ارباح الإدارة العامة')
                ->default(0)
            ->readOnly(),

        ])->columns(4);
}

    protected function getFooterWidgets(): array
  {
    return [

      RebhMonthPlace::make([
        'year'=>$this->year,'place' => $this->place,
      ]),
        ChartArbah::make(['year'=>$this->year,'place' => $this->place,])



    ];
  }


}
