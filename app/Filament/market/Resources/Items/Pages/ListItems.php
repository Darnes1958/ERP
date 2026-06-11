<?php

namespace App\Filament\market\Resources\Items\Pages;

use App\Filament\market\Resources\Items\ItemResource;
use App\Livewire\Traits\PublicTrait;
use App\Models\Item;
use App\Models\Item_type;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;
class ListItems extends ListRecords
{
    use PublicTrait;

    protected static string $resource = ItemResource::class;

    public function getTitle():  string|Htmlable
    {
        return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">أصناف</div>');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_labels_pdf')
                ->label('طباعة ملصقات')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->schema([
                    Select::make('item_type_id')
                        ->label('التصنيف')
                        ->options(Item_type::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $items = Item::query()
                        ->where('item_type_id', $data['item_type_id'])
                        ->orderBy('id')
                        ->get();

                    if ($items->isEmpty()) {
                        Notification::make()
                            ->title('لا توجد أصناف لهذا التصنيف')
                            ->warning()
                            ->send();

                        return;
                    }

                    return Response::download(
                        self::ret_spatie_labels($items, 'PDF.ItemLabels'),
                        'item-labels.pdf',
                        self::ret_spatie_header()
                    );
                }),
            CreateAction::make()
                ->label('إضافة صنف جديد'),
        ];
    }
}
