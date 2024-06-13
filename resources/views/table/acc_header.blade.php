<div class="inline-flex items-center space-x-1 rtl:space-x-reverse ">
    @if($balance)
        <span>
            رصيد سابق
        </span>
        <span class="font-medium  text-danger-600">
            مدين {{ number_format($mden,2, '.', ',') }}
        </span>
        <span class="font-medium text-indigo-700">دائن {{ number_format($daen,2, '.', ',') }}</span>
    @endif
</div>
