<x-filament-panels::page>
    <div class="flex ">
        <div class="w-6/12">
            {{ $this->form }}
        </div>

        <div class="w-3/12 " >
            {{ $this->printAction }}
        </div>


    </div>
    <div>
        <div wire:loading class="text-primary-400">
            يرجي الإنتظار ...
        </div>
        {{$this->table}}


    </div>


</x-filament-panels::page>
