<?php

namespace App\Livewire\Reports;

use App\Models\Main;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Query\Builder;
use Livewire\Component;
use App\Models\Tran;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Filament\Forms\Get;


class RepAksatNotGet extends Component implements HasTable, HasForms
{
    use InteractsWithTable,InteractsWithForms;
    #[Reactive]
    public $bank_id;
    #[Reactive]
    public $Date1;
    #[Reactive]
    public $Date2;

    public $sul;
    public $pay;
    public $raseed;


    public function table(Table $table):Table
    {
        return $table
            ->pluralModelLabel('العقود')
            ->query(function (Main $main)  {

                 $main= Main::where('taj_id',$this->bank_id)
                  ->whereNotin('id',function ($q){
                    $q->select('main_id')->from('trans')->whereBetween('ksm_date',[$this->Date1,$this->Date2]);
                 });
                $this->sul=$main->sum('sul');
                $this->pay=$main->sum('pay');
                $this->raseed=$main->sum('raseed');
                return  $main;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('رقم العقد'),
                TextColumn::make('Customer.name')
                    ->label('الاسم'),
                TextColumn::make('sul')
                    ->label('اجمالي العقد')
                  ->summarize(
                    Summarizer::make()
                      ->using(function (){return $this->sul;})
                  ),
                TextColumn::make('pay')
                    ->label('المسدد')
                  ->summarize(
                    Summarizer::make()
                      ->using(function (){return $this->pay;})
                  ),
                TextColumn::make('raseed')
                    ->label('الرصيد')
                  ->summarize(
                    Summarizer::make()
                      ->using(function (){return $this->raseed;})
                  ),
                TextColumn::make('kst')
                    ->label('القسط'),
                TextColumn::make('LastKsm')
                    ->label('تاريخ أخر خصم'),
            ]);
    }

    public function mount($Date1,$Date2,$bank_id){
        $this->Date1=$Date1;
        $this->Date2=$Date2;
        $this->bank_id=$bank_id;


    }

    public function render()
    {
        return view('livewire.reports.rep-aksat-not-get');
    }
}
