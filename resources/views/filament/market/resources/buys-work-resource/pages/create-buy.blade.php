<x-filament-panels::page>
    <div>
        <div class="w-full ">
            {{$this->buyForm}}
        </div>
        <div class="flex w-full gap-2 pt-2">
            <div class="w-4/12  ">
             <div >
                {{$this->buyTranForm}}
             </div>
             <div class="pt-2 ">
                {{$this->buyStoreForm}}
             </div>
            </div>
            <div class="w-8/12">
               {{$this->table}}
            </div>


        </div>

    </div>

</x-filament-panels::page>
