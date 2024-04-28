<?php

namespace App\Livewire\widget;

use App\Models\Rebh_second;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class RebhMonth extends BaseWidget
{
  public $repDate1;
  public $repDate2;

  #[On('updateDate1')]
  public function updatedate1($repdate)
  {
    $this->repDate1=$repdate;

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;

  }
  public function getTableRecordKey(Model $record): string
  {
    return uniqid();
  }

  public function table(Table $table): Table
    {
        return $table
            ->query(function (){
              $res=Rebh_second::whereBetween('date',[$this->repDate1,$this->repDate2]);
              return $res;
            }

            )
          ->defaultSort('date')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                 ->label('التاريخ'),
                Tables\Columns\TextColumn::make('rebh')
                 ->label('الربح'),
              Tables\Columns\TextColumn::make('masr')
                ->label('مصروفات'),
              Tables\Columns\TextColumn::make('sal')
                ->label('مرتبات'),
              Tables\Columns\TextColumn::make('rent')
                ->label('ايجارات'),


            ]);
    }
}
