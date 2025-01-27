<x-filament-panels::page>
    <div>
        <div class="w-full ">
            {{$this->sellForm}}
        </div>
        <div class="flex w-full gap-2 pt-2">
            <div class="w-6/12  ">
                {{$this->sellTranForm}}
            </div>
            <div class="w-6/12">
                {{$this->table}}
            </div>
        </div>

    </div>
</x-filament-panels::page>
