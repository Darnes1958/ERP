<div class="flex">
    @if(\Illuminate\Support\Facades\Auth::user()->is_prog)

        {{$this->form}}

    @endif



</div>
