<x-filament-panels::page>
    <div class="flex w-full gap-2 pt-2">
        <div class="w-4/12  ">
          {{$this->form}}
        </div>
        <div class="w-8/12">
          {{$this->table}}
        </div>
    </div>

</x-filament-panels::page>

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
        @this.on('goto', (event) => {
            postid = (event.test);

            console.info(postid)
            if (postid == 'order_date') {
                $("#order_date").focus();
                $("#order_date").select();
            }
            if (postid == 'customer_id') {
                $("#customer_id").focus();
                $("#customer_id").select();
            }
            if (postid == 'place_id') {
                $("#place_id").focus();
                $("#place_id").select();
            }
            if (postid == 'price_type_id') {
                $("#price_type_id").focus();
                $("#price_type_id").select();
            }
            if (postid == 'pay') {
                $("#pay").focus();
                $("#pay").select();
            }
            if (postid == 'barcode_id') {
                $("#barcode_id").focus();
                $("#barcode_id").select();
            }if (postid == 'item_id') {
                $("#item_id").focus();
                $("#item_id").select();
            }if (postid == 'q1') {
                $("#q1").focus();
                $("#q1").select();
            }
            if (postid == 'q2') {
                $("#q2").focus();
                $("#q2").select();
            }if (postid == 'price1') {
                $("#price1").focus();
                $("#price1").select();
            }
            if (postid == 'price2') {
                $("#price2").focus();
                $("#price2").select();
            }
        });
        });
    </script>
@endpush
