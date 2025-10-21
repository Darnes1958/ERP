<?php

namespace App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans;

use App\Filament\Ins\Resources\HafithaResource;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Pages\CreateHafithaTran;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Pages\EditHafithaTran;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Pages\ViewHafithaTran;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Schemas\HafithaTranForm;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Schemas\HafithaTranInfolist;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Tables\HafithaTransTable;
use App\Models\HafithaTran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HafithaTranResource extends Resource
{
    protected static ?string $model = HafithaTran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = HafithaResource::class;

    public static function form(Schema $schema): Schema
    {
        return HafithaTranForm::configure($schema);
    }
    public static function infolist(Schema $schema): Schema
    {
        return HafithaTranInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HafithaTransTable::configure($table);
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
            'create' => CreateHafithaTran::route('/create'),
            'view' => ViewHafithaTran::route('/{record}'),
            'edit' => EditHafithaTran::route('/{record}/edit'),
        ];
    }
}
