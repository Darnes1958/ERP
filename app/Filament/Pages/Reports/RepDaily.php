<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\widget\RepBuy;
use App\Livewire\widget\RepReceipt;
use App\Livewire\widget\RepResSupp;
use App\Livewire\widget\RepSell;
use App\Models\Recsupp;
use Filament\Forms\Components\DatePicker;
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
    protected static ?string $navigationGroup = 'تقارير';
    protected static ?int $navigationSort=4;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('Admin');
    }

    protected static string $view = 'filament.pages.reports.rep-daily';
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
            RepBuy::class,
            RepSell::class,
            Recsupp::class,
            RepReceipt::class,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            RepBuy::make([
                'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepResSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepReceipt::make([
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
                        $this->repDate1=$state;
                        $this->dispatch('updateDate1', repdate: $state);
                    })
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->label('إلي تاريخ')

            ])->columns(6);
    }
}
