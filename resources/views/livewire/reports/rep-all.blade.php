<div class="text-sm ">

    <div class="flex gap-2  justify-between">
        <div class="flex  gap-6">
            <div class="flex gap-2">
                <x-label  class="text-primary-400" for="radio1" value="{{ __('بالتجميعي') }}"/>
                <x-input type="radio" class="ml-4" wire:model.live="By" name="radio1" value="2" />
            </div>
            <div class="flex gap-2">
                <x-label  class="text-primary-400" for="radio2" value="{{ __('بفروع المصارف') }}"/>
                <x-input type="radio" class="ml-4" wire:model.live="By" name="radio2" value="1"/>
            </div>

        </div>

        @if($bank_id != null)
        <div class="flex gap-1">
            <span class="text-primary-500">طباعة</span>
            @if($rep_name=='All')
                <a  href="{{route('pdfall',['bank_id'=>$bank_id,'By'=>$By])}}"  class="text-primary-500">
                    <x-icon.print/>
                </a>
            @endif
            @if($rep_name=='Mosdada')
                <a  href="{{route('pdfmosdadabank',['Baky'=>$Baky,'bank_id'=>$bank_id,'By'=>$By])}}"  class="text-primary-500">
                    <x-icon.print/>
                </a>
            @endif
            @if($rep_name=='Motakra')
                <a  href="{{route('pdfmotakrabank',['Baky'=>$Baky,'bank_id'=>$bank_id,'By'=>$By])}}"  class="text-primary-500">
                    <x-icon.print/>
                </a>
            @endif
            @if( $rep_name=='Mohasla')
                <a  href="{{route('pdfmohasla',['bank_id'=>$bank_id,'Date1'=>$Date1,'Date2'=>$Date2,'By'=>$By])}}"  class="text-primary-500">
                    <x-icon.print/>
                </a>
            @endif
            @if( $rep_name=='Not_Mohasla')
                <a  href="{{route('pdfnotmohasla',['bank_id'=>$bank_id,'Date1'=>$Date1,'Date2'=>$Date2,'By'=>$By])}}"  class="text-primary-500">
                    <x-icon.print/>
                </a>
            @endif
        </div>
        @endif
    </div>
        <div class=" mt-2 rounded shadow-inner bg-blue-100">
            {{ $this->form }}
        </div>
    @if($rep_name=='Mosdada' || $rep_name=='Motakra' || $rep_name=='All')
    <div class="w-full mt-2">
        {{ $this->table }}
    </div>
    @endif
    @if($rep_name=='Mohasla' )
        <div class="w-full mt-2">
            @livewire('reports.rep-aksat-get' ,['Date1'=>$Date1 ,'Date2'=>$Date2 ,'bank_id'=>$bank_id ,'By'=>$By])
        </div>
    @endif
    @if($rep_name=='Not_Mohasla' )
        <div class="w-full mt-2">
            @livewire('reports.rep-aksat-not-get' ,['Date1'=>$Date1 ,'Date2'=>$Date2 ,'bank_id'=>$bank_id ,'By'=>$By])
        </div>
    @endif


</div>
