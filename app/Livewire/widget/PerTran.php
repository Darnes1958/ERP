<?php

namespace App\Livewire\widget;

use App\Models\Buy_tran;
use App\Models\Per;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PerTran extends BaseWidget
{
  public function mount($per_id){
    $this->per_id=$per_id;
  }
  protected static ?string $heading='';
  public $per_id;
    public function table(Table $table): Table
    {
        return $table
          ->query(function (){
            return \App\Models\PerTran::where('per_id',$this->per_id);

          })
            ->columns([
              TextColumn::make('item_id')
                ->label('رقم الصنف')
                ->sortable(),
              TextColumn::make('Item.name')
                ->label('اسم الصنف')
                ->sortable(),
              TextColumn::make('quantity')
                ->label('الكمية')
                ->sortable(),

            ]);
    }
}
