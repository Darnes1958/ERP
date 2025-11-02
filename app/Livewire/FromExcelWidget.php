<?php

namespace App\Livewire;

use App\Models\Dateofexcel;

use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

use Illuminate\Support\Facades\Auth;

class FromExcelWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table

            ->query(function () {
                $dateofexcel= Dateofexcel::where('taj_id',Auth::user()->taj);
                return $dateofexcel;
            }
            )
            ->defaultSort('date_begin','desc')
            ->columns([
                TextColumn::make('date_begin'),
                TextColumn::make('date_end'),
                TextColumn::make('taj_id'),
            ])
            ->recordActions([
               DeleteAction::make(),

            ]);
    }
}
