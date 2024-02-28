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

    public $repDate;
    public function mount(){
        $this->repDate=now();
        $this->form->fill(['repDate'=>$this->repDate]);
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
                'repDate'=>$this->repDate,
            ]),
            RepSell::make([
                'repDate'=>$this->repDate,
            ]),
            RepResSupp::make([
                'repDate'=>$this->repDate,
            ]),
            RepReceipt::make([
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
