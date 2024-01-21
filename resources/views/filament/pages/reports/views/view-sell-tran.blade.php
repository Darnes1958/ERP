<x-filament-panels::page>



    <x-table class="table-fixed font-medium">
        <x-slot name="head">
            <x-table.heading class="w-1/12 text-right" >رقم الصنف</x-table.heading>
            <x-table.heading class="w-4/12 text-right" >الصنف</x-table.heading>
            <x-table.heading class="w-1/12 text-right" >الكمية(ك)</x-table.heading>
            <x-table.heading class="w-1/12 text-right" >الكمية(ص)</x-table.heading>
            <x-table.heading class="w-1/12 text-right">السعر(ك)</x-table.heading>
            <x-table.heading class="w-1/12 text-right">السعر(ص)</x-table.heading>
            <x-table.heading class="w-1/12 text-right">المجموع</x-table.heading>
        </x-slot>

        <x-slot name="body">
            @foreach ($record->Sell_tran as $item)

                <x-table.row  class=" text-xs " style="height: 10pt;">
                    <x-table.cell > {{$item->item_id}} </x-table.cell>
                    <x-table.cell > {{$item->Item->name}} </x-table.cell>
                    <x-table.cell > {{$item->q1}}  </x-table.cell>
                    <x-table.cell > {{$item->q2}}  </x-table.cell>
                    <x-table.cell>  {{$item->price1}} </x-table.cell>
                    <x-table.cell>  {{$item->price2}} </x-table.cell>
                    <x-table.cell > {{$item->sub_tot}}  </x-table.cell>
                </x-table.row>
            @endforeach
        </x-slot>
    </x-table>

</x-filament-panels::page>
