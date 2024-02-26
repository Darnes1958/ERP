<x-filament-panels::page>

    <x-table class="table-fixed font-medium">
        <x-slot name="head">
            <x-table.heading class="w-1/12 text-right" >رقم الصنف</x-table.heading>
            <x-table.heading class="w-4/12 text-right" >الصنف</x-table.heading>
            <x-table.heading class="w-1/12 text-right" >الكمية</x-table.heading>
            <x-table.heading class="w-1/12 text-right">السعر</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المجموع</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المتبقي(ك)</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المتبقي(ص)</x-table.heading>
        </x-slot>

        <x-slot name="body">
            @foreach ($record->Buy_tran as $item)

                <x-table.row  class=" text-xs " style="height: 10pt;">
                    <x-table.cell > {{$item->item_id}} </x-table.cell>
                    <x-table.cell > {{$item->Item->name}} </x-table.cell>

                    <x-table.cell > {{$item->q1}}  </x-table.cell>
                    <x-table.cell>  {{$item->price_input}} </x-table.cell>
                    <x-table.cell > {{$item->sub_input}}  </x-table.cell>
                    <x-table.cell > {{$item->qs1}}  </x-table.cell>
                    <x-table.cell>  {{$item->qs2}} </x-table.cell>
                </x-table.row>
            @endforeach
        </x-slot>
    </x-table>


</x-filament-panels::page>
