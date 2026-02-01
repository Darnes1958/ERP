<?php

namespace App\Filament\Market\Resources\Inventories\Schemas;

use App\Models\InventoryData;
use App\Models\Item;
use App\Models\Place_stock;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('inventory_data_id')->default(InventoryData::where('active', true)->first()->id),
                Select::make('place_id')
                 ->label('المكان')
                 ->relationship('Place', 'name')
                 ->preload()
                 ->required()
                 ->live()
                 ->searchable(),
                Select::make('item_id')
                    ->relationship('Item', 'name')
                    ->options(fn (Get $get): Collection => Item::query()
                        ->join('place_stocks', 'items.id', '=', 'place_stocks.item_id')
                        ->where('place_id', $get('place_id'))
                        ->pluck('name', 'items.id'))
                    ->required()
                    ->live()
                    ->preload()
                    ->afterStateUpdated(function ($state,Set $set,Get $get) {
                        if ($state){
                            $set('book_balance',Place_stock::where('place_id', $get('place_id'))
                                ->where('item_id', $get('item_id'))
                                ->first()
                                ->stock1);
                        }
                    })
                    ->searchable(),

                TextInput::make('actual_balance')
                 ->belowContent(function (Get $get){
                   if ($get('item_id'))
                       return 'الرصيد الدفتري : '.
                     Place_stock::where('place_id', $get('place_id'))
                         ->where('item_id', $get('item_id'))
                         ->first()
                         ->stock1;
                   else return null;
                 })
                    ->afterStateUpdated(function (Set $set,Get $get,$state) {
                        if ($state) {
                            $set('difference',
                            $state-Place_stock::where('place_id', $get('place_id'))
                                ->where('item_id', $get('item_id'))
                                ->first()
                                ->stock1);
                        }
                    })
                 ->required()
                 ->numeric(),
                Hidden::make('book_balance'),
                Hidden::make('difference'),
                Hidden::make('user_id')->default(Auth::id())


            ]);
    }
}
