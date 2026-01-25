<?php

namespace App\Livewire\widget;

use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

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
              $buy_tran=Buy_tran::where('buy_id',$this->buy_id);
              return $buy_tran;
            }

            )
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
                  if ($record->tar_buy_id) return ' كمية مرجعة  ('.$record->Tar_buy->q1.') بتاريخ '.$record->Tar_buy->tar_date;
                })
                ->color(function(Buy_tran $record){
                  if ($record->tar_buy_id) return 'primary'; else return 'info';
                })
                ->sortable(),
              TextColumn::make('q1')
                ->label('الكمية'),

              TextColumn::make('q2')
                ->label('صغري')
                ->visible(Setting::find(Auth::user()->company)->has_two)
                ->formatStateUsing(function (string $state) {
                  if ($state=='0') return '';
                  return $state;
                }),
              TextColumn::make('price1')
                ->label('سعر البيع'),

              TextColumn::make('price2')
                ->label('سعر الصغري')
                ->visible(Setting::find(Auth::user()->company)->has_two)
                ->formatStateUsing(function (string $state) {
                  if ($state=='0.0') return '';
                  return $state;
                }),
            ]);
    }
}
