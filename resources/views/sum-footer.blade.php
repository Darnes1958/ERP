@php use Filament\Tables\Table; @endphp

<x-table.row>

   @foreach ($columns as $column)
      <x-table.cell
              wire:loading.remove.delay
              wire:target="{{ implode(',', Table::LOADING_TARGETS) }}"
      >
          @for ($i = 0; $i < count($calc_columns); $i++ )
              @if ($column->getName() == $calc_columns[$i])
                  <div class="filament-tables-column-wrapper">
                      <div class="filament-tables-text-column px-2 py-1 flex w-full justify-start text-start">
                          <div class="inline-flex items-center space-x-1 rtl:space-x-reverse">
                              @if($column->getName() =='acc' || $column->getName() =='BankName')
                                  <span class="font-medium dark:text-gray-800">
                                      اجمالي الصفحة
                                    </span>
                              @else
                                <span class="font-medium dark:text-gray-800">
                                    {{ number_format($records->sum($calc_columns[$i]),0, '', ',')   }}
                                </span>
                              @endif
                          </div>
                      </div>
                  </div>

              @endif
          @endfor

      </x-table.cell>
   @endforeach

</x-table.row>
<x-table.row>

    @foreach ($columns as $column)
        <x-table.cell
                wire:loading.remove.delay
                wire:target="{{ implode(',', Table::LOADING_TARGETS) }}"
        >

            @for ($i = 0; $i < count($calc_columns); $i++ )

                @if ($column->getName() == $calc_columns[$i])
                    <div class="filament-tables-column-wrapper">
                        <div class="filament-tables-text-column px-2 py-1 flex w-full justify-start text-start">
                            <div class="inline-flex items-center space-x-1 rtl:space-x-reverse">
                                @if($column->getName() =='acc' || $column->getName() =='BankName')
                                    <span class="font-medium dark:text-gray-800">
                                      الاجمالي الكلي
                                    </span>
                                @endif
                                    @if($column->getName() =='sul' || $column->getName() =='main_sum_sul')
                                     <input wire:model.live="sul"  class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='pay' || $column->getName() =='main_sum_pay')
                                        <input wire:model.live="pay" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='main_sum_raseed')
                                        <input wire:model.live="raseed" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='main_count')
                                        <input wire:model.live="count" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='main_sum_over_kst')
                                        <input wire:model.live="over" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='main_sum_tar_kst')
                                        <input wire:model.live="tar" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif
                                    @if($column->getName() =='wrong_kst')
                                        <input wire:model.live="wrong" class="text-sm w-32 font-medium px-0 mx-0 bg-transparent border-none dark:text-gray-800" readonly/>
                                    @endif


                            </div>
                        </div>
                    </div>

                @endif
            @endfor

        </x-table.cell>
    @endforeach

</x-table.row>

