<?php

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Sell;

class SellTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Sell::query())
            ->modifyQueryUsing(function (Builder $query) use ($table): Builder {
                $arguments = $table->getArguments();
                if ($customerId = $arguments['customer_id'] ?? null) {
                    $query->where('customer_id', $customerId);
                }
                return $query;
            })

            ->columns([
                TextColumn::make('id')
                ->searchable()
                ->sortable(),
                TextColumn::make('Customer.name')
                ->searchable()
                ->sortable(),
                TextColumn::make('total'),
                TextColumn::make('order_date')
                ->searchable()
                ->sortable(),
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
