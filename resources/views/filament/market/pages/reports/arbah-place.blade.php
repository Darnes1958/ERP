<x-filament-panels::page>
    {{ $this->form }}
    <div class="flex w-full gap-2">

            <div class="w-6/12 ">
                <div wire:loading class="text-indigo-700">
                    يرجي الإنتظار ...
                </div>
                {{$this->table}}
            </div>
            <div class="w-6/12">
                @livewire(\App\Livewire\widget\ChartArbah::class,['year'=>$year,'place' => $place,])
            </div>


    </div>

</x-filament-panels::page>
