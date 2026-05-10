<x-filament-widgets::widget>
    <x-filament::section>
        @if(\Illuminate\Support\Facades\Auth::user()->hasMessage())
            {{$this->form}}
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
