<div class="inline-flex items-center space-x-1 rtl:space-x-reverse ">
    @if($balance)
        <span>
            رصيد سابق
        </span>
       @if($last_raseed==0)
            <span class="font-medium  text-white">
              {{ number_format($last_raseed,2, '.', ',') }}
        </span>
        @else

            @if($last_mden>$last_daen)
            <span class="font-medium  text-danger-600">
                 مدين {{ number_format($last_raseed,2, '.', ',') }}
            </span>
            @else
                <span class="font-medium  text-indigo-700">
                 دائن {{ number_format($last_raseed,2, '.', ',') }}
            </span>

            @endif
        @endif

    @endif
</div>
