@php use Filament\Tables\Table; @endphp
<x-table.row>


    @foreach ($columns as $column)
        <x-table.cell
            wire:loading.remove.delay
            wire:target="{{ implode(',', \Filament\Tables\Table::LOADING_TARGETS) }}"
        >
            @for ($i = 0; $i < count($calc_columns); $i++ )
                @if ($column->getName() == $calc_columns[$i])
                    <div class="filament-tables-column-wrapper">
                        <div class="filament-tables-text-column px-2 py-2 flex w-full justify-start text-start">
                            <div class="inline-flex items-center space-x-1 rtl:space-x-reverse ">
                                @if($calc_columns[$i]=='mden' || $calc_columns[$i]=='safi')
                                 <span class="font-medium  text-danger-600">
                                    {{ number_format($records->sum($calc_columns[$i]),2, '.', ',') }}
                                 </span>
                                @endif
                                @if($calc_columns[$i]!='mden' && $calc_columns[$i]!='safi' && $calc_columns[$i]!='raseed')
                                 <span class="font-medium text-indigo-700">
                                    {{ number_format($records->sum($calc_columns[$i]),2, '.', ',') }}
                                 </span>
                                @endif
                                    @if($calc_columns[$i]=='raseed' )
                                        @if($records->sum($calc_columns[$i])<0)
                                            <span class="font-medium text-danger-600">
                                             {{ number_format($records->sum($calc_columns[$i]),2, '.', ',') }}
                                         </span>
                                        @else
                                            <span class="font-medium text-indigo-700">
                                            {{ number_format($records->sum($calc_columns[$i]),2, '.', ',') }}
                                         </span>
                                        @endif

                                    @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endfor
        </x-table.cell>

    @endforeach
</x-table.row>
