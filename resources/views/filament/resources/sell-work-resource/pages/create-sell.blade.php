<x-filament-panels::page>
    <div>
        <div class="w-full ">
            {{$this->sellForm}}
        </div>
        <div class="flex w-full gap-2 pt-2">
            <div class="w-5/12  ">
                <div >
                    {{$this->sellTranForm}}
                </div>
                <div class="pt-2 ">
                    {{$this->sellStoreForm}}
                </div>
            </div>
            <div class="w-7/12">
                {{$this->table}}
            </div>


        </div>

    </div>

</x-filament-panels::page>
