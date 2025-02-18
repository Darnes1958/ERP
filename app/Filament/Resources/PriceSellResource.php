<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceSellResource\Pages;
use App\Filament\Resources\PriceSellResource\RelationManagers;
use App\Models\Price_sell;
use App\Models\PriceSell;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PriceSellResource extends Resource
{
    protected static ?string $model = Price_sell::class;



    protected static ?string $navigationLabel='أسعار الأصناف';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=7;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPriceSells::route('/'),
            'create' => Pages\CreatePriceSell::route('/create'),
            'edit' => Pages\EditPriceSell::route('/{record}/edit'),
        ];
    }
}
