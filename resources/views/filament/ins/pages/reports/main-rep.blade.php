<x-filament-panels::page>

    <div  class="gap-2">
        <div class="flex">
            <div class=" mb-2">
                {{ $this->form }}
            </div>
        </div>
        <div class="flex gap-2">
            <div class="w-1/2  ">
                <div >
                    {{ $this->mainInfolist }}
                </div>
                <div class="mt-4">
                    @livewire(\App\Livewire\widget\SelltranWidget::class,['main_id'=>$main_id])
                </div>
                @if($showArc)
                    <div class="mt-4">
                        @livewire(\App\Livewire\widget\ContArc::class,['main_id'=>$main_id])
                    </div>
                @endif

            </div>

            <div class="w-1/2">
                {{ $this->table }}
                @if($mainRec->overkstable->count()>0)
                    @livewire(\App\Livewire\widgets\OverWidget::class,['main_id'=>$main_id])
                @endif
                @if($mainRec->tarkst->count()>0)
                    @livewire(\App\Livewire\widgets\TarWidget::class,['main_id'=>$main_id])
                @endif
            </div>
        </div>
    </div>

</x-filament-panels::page>




