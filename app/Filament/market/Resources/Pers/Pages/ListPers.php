<?php

namespace App\Filament\market\Resources\Pers\Pages;

use App\Filament\Market\Pages\InpPer;
use App\Filament\market\Resources\Pers\PerResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Support\Enums\Alignment;

class ListPers extends ListRecords
{
    protected static string $resource = PerResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
        //    CreateAction::make()->label('ادخال اذن صرف'),

                Action::make('add')
                    ->label('ادخال اذن صرف')
                    ->url(InpPer::getUrl()),


        ];
    }

}
