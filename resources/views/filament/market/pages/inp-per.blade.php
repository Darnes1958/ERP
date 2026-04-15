<x-filament-panels::page>
    {{$this->perForm}}
    <div class="flex w-full gap-2 pt-2">
        <div class="w-5/12  ">
            <div >
                {{$this->tranForm}}
            </div>
        </div>
        <div class="w-7/12">
            {{$this->table}}
        </div>


    </div>


</x-filament-panels::page>
