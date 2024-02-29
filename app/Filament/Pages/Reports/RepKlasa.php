<?php

namespace App\Filament\Pages\Reports;


use App\Livewire\widget\KlasaBuy;
use App\Livewire\widget\KlasaCust;
use App\Livewire\widget\KlasaSell;
use App\Livewire\widget\KlasaSupp;
use App\Livewire\widget\RepBuy;
use App\Livewire\widget\RepReceipt;
use App\Livewire\widget\RepResSupp;
use App\Livewire\widget\RepSell;
use App\Livewire\widget\StatsKlasa;
use App\Models\Recsupp;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RepKlasa extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'خلاصة الحركة اليومية';
    protected static ?string $navigationGroup = 'تقارير';
    protected static ?int $navigationSort=5;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('Admin');
    }

    protected static string $view = 'filament.pages.reports.rep-klasa';
    protected ?string $heading="";

    public $repDate;
    public function mount(){
        $this->repDate=now();
        $this->form->fill(['repDate'=>$this->repDate]);
    }
    public static function getWidgets(): array
    {
        return [

            KlasaBuy::class,
            KlasaSell::class,
            KlasaSupp::class,
            KlasaCust::class,

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
            'repDate'=>$this->repDate,
          ]),

            KlasaBuy::make([
                'repDate'=>$this->repDate,]),
            KlasaSell::make([
                'repDate'=>$this->repDate,
            ]),
            KlasaSupp::make([
                'repDate'=>$this->repDate,
            ]),
            KlasaCust::make([
                'repDate'=>$this->repDate,
            ]),

        ];
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('repDate')
                 ->live()
                 ->afterStateUpdated(function ($state){
                     $this->repDate=$state;
                     $this->dispatch('updateRep', repdate: $state);
                 })
                 ->label('تاريخ اليومية')
            ])->columns(6);
    }

}
