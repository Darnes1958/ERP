<?php

namespace App\Filament\ins\Resources\WrongkstResource\Pages;

use App\Filament\ins\Resources\WrongkstResource;
use App\Models\Correct;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class ListWrongksts extends ListRecords
{
    protected static string $resource = WrongkstResource::class;

    protected ?string $heading='أقساط واردة بالخطأ';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewStats')
                ->label('عرض الأرشيف')
                ->visible(fn():bool =>Correct::count()>0)
                ->modalHeading('عرض الاقساط المصححة والمرجعة في الارشيف')
               // ->modalWidth('4xl') // Adjust size as needed
                ->modalSubmitAction(false) // Remove "Submit" button if it's view-only
                ->modalCancelActionLabel('عودة')
                ->modalContent(fn () => new HtmlString(
                    Blade::render('@livewire(\App\Livewire\widget\ViewCorrect::class)')
                ))

        ];
    }
}
