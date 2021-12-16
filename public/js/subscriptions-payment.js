(function($) {
    $(document).ready(function() {
        $('.payment-col').on('click',function() {
            var id = $(this).data('id');
            $('.payment-col .payment-title').removeClass('payment-active');
            $('.payment-title', this).addClass('payment-active');
            $('#description-'+id).slideDown();
            $('.payment-body').not('#description-'+id).slideUp();
        });
    });
})(jQuery);