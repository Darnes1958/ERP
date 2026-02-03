

    <script>

        document.addEventListener('livewire:initialized', () => {
        @this.on('gotoitem', (event) => {
            postid = (event.test);
            alert(postid);
            if (postid == 'actual_balance') {

                $("#actual_balance").focus();
                $("#actual_balance").select();
            }
            if (postid == 'kst') {
                $("#kst").focus();
                $("#kst").select();
            }
            if (postid == 'ksm_date') {

                $("#ksm_date").focus();
                $("#ksm_date").select();
            }
            if (postid == 'ksm') {

                $("#ksm").focus();
                $("#ksm").select();
            }
            if (postid == 'ksm_date2') {

                $("#ksm_date2").focus();
                $("#ksm_date2").select();
            }
            if (postid == 'ksm2') {

                $("#ksm2").focus();
                $("#ksm2").select();
            }

            if (postid == 'kst_count') {

                $("#kst_count").focus();
                $("#kst_count").select();

            }
            if (postid == 'main_id') {

                $("#main_id").focus();
                $("#main_id").select();

            }
            if (postid == 'acc') {

                $("#acc").focus();
                $("#acc").select();

            }

            if (postid == 'notes') {
                $("#notes").focus();
                $("#notes").select();
            }
            if (postid == 'sul_begin') {

                $("#sul_begin").focus();
                $("#sul_begin").select();
            }

            if (postid == 'bank_id') {
                $("#bank_id").focus();
                $("#bank_id").select();
            }


            if (postid == 'q1') {
                $("#q1").focus();
                $("#q1").select();
            }
            if (postid == 'price_buy') {

                $("#price_buy").focus();
                $("#price_buy").select();

            }
            if (postid == 'price1') {

                $("#price1").focus();
                $("#price1").select();

            }
            if (postid == 'price2') {

                $("#price2").focus();
                $("#price2").select();

            }

            if (postid == 'supplier_id') {
                $("#supplier_id").focus();
                $("#supplier_id").select();
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
            }
            if (postid == 'price_input') {
                $("#price_input").focus();
                $("#price_input").select();
            }
            if (postid == 'notes') {
                $("#notes").focus();
                $("#notes").select();
            }
            if (postid == 'price_nakdy') {
                $("#price_nakdy").focus();
                $("#price_nakdy").select();
            }
            if (postid == 'price_takseet') {
                $("#price_takseet").focus();
                $("#price_takseet").select();
            }

        });
        });
    </script>


