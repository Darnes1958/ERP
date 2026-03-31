<?php

namespace App\Livewire\widget;

use App\Models\Correct;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

;
class ViewCorrect extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Correct::query())
            ->heading('')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('الاسم'),
                TextColumn::make('Taj.TajName')
                    ->searchable()
                    ->sortable()
                    ->label('المصرف'),
                TextColumn::make('acc')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->label('رقم الحساب'),
                TextColumn::make('wrong_date')
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('kst')
                    ->label('المبلغ'),
                TextColumn::make('status')
                    ->label('الحالة'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
