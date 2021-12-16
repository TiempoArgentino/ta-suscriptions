(function( $ ) {
    /**
     * This code depends of admin, if you change any thing of admin or admin forms this code die... Thanks, we love you...
    */
	'use strict';
	$(document).ready(function() {
        var i=1;
        $('.add-price').on('click', function() {
            i++;
            $('#prices-container').append('<div id="price-extra-'+i+'" style="margin-top:5px"><input type="text" name="prices_extra[]" style="max-width:80px" value="" /><span class="dashicons-trash dashicons remove-price" data-id="#price-extra-'+i+'" style="cursor:pointer"></span></div>');
        });
    });
    $(document).on('click', '.remove-price', function() {
        var id = $(this).data('id');
        $(id).remove();
    });
    $(document).on('change', '#time', function() {
        if($(this).val() === '') {
            $('#expires').attr("value", "");
        }
    });
    $(document).ready(function() {
        $('.post-type-memberships input#title').attr('disabled',true).hide();
        $('.post-type-memberships #edit-slug-box').hide();
    });
    $(document).ready(function() {
        $('.panel-header').on('click', function(){
            var id = $(this).data('id');
            $('.panel-form').not(id).hide();
            $(id).toggle();
        });
    });
    $(document).ready(function(){
        $('.open-form-status').on('click', function() {
            var id = $(this).data('id');
            if($('.form-edit-status').is(':visible')) {
                $('.form-edit-status').not('#form-edit-status-' + id).slideUp();
            }
            $('#form-edit-status-' + id).slideDown();
        });
        
    });
    $(document).ready(function(){
        if($('#donation_check').is(':checked')){
            $('.show-discount').show();
        } else {
            $('#_discount').val('');
        }

        $('#donation_check').on('click',function(){
            if(!$('#_discount').is(':visible')){
                $('.show-discount').show();
            } else {
                $('#_discount').val('');
                $('.show-discount').hide();
            }
        });

    });
})( jQuery );
