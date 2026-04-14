<?php

namespace App\Filament\market\Resources\PriceSells;

use App\Filament\market\Resources\PriceSells\Pages\CreatePriceSell;
use App\Filament\market\Resources\PriceSells\Pages\EditPriceSell;
use App\Filament\market\Resources\PriceSells\Pages\ListPriceSells;
use App\Filament\Resources\PriceSellResource\Pages;
use App\Filament\Resources\PriceSellResource\RelationManagers;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\Price_sell;

use App\Models\Price_type;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
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
                TextColumn::make('Item.price_buy')
                 ->visible(fn()=>Auth::user()->hasRole('admin'))
                 ->label('سعر الشراء'),
                TextInputColumn::make('price1')
                    ->rules(['required', 'gt:0'])
                    ->afterStateUpdated(function ($state, $record) {
                       if ($record->price_type_id == 1)
                        Item::find($record->item_id)->update(['price1' => $state]);
                    })
                    ->label('السعر'),


            ])
            ->recordUrl(false)
            ->filters([
                SelectFilter::make('price_type_id')
                    ->label('نوع السعر')
                    ->options(Price_type::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Filter::make('item_type')
                ->schema([
                    Select::make('item_type_id')
                        ->label('التصنيف')
                        ->options(Item_type::all()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['item_type_id'],
                            fn (Builder $query): Builder =>
                              $query->whereIn('item_id',  Item::where('item_type_id', $data['item_type_id'])->pluck('id')),
                        );
                })
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                //
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
