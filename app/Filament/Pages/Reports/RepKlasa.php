<?php

namespace App\Filament\Pages\Reports;


use App\Livewire\widget\KlasaBuy;
use App\Livewire\widget\KlasaCust;
use App\Livewire\widget\KlasaMasr;
use App\Livewire\widget\KlasaSell;
use App\Livewire\widget\KlasaSupp;
use App\Livewire\widget\RepBuy;
use App\Livewire\widget\RepReceipt;
use App\Livewire\widget\RepResSupp;
use App\Livewire\widget\RepSell;
use App\Livewire\widget\StatsKlasa;
use App\Models\Recsupp;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
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
        return Auth::user()->hasRole('Admin');
    }

    protected static string $view = 'filament.pages.reports.rep-klasa';
    protected ?string $heading="";

  public $repDate1;
  public $repDate2;
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
            StatsKlasa::class,
            RepBuy::class,
            RepSell::class,
            Recsupp::class,
            RepReceipt::class,


        ];
    }
    protected function getFooterWidgets(): array
    {
        return [

          StatsKlasa::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
          ]),

            KlasaBuy::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
              ]),
            KlasaSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            KlasaSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            KlasaCust::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
          KlasaMasr::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
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
                ->label('من تاريخ')
                ->inlineLabel(),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    if ($this->chkDate($state)) $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->label('حتي تاريخ')
                  ->inlineLabel()

            ]);
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
            ->url(fn (): string => route('pdfklasa', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,]));
    }
}
