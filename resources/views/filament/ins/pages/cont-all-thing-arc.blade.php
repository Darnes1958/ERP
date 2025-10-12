<x-filament-panels::page>
    <div x-data class="flex gap-2">
        <div class="w-1/2 ">
            {{$this->contForm}}
            <x-filament::section x-show="$wire.main_id" class="mt-2">
                {{$this->mainArcInfolist}}
            </x-filament::section>

        </div>
        <div class="w-1/2">
            @livewire(\App\Livewire\widgets\TranArcWidget::class,['main_id'=>$main_id])
        </div>
    </div>



</x-filament-panels::page>
