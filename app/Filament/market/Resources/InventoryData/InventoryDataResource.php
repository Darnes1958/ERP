<?php

namespace App\Filament\Market\Resources\InventoryData;

use App\Filament\Market\Resources\InventoryData\Pages\CreateInventoryData;
use App\Filament\Market\Resources\InventoryData\Pages\EditInventoryData;
use App\Filament\Market\Resources\InventoryData\Pages\ListInventoryData;
use App\Filament\Market\Resources\InventoryData\Schemas\InventoryDataForm;
use App\Filament\Market\Resources\InventoryData\Tables\InventoryDataTable;
use App\Models\InventoryData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class InventoryDataResource extends Resource
{
    protected static ?string $model = InventoryData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel='جرد';
    protected static string | UnitEnum | null $navigationGroup='اعدادت';
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::id()==1;
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryDataTable::configure($table);
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
            'index' => ListInventoryData::route('/'),
            'create' => CreateInventoryData::route('/create'),
            'edit' => EditInventoryData::route('/{record}/edit'),
        ];
    }
}
