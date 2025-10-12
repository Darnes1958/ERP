<div>
    @if($show)
      <div class="mb-4">
          {{$this->mainInfolist}}
      </div>


    <form wire:submit="create">
        {{ $this->form }}

        <x-button type="submit" class="mt-2 bg-primary-500">
            تخزين
        </x-button>
    </form>
    @endif
</div>
