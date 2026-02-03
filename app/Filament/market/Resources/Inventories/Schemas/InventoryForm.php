<?php

namespace App\Filament\Market\Resources\Inventories\Schemas;

use App\Models\Inventory;
use App\Models\InventoryData;
use App\Models\Item;
use App\Models\Place_stock;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

class InventoryForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make()
                ->schema([
                    Hidden::make('inventory_data_id')->default(InventoryData::where('active', true)->first()->id),
                    Select::make('place_id')
                        ->label('المكان')
                        ->relationship('Place', 'name')
                        ->preload()
                        ->required()
                        ->live()
                        ->id('place_id')
                        ->searchable(),
                    Select::make('item_id')

                        ->options(fn (Get $get): Collection => Item::query()
                            ->join('place_stocks', 'items.id', '=', 'place_stocks.item_id')
                            ->where('place_id', $get('place_id'))
                            ->whereNotIn('items.id', Inventory::where('inventory_data_id', $get('inventory_data_id'))
                             ->where('place_id', $get('place_id'))->pluck('item_id')

                            )
                            ->pluck('name', 'items.id')
                        )
                        ->required()
                        ->live()
                        ->preload()
                        ->afterStateUpdated(function ($state,Set $set,Get $get,$livewire) {

                            if ($state){
                                $res=Place_stock::where('place_id', $get('place_id'))
                                    ->where('item_id', $state)
                                    ->first();
                                $set('book_balance',$res->stock1);
                                $set('place_stock_id',$res->id);
                            }
                        })

                        ->id('item_id')
                        ->searchable(),

                    TextInput::make('actual_balance')
                        ->readOnly(fn(Get $get) => ! $get('item_id'))
                        ->minValue(0)
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
                            if ($state && $get('item_id')) {
                                $res=Place_stock::where('place_id', $get('place_id'))
                                    ->where('item_id', $get('item_id'))
                                    ->first();

                                $set('difference',$state-$res->stock1);
                                $set('its_value',($state-$res->stock1)*Item::find($get('item_id'))->price_buy);
                            }
                        })
                        ->required()
                        ->id('actual_balance')
                        ->numeric(),
                    Hidden::make('place_stock_id'),
                    Hidden::make('book_balance'),
                    Hidden::make('difference'),
                    Hidden::make('its_value'),
                    Hidden::make('user_id')->default(Auth::id())

                ])
                ->columns(1)
            ]);
    }
}
