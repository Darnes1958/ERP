<div class="flex">
    @if(\Illuminate\Support\Facades\Auth::id()==1)
        <x-filament::input.wrapper >
            <x-filament::input.select wire:model="status" wire:change="optionSelected">
                <option value={{$name}}>{{$name}}</option>
                @foreach($company as $item)
                    <option value={{$item->Company}}>{{$item->Company}}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    @endif



</div>
