<?php

namespace App\Filament\Pages\Reports;

use App\Models\Buy;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class BuyRep extends Page implements HasForms,HasTable
{
  use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.buy-rep';
  protected static ?string $navigationLabel = 'قواتير مشتريات';
  protected static ?string $navigationGroup = 'تقارير';
  protected ?string $heading = "";

  public function table(Table $table): Table
  {
    return $table
      ->query(function (Buy $buy){
        $buy->all();
        return $buy;
      })

      ->columns([
        TextColumn::make('id')
          ->label('الرقم الالي'),
        TextColumn::make('Supplier.name')
          ->label('اسم المورد'),
        TextColumn::make('order_date')
          ->label('التاريخ'),
        TextColumn::make('tot')
          ->label('اجمالي الفاتورة'),
        TextColumn::make('pay')
          ->label('المدفوع'),
        TextColumn::make('baky')
          ->label('الباقي'),
        TextColumn::make('notes')
          ->label('ملاحظات'),

      ]);
  }
}
