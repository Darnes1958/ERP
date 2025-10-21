<?php

namespace App\Filament\Ins\Resources\HafithaResource\Pages;

use App\Filament\Ins\Resources\HafithaResource;
use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\HafithaTranResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ManageHafithaTrans extends ManageRelatedRecords
{
    protected static string $resource = HafithaResource::class;

    protected static string $relationship = 'hafithaTrans';

    protected static ?string $relatedResource = HafithaTranResource::class;



    public function table(Table $table): Table
    {
        return $table

            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
