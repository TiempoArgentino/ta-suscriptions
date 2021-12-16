(function( $ ) {
	'use strict';
	$(document).ready(function() {
		/**
		 * Price button
		 */
		$('.price').on('click',function() {
			var id = $(this).data('id');
			var price = $(this).data('price');
			$('.price').not(this).removeClass('selected');
			$(this).addClass('selected');
			$('.button-suscribe').not('#button' + id).attr('disabled',true).removeAttr('data-price');
			$('#button' + id).attr('disabled',false).attr('data-price',price);
		});
		/**
		 * Custom price
		 */
		$('.open-price').on('click', function() {
			var id = $(this).data('id');
			var min = $(this).data('min');
			var title = $(this).data('title');
			var address = $(this).data('address');

			$('.button-suscribe').attr('disabled',true).removeAttr('data-price');
			if($('.price').hasClass('selected')) {
				$('.price').removeClass('selected');
			}

			$('#subscriptions-loop').slideUp(400,function(){
				$('#custom-price-row').slideDown();
			});
			
			$('#custom-price-input').attr('data-role',$(this).data('role')).attr('min',min).attr('data-id',id).attr('data-name',title).data('address',address).val(min);
			$('#price-min-span').append(min);
			
		});
		/**
		 * Cancel custom price
		 */
		$('.cancel-custom-price').on('click', function() {
			$('#custom-price-input').removeAttr('min').removeAttr('data-id').val('');
			$('#subscriptions-loop').slideDown();
			$('#custom-price-row').slideUp();
		});
		/**
		 * Register login
		 */
		$('.button-suscribe').on('click', function() {
			var id = $(this).data('id');
			var price = $(this).data('price');
			var address = $(this).data('address');
			$('#subscriptions-loop').slideUp(400, function(){
				$('#login-register-loop').slideDown();
			});
			
		});

		$('#custom-next').on('click', function() {
			var val = $('#custom-price-input').val();
			var min = $('#custom-price-input').attr('min');
			var address = $('#custom-price-input').data('address');

			if(parseInt(val) < parseInt(min))
			{
				$('#custom-price-input').addClass('is-invalid');
				$('#minimum').html('<strong>' + min + '</strong>');
				return;
			}
			$('#custom-price-row').slideUp(400,function(){
				$('#login-register-loop').slideDown();
			});
			
		});

		$('#login-title-loop').on('click', function() {
			$('#login-form-loop').slideDown();
			$('.form-title').slideUp();
			if($('#register-form-loop').is(':visible')) {
				$('#register-form-loop').slideUp();
			}
		});

		$('#register-title-loop').on('click', function() {
			$('#register-form-loop').slideDown();
			$('.form-title').slideUp();
			if($('#login-form-loop').is(':visible')) {
				$('#login-form-loop').slideUp();
			}
		});
		$('.cancel-login .btn').on('click', function() {
			$('.form-title').slideDown();
			if($('#login-form-loop').is(':visible')) {
				$('#login-form-loop').slideUp();
			}
			if($('#register-form-loop').is(':visible')) {
				$('#register-form-loop').slideUp();
			}
		});
	});
	$(document).ready(function() {
		$('#continue-next').on('click',function() {
			var link = $(this).data('payment_page');
			console.log(link);
			window.location.href = link;
		});
	});
	/**
	 * donations
	 */
	$(document).ready(function() {
		$('#continue-next-donations').on('click',function() {
			var link = $(this).data('payment_page');
			console.log(link);
			window.location.href = link;
		});

		$('.button-donations').on('click', function() {
			var id = $(this).data('id');
			var price = $(this).data('price');
			$('#subscriptions-loop').slideUp();
			$('#user-donations-data').slideDown();
		});
	
		$('#custom-next-donations').on('click', function() {
			var val = $('#custom-price-input').val();
			var min = $('#custom-price-input').attr('min');
	
			if(parseInt(val) < parseInt(min))
			{
				$('#custom-price-input').addClass('is-invalid');
				$('#minimum').html('<strong>' + min + '</strong>');
				return;
			}
	
			$('#custom-price-row').slideUp();
			$('#user-donations-data').slideDown();
		});
		
	});

	$(document).ready(function(){
		$('#edit_donation').on('click',function(){
			$('#become-a-member').slideUp(400,function(){
				$('#membership-edit-donations').slideDown();
			});
		});
		$('#cancel-edit').on('click',function(){
			$('#become-a-member').slideDown(400,function(){
				$('#membership-edit-donations').slideUp();
			});
		});
	});

	$(document).on('change','#subs_change',function(){
		console.log(subscriptions_ajax_object.pricesURL)
		var physical = $(this).find(":selected").data("physical");
		  var min_price = $(this).find(":selected").data("min");
		  if (physical === 0) {
			$("#physical").show();
		  } else {
			$("#physical").hide();
		  }
		  $('#amount-subscription').val(min_price).prop('min',min_price);
		  $('#min-price-show').html(min_price);
		  $('#payment_min_s').val(min_price);
		  $('#subscription_name').val($(this).find(":selected").text());
		  if($('#paper').is(':checked')){
				$('#paper').prop('checked',false);
			}

			$('#users-prices-container').html('Cargando precios...');
		
		  $.ajax({
				url: subscriptions_ajax_object.pricesURL,
				type: 'POST',
				data: {
					subscription: $(this).val(),
				},
				success: function(response) {
					if(!response.success){
						return;
					}

					var html = `Selecionar monto:`;
					html += `<div class="amount" style="margin:0 4px"><span class="price-select price">${response.data[0]}</span></div>`;
					const prices = response.data[1];
					for(let price in prices) {
						html += `<div class="amount" style="margin:0 4px"><span class="price-select price">${prices[price]}</span></div>`;
					}

					$('#users-prices-container').html(html);
				},
				error: function(error) {
					console.log(error)
				}
		  })
	  });
	
	$(document).ready(function(){
		$('#paper').on('click',function(){
			var price = $('#amount-subscription').prop('min');
			if($('#paper').is(':checked')){
				$('#amount-subscription').val(parseInt($('#amount-subscription').val()) + parseInt($('#paper').val()));
			} else {
				$('#amount-subscription').val(price);
			}	
		});
		if($('#paper').is(':checked')){
			$('#amount-subscription').val(parseInt($('#amount-subscription').val()) + parseInt($('#paper').val()) );
		} else {
			$('#amount-subscription').val(parseInt($('#amount-subscription').val()));
		}
	});

	$(document).on('click','#edit_subscription',function(){
		$('#membership-edit-subscriptions').slideDown();
	  });
	$(document).on('click','#cancel-edit-subscription',function(){
		$('#membership-edit-subscriptions').slideUp();
	});

	$(document).on('click','.price-select',function(){
		
		if($('#paper').is(':checked')){
			$('#amount-subscription').val(parseInt($(this).text()) + parseInt($('#paper').val()) );
		} else {
			$('#amount-subscription').val($(this).text());
		}
	});

})( jQuery );
