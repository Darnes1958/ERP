<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\widget\RepBuy;
use App\Livewire\widget\RepMasr;
use App\Livewire\widget\RepReceipt;
use App\Livewire\widget\RepResSupp;
use App\Livewire\widget\RepSell;
use App\Livewire\widget\RepTarBuy;
use App\Livewire\widget\RepTarSell;
use App\Models\Place;
use App\Models\Recsupp;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RepDaily extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'الحركة اليومية';
    protected static ?string $navigationGroup = 'الحركة اليومية';
    protected static ?int $navigationSort=1;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected static string $view = 'filament.pages.reports.rep-daily';
    protected ?string $heading="";

    public $repDate1;
    public $repDate2;
    public $place_id;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id]);
    }
    public static function getWidgets(): array
    {
        return [
            RepBuy::class,
            RepSell::class,
            Recsupp::class,
            RepReceipt::class,
            RepTarSell::class,
            RepTarBuy::class,
            RepMasr::class,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            RepBuy::make([
                'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
            RepSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
            RepResSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
            RepReceipt::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
            ]),
          RepTarSell::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),
          RepTarBuy::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),
          RepMasr::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id,
          ]),


        ];
    }
    public function chkDate($repDate){
        try {
            Carbon::parse($repDate);
            return true;
        } catch (InvalidFormatException $e) {
            return false;
        }
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('repDate1')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate1=$state;
                        $this->dispatch('updateDate1', repdate: $state);
                    })
                    ->columnSpan(2)
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->columnSpan(2)
                  ->label('إلي تاريخ'),
                Select::make('place_id')
                    ->placeholder('الكل')
                    ->columnSpan(2)
                    ->live()
                 ->options(Place::all()->pluck('name', 'id'))
                 ->afterStateUpdated(function ($state){
                     $this->place_id=$state;
                     $this->dispatch('updatePlace', place: $state);
                 })
                 ->label('المكان')


            ])->columns(6);
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
            ->url(fn (): string => route('pdfdaily', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,'place_id'=>$this->place_id]));
    }
}
