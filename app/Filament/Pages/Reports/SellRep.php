<?php

namespace App\Filament\Pages\Reports;

use App\Models\Sell;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class SellRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.sell-rep';
    protected static ?string $navigationLabel = 'قواتير مبيعات';
    protected static ?string $navigationGroup = 'تقارير';
    protected ?string $heading = "";

 public function table(Table $table): Table
 {
   return $table
    ->query(function (Sell $sell){
      $sell->all();
      return $sell;
    })

     ->columns([
       TextColumn::make('id')
        ->label('الرقم الالي'),
       TextColumn::make('Customer.name')
         ->label('اسم الزبون'),
       TextColumn::make('order_date')
         ->label('التاريخ'),
       TextColumn::make('tot')
         ->label('اجمالي الفاتورة'),
       TextColumn::make('pay')
         ->label('المدفوع'),
       TextColumn::make('baky')
         ->label('الباقي'),
         TextColumn::make('sell_tran_sum_profit')
             ->sum('Sell_tran','profit')
             ->label('الربح'),
       TextColumn::make('notes')
         ->label('ملاحظات'),

     ]);
 }

}
