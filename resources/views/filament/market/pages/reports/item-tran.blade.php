<x-filament-panels::page>
    <div >

            {{ $this->form }}





    </div>
    <div>
        <div wire:loading class="text-primary-400">
            يرجي الإنتظار ...
        </div>
        {{$this->table}}


    </div>


</x-filament-panels::page>
