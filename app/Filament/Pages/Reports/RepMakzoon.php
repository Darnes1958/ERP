<?php

namespace App\Filament\Pages\Reports;

use App\Models\Place_stock;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class RepMakzoon extends Page implements HasTable

{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.rep-makzoon';
    protected static ?string $navigationLabel='تقرير عن المخزون';
    protected static ?string $navigationGroup='تقارير';
    protected ?string $heading="";

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Place_stock $place_stock){
                $place_stock->all();
                return $place_stock;
            })
            ->columns([
                TextColumn::make('Place.name')
                    ->sortable()
                    ->searchable()
                    ->label('المكان'),
                TextColumn::make('item_id')
                    ->sortable()
                    ->searchable()
                   ->label('رقم الصنف'),
                TextColumn::make('Item.name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الصنف'),
                TextColumn::make('Item.stock1')
                 ->label('الرصيد الكلي'),
                TextColumn::make('stock1')
                    ->label('الكمية (ك)'),
                TextColumn::make('stock2')
                    ->label('الكمية (ص)'),

            ])
            ->striped();
    }
}