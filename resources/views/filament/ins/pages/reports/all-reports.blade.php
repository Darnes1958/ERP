<x-filament-panels::page>
{{$this->form}}
    @if($rep_name=='Mosdada' || $rep_name=='NotMosdada' || $rep_name=='Motakra' || $rep_name=='All')

        <div  class="w-full mt-2">
            <div wire:loading class="text-primary-400">
                يرجي الإنتظار ...
            </div>
            {{$this->table}}


        </div>
    @endif
    @if($rep_name=='Mohasla' )
        <div  class="w-full mt-2">
            <div wire:loading class="text-primary-400">
                يرجي الإنتظار ...
            </div>
            <div class="w-full mt-2">
                @livewire('reports.rep-aksat-get' ,['Date1'=>$Date1 ,'Date2'=>$Date2 ,'bank_id'=>$bank_id ])
            </div>


        </div>

    @endif
    @if($rep_name=='Not_Mohasla' )
        <div  class="w-full mt-2">
            <div wire:loading class="text-primary-400">
                يرجي الإنتظار ...
            </div>
            <div class="w-full mt-2">
                @livewire('reports.rep-aksat-not-get' ,['Date1'=>$Date1 ,'Date2'=>$Date2 ,'bank_id'=>$bank_id ])
            </div>


        </div>

    @endif
</x-filament-panels::page>
