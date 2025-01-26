<x-filament-panels::page>


    <div
        x-data="{ mode: 'light' }"
        x-on:dark-mode-toggled.window="mode = $event.detail"
    >

    <x-table class="table-fixed font-medium">
        <x-slot name="head">
            <x-table.heading class="w-1/12 text-right" >رقم الصنف</x-table.heading>
            <x-table.heading class="w-4/12 text-right" >الصنف</x-table.heading>
            <x-table.heading class="w-1/12 text-right" >الكمية</x-table.heading>
            <x-table.heading class="w-1/12 text-right">السعر</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المجموع</x-table.heading>
            @if(\App\Models\Setting::find(\Illuminate\Support\Facades\Auth::user()->company)->has_two)
            <x-table.heading class="w-1/12 text-right">المتبقي(ك)</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المتبقي(ص)</x-table.heading>
            @else
                <x-table.heading class="w-1/12 text-right">المتبقي</x-table.heading>
            @endif
        </x-slot>

        <x-slot name="body">
            @foreach ($record->Buy_tran as $item)

                <x-table.row  class=" text-xs " style="height: 10pt;">
                    <x-table.cell fcolor="text-indigo-700"> {{$item->item_id}} </x-table.cell>
                    <x-table.cell fcolor="text-indigo-700"> {{$item->Item->name}} </x-table.cell>

                    <x-table.cell fcolor="text-indigo-700"> {{$item->q1}}  </x-table.cell>
                    <x-table.cell fcolor="text-indigo-700">  {{$item->price_input}} </x-table.cell>
                    <x-table.cell fcolor="text-indigo-700"> {{$item->sub_input}}  </x-table.cell>
                    <x-table.cell fcolor="text-indigo-700"> {{$item->qs1}}  </x-table.cell>
                    @if(\App\Models\Setting::find(\Illuminate\Support\Facades\Auth::user()->company)->has_two)
                    <x-table.cell fcolor="text-indigo-700">  {{$item->qs2}} </x-table.cell>

                    @endif



                </x-table.row>
            @endforeach
        </x-slot>
    </x-table>


</x-filament-panels::page>
