<x-filament-panels::page>
    <x-filament::tabs label="Content tabs">
        <x-filament::tabs.item
            :active="$activeTab === 'الكل'"
            wire:click="$set('activeTab', 'الكل')">
            الكل
            <x-slot name="badge" >
                {{\App\Models\Sell::query()->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'تقسيط'"
                               wire:click="$set('activeTab', 'تقسيط')">
            تقسيط
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', 3)->count()}}
            </x-slot>

        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'تقسيط قائم'"
                               wire:click="$set('activeTab', 'تقسيط قائم')">
            تقسيط قائم
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', 3)
                  ->whereIn('id', \App\Models\Main::query()->pluck('sell_id'))->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'تقسيط أرشيف'"
                               wire:click="$set('activeTab', 'تقسيط أرشيف')">
            تقسيط أرشيف
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', 3)
                    ->whereIn('id', \App\Models\Main_arc::query()->pluck('sell_id'))->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'تقسيط بدون عقد'"
                               wire:click="$set('activeTab', 'تقسيط بدون عقد')">
            تقسيط بدون عقد
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', 3)->whereNotIn('id', \App\Models\Main::query()->pluck('sell_id'))
                    ->whereNotIn('id', \App\Models\Main_arc::query()->pluck('sell_id'))->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'نقدا'"
                               wire:click="$set('activeTab', 'نقدا')">
            نقدا
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', '!=',3)->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'نقداً آجلة'"
                               wire:click="$set('activeTab', 'نقداً آجلة')">
            نقداً آجلة
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', '!=',3)
                    ->where('baky','!=',0)->count()}}
            </x-slot>
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$activeTab === 'نقداً مدفوعة'"
                               wire:click="$set('activeTab', 'نقداً مدفوعة')">
            نقداً مدفوعة
            <x-slot name="badge">
                {{\App\Models\Sell::where('price_type_id', '!=',3)
                    ->where('baky',0)->count()}}
            </x-slot>
        </x-filament::tabs.item>

    </x-filament::tabs>

    {{$this->table}}
</x-filament-panels::page>
