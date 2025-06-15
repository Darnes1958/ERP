<?php

namespace App\Filament\Pages\Reports;


use App\Livewire\widget\KlasaBox;
use App\Livewire\widget\KlasaBuy;
use App\Livewire\widget\KlasaCust;
use App\Livewire\widget\klasakzaen;
use App\Livewire\widget\KlasaMasr;
use App\Livewire\widget\KlasaSalary;
use App\Livewire\widget\KlasaSell;
use App\Livewire\widget\KlasaSupp;
use App\Livewire\widget\KlasaTar;
use App\Livewire\widget\KlasaTarBuy;
use App\Livewire\widget\RepBuy;
use App\Livewire\widget\RepReceipt;
use App\Livewire\widget\RepResSupp;
use App\Livewire\widget\RepSell;
use App\Livewire\widget\StatsKlasa;
use App\Models\Place;
use App\Models\Recsupp;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class RepKlasa extends Page implements HasForms,HasActions
{
    use InteractsWithForms,InteractsWithActions;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'خلاصة الحركة اليومية';
    protected static ?string $navigationGroup = 'الحركة اليومية';
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

    protected static string $view = 'filament.pages.reports.rep-klasa';
    protected ?string $heading="";

  public $repDate1;
  public $repDate2;
    public $place_id=0;
    public function mount(){
      $this->repDate1=now();
      $this->repDate2=now();
      $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);


    }
    public static function getWidgets(): array
    {
        return [
            KlasaBuy::class,
            KlasaSell::class,
            KlasaSupp::class,
            KlasaCust::class,
            KlasaMasr::class,
            KlasaSalary::class,
            StatsKlasa::class,
            RepBuy::class,
            RepSell::class,
            Recsupp::class,
            RepReceipt::class,
            klasakzaen::class,
            KlasaTarBuy::class,
          KlasaTar::class,

        ];
    }
    protected function getFooterWidgets(): array
    {
        return [

          StatsKlasa::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),

            KlasaBuy::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
              ]),
            KlasaSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
            KlasaSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
            KlasaCust::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
          KlasaMasr::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),
            KlasaSalary::make([
                'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
          klasakzaen::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),

          KlasaTar::make([
                'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
          KlasaTarBuy::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),


        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('repDate1')
                 ->live()
                 ->afterStateUpdated(function ($state){
                   if ($this->chkDate($state))  $this->repDate1=$state;
                     $this->dispatch('updateDate1', repdate: $state);
                 })

                ->prefix('من تاريخ')
                ->hiddenLabel(),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    if ($this->chkDate($state)) $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->prefix('حتي تاريخ')
                  ->hiddenLabel(),
                Select::make('place_id')
                    ->placeholder('الكل')
                    ->columnSpan(2)
                    ->live()
                    ->options(Place::all()->pluck('name', 'id'))
                    ->afterStateUpdated(function ($state){
                        if ($state!=null) $this->place_id=$state;else $this->place_id=0;
                        $this->dispatch('updateklasaplace', place: $state);
                    })
                    ->label('المكان')


            ])->columns(2);
    }
    public function printAction(): Action
    {

        return Action::make('print')
            ->visible(function (){
                return $this->chkDate($this->repDate1) || $this->chkDate($this->repDate2);
            })
            ->label('طباعة')
            ->button()
            ->color('danger')
            ->icon('heroicon-m-printer')
            ->color('info')

            ->url(fn (): string => route('pdfklasa', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id]));
    }
}
