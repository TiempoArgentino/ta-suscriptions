(function($){
/**
     * Donations
     */
    $(document).ready(function(){
        $('#submit-donations-user-data').on('click',function(){
            var redirect = $('#payment_redirect').val();
            $.ajax({
                type: 'post',
                url: ajax_add_user.url,
                data: {
                    action: ajax_add_user.action,
                    _ajax_nonce: ajax_add_user._ajax_nonce,
                    add_user: ajax_add_user.add_user,
                    name: $('#donations_name').val(),
                    lastname:  $('#donations_lastname').val(),
                    email: $('#donations_email').val()
                },
                beforeSend: function(result) {
                   // $('#message-register-response').html(ajax_register_data.sending);
                },
                success: function(result){
                    if (!$.trim(result)){   
                        window.location.href = redirect;
                        //console.log(result);
                     }
                     console.log(result);
                     $('#donations-response').html(result);
                },
                error: function(result) {
                    console.log('error ' + result);
                }
            });
        });
    })
    $(document).ready(function() {
        $('#custom-next-donations').on('click', function() {
            $.ajax({
                    type: 'post',
                    url: ajax_add_custom_price_data.url,
                    data: {
                        action: ajax_add_custom_price_data.action,
                        _ajax_nonce: ajax_add_custom_price_data._ajax_nonce,
                        add_price_custom: ajax_add_custom_price_data.add_price_custom,
                        donations_id: $('#custom-price-input').data('id'),
                        donations_price:  $('#custom-price-input').val(),
                        donations_name: $('#custom-price-input').data('name'),
                        donations_type: $('#ccustom-next').data('type')
                    },
                    beforeSend: function(result) {
                        //console.log('before ' + $('#custom-price-input').data('id'));
                    },
                    success: function(result){
                       // console.log('success custom ' + result);
                    },
                    error: function(result) {
                        console.log('error ' + result);
                    }
            });
       });
    });

    $(document).ready(function() {
        $('.button-donations').on('click', function() {
            var donations_id = $(this).data('id');
            var donations_price = $(this).data('price');
            var donations_name = $(this).data('name');
            $.ajax({
                type: 'post',
                url: ajax_add_price_data.url,
                data: {
                    action: ajax_add_price_data.action,
                    _ajax_nonce: ajax_add_price_data._ajax_nonce,
                    add_price: ajax_add_price_data.add_price,
                    donations_id: donations_id,
                    donations_price: donations_price,
                    donations_name: donations_name,
                    donations_type: $('#ccustom-next').data('type')
                },
                beforeSend: function(result) {
                   //console.log('before ' + donations_id);
                },
                success: function(result){
                    //console.log('success ' + result);
                },
                error: function(result) {
                    console.log('error ' + result);
                }
            });
        });
    });

   
})(jQuery);