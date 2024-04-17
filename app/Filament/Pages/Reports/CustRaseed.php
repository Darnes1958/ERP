<?php

namespace App\Filament\Pages\Reports;

use App\Models\Cust_tran;
use App\Models\Supp_tran;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CustRaseed extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='أرصدة الزبائن';
    protected static ?string $navigationGroup='زبائن وموردين';
  protected static ?int $navigationSort=6;
    protected ?string $heading="";

    protected static string $view = 'filament.pages.reports.cust-raseed';

    public $repDate1;
    public $repDate2;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
    }
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('repDate1')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate1=$state;

                    })
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate2=$state;

                    })
                    ->label('إلي تاريخ')

            ])->columns(6);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Cust_tran::
                    selectRaw('name,sum(mden) mden,sum(daen) daen,sum(daen-mden) raseed')
                    ->when($this->repDate1,function ($q){
                    $q->where('repDate','>=',$this->repDate1);
                })
                    ->when($this->repDate2,function ($q){
                        $q->where('repDate','<=',$this->repDate2);
                    })
                    ->groupBy('name')
                ;
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الزبون'),
                TextColumn::make('mden')
                    ->color('danger')
                    ->searchable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('مدين'),
                TextColumn::make('daen')
                    ->color('info')
                    ->searchable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('دائن'),
                TextColumn::make('raseed')
                    ->color(function($state){
                        if($state>=0) return 'green'; else return 'danger';
                    })
                    ->searchable()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('الرصيد'),

            ])
            ->defaultSort('name')
            ->striped();
    }
}
