<?php

namespace App\Filament\Pages\Reports;

use App\Models\Cust_tran;
use App\Models\Customer;
use App\Models\Place_stock;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustTran extends Page implements HasForms,HasTable
{
  use InteractsWithTable,InteractsWithForms;
  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel='حركة زبون';
  protected static ?string $navigationGroup='تقارير';
  protected ?string $heading="";
  protected static string $view = 'filament.pages.reports.cust-tran';

  public $cust_id;
  public $repDate;

  public function mount(){
    $this->repDate=now();

    $this->form->fill(['repDate'=>$this->repDate,]);
  }

  public function getTableRecordKey(Model $record): string
  {
    return uniqid();
  }


  public function table(Table $table): Table
  {
    return $table
      ->query(function (){
        $report=Cust_tran::
          where('customer_id',$this->cust_id)
          ->where('repDate','>=',$this->repDate);
        return $report;
      })

      ->columns([
        TextColumn::make('repDate')
          ->sortable()
          ->searchable()
          ->label('التاريخ'),
        TextColumn::make('id')
          ->sortable()
          ->searchable()
          ->label('الرقم الألي'),

        TextColumn::make('rec_who')
          ->sortable()
          ->searchable()
          ->label('البيان'),
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
        TextColumn::make('notes')
         ->label('ملاحظات')
      ])
      ->defaultSort('created_at')
      ->striped();
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Select::make('cust_id')
         ->options(Customer::all()->pluck('name','id'))
         ->searchable()
         ->preload()
         ->live()
          ->afterStateUpdated(function ($state){
            $this->cust_id=$state;
          })
         ->label('الزبون'),
        DatePicker::make('repDate')
          ->live()
          ->afterStateUpdated(function ($state){
            $this->repDate=$state;
          })
          ->label('من تاريخ'),
      ])->columns(6);
  }

}
