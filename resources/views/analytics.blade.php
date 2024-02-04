

    <script>

        document.addEventListener('livewire:initialized', () => {
        @this.on('gotoitem', (event) => {
            postid = (event.test);

            if (postid == 'q1') {
                $("#q1").focus();
                $("#q1").select();
            }
            if (postid == 'price_buy') {

                $("#price_buy").focus();
                $("#price_buy").select();

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
                $("#price_input").focus();
                $("#price_input").select();
            }
        });
        });
    </script>


