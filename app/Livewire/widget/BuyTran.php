<?php

namespace App\Livewire\widget;

use App\Models\Buy_tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BuyTran extends BaseWidget
{
  public function mount($buy_id){
    $this->buy_id=$buy_id;
  }
  protected static ?string $heading='';
  public $buy_id;
    public function table(Table $table): Table
    {
        return $table
          ->query(function (){
            return Buy_tran::where('buy_id',$this->buy_id);

          })
            ->columns([
              TextColumn::make('item_id')
                ->label('رقم الصنف')
                ->sortable(),
              TextColumn::make('barcode_id')
                ->label('الباركود')
                ->sortable(),
              TextColumn::make('Item.name')
                ->label('اسم الصنف')
                ->description(function (Buy_tran $record){
                  if ($record->tar_buy_id) return ' كمية مرجعة  ('.$record->buy->q1.') بتاريخ '.$record->Tar_buy->tar_date;
                })
                ->color(function(Buy_tran $record){
                  if ($record->tar_buy_id) return 'primary'; else return 'info';
                })
                ->sortable(),
              TextColumn::make('q1')
                ->label('الكمية')
                ->sortable(),
              TextColumn::make('price_input')
                ->label('سعر الشراء')
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->sortable(),
              TextColumn::make('sub_input')
                ->label('المجموع')
                ->numeric(
                  decimalPlaces: 2,
                  decimalSeparator: '.',
                  thousandsSeparator: ',',
                )
                ->sortable(),
            ]);
    }
}
