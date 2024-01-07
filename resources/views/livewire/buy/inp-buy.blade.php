<div>
    <div class="w-full ">
        {{$this->buyFormBlade}}
    </div>
    <div class="flex w-full gap-2 pt-2">
        <div class="w-4/12  ">
            {{$this->buytranFormBlade}}
        </div>
        <div class="w-8/12">
            {{$this->table}}
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
        @this.on('goto', (event) => {
            postid = (event.test);

            if (postid == 'order_date') {
                $("#order_date").focus();
                $("#order_date").select();
            }
            if (postid == 'supplier_id') {
                $("#supplier_id").focus();
                $("#supplier_id").select();
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
            }if (postid == 'price_input') {
                $("#p1").focus();
                $("#p1").select();
            }
        });
        });
    </script>
@endpush



