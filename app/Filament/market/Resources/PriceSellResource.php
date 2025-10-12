<?php

namespace App\Filament\market\Resources;

use App\Filament\market\Resources\PriceSellResource\Pages\CreatePriceSell;
use App\Filament\market\Resources\PriceSellResource\Pages\EditPriceSell;
use App\Filament\market\Resources\PriceSellResource\Pages\ListPriceSells;
use App\Filament\Resources\PriceSellResource\Pages;
use App\Filament\Resources\PriceSellResource\RelationManagers;
use App\Models\Price_sell;
use App\Models\PriceSell;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PriceSellResource extends Resource
{
    protected static ?string $model = Price_sell::class;



    protected static ?string $navigationLabel='أسعار الأصناف';
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=7;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Item.name')
                ->searchable()
                ->label('الصنف'),
                TextColumn::make('Price_type.name')
                    ->searchable()
                    ->label('نوع السعر'),
                TextInputColumn::make('price1')
                    ->rules(['required', 'gt:0'])


                    ->label('السعر'),


            ])
            ->recordUrl(false)
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceSells::route('/'),
            'create' => CreatePriceSell::route('/create'),
            'edit' => EditPriceSell::route('/{record}/edit'),
        ];
    }
}
