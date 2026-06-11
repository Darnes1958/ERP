<?php

namespace App\Support;

use App\Models\Item;
use App\Services\ItemLabelPrintService;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;
use Throwable;

class ItemLabelPrintActions
{
    /**
     * @param  Collection<int, Item>|Item  $items
     */
    public static function directPrint(Collection|Item $items, Component $livewire): void
    {
        $service = app(ItemLabelPrintService::class);
        $items = $items instanceof Item ? collect([$items]) : $items;

        if ($service->shouldPrintOnServer()) {
            try {
                $service->printOnServer($items);

                Notification::make()
                    ->title('تمت الطباعة المباشرة')
                    ->body('تم إرسال '.$items->count().' ملصق إلى الطابعة.')
                    ->success()
                    ->send();

                return;
            } catch (Throwable $exception) {
                if (! $service->shouldFallbackToQz()) {
                    Notification::make()
                        ->title('فشلت الطباعة المباشرة')
                        ->body($service->serverPrintErrorMessage($exception))
                        ->danger()
                        ->send();

                    return;
                }
            }
        }

        try {
            $payload = $service->buildPdfBase64($items);

            $livewire->dispatch(
                'direct-print-labels',
                payload: $payload,
                format: 'pdf',
                count: $items->count(),
            )->self();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('فشلت الطباعة المباشرة')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
