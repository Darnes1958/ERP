<x-filament-panels::page>
    {{$this->form}}
    <div>
    <div wire:loading class="text-primary-400">
        يرجي الإنتظار ...
    </div>
    {{$this->table}}
    </div>
</x-filament-panels::page>
