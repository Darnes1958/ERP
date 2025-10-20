<div class="gap-2">
    <div class="flex  mb-2  ">

            {{ $this->form }}


    </div>
    <div class="flex gap-2">
        <div class="w-1/2 text-xs ">
            <div >
                {{ $this->mainArcInfolist }}
            </div>
        </div>

        <div class="w-1/2">
            {{ $this->table }}
            @if($mainRec->overkstable->count()>0)
                @livewire(\App\Livewire\widget\OverWidget::class,['main_id'=>$mainId])
            @endif
        </div>
    </div>


</div>

@

