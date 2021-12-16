(function ($) {
  /**
   * This login ajax
   */
   function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }

  $(document).ready(function ($) {
    $("#send-login").on("click", function () {

      var username = $("#username").val();
      var password = $("#password").val();
      var redirect_to = $("#redirect_to").val();
      if(username.length < 3 || password.length < 3) {
        return;
      }

      if(!validateEmail(username)){
        return;
      }

      $.ajax({
        type: "post",
        url: ajax_login_data.url,
        data: {
          action: ajax_login_data.action,
          _ajax_nonce: ajax_login_data._ajax_nonce,
          login: ajax_login_data.login,
          username: username,
          password: password,
          redirect_to: ajax_login_data.redirect_to,
        },
        beforeSend: function (result) {
          $("#message-login-response").html(ajax_login_data.sending);
        },
        success: function (result) {
          
          if (result.success) {
            window.location.href = redirect_to;
          } else if (result.data === "member") {
            //console.log(result);
            location.reload();
          } else {
            $("#message-login-response").html(result);
          }
        },
        error: function (result) {
          console.log(result);
        },
      });
    });
  });
  /**
   * Register ajax
   */
  $(document).ready(function () {
    $("#submit-register:not([disabled])").on("click", function () {

      $(this).prop('disabled',true);
      $(this).hide();

      $('#loader-address').removeClass('d-none');

      var first_name = $("#first_name").val();
      var last_name = $("#last_name").val();
      var email = $("#email").val();
      var password = $("#passwd").val();
      var password2 = $("#passwd2").val();
      var register_redirect = $("#register_redirect").val();

      $.ajax({
        type: "post",
        url: ajax_register_data.url,
        data: {
          action: ajax_register_data.action,
          _ajax_nonce: ajax_register_data._ajax_nonce,
          register: ajax_register_data.register,
          first_name: first_name,
          last_name: last_name,
          email: email,
          password: password,
          password2: password2,
          register_redirect: register_redirect,
          subscriptor_type: $('#subscriptor_type').val()
        },
        beforeSend: function (result) {
          $("#message-register-response").html(ajax_register_data.sending);
        },
        success: function (result) {
          if(!result.success) {
            $("#message-register-response").show().html(`<div class="alert alert-danger" role="alert">${result.data}</div>`);
            $('#submit-register').prop('disabled',false);
            $('#submit-register').show();
            $('#loader-address').addClass('d-none');
          } else {
            //$('#submit-register').prop('disabled',true);
            window.location.href = register_redirect;
          }
        },
        error: function (result) {
          console.log(result);
        },
      });
    });
  });
  /**
   * Add suscrption
   */
  $(document).ready(function () {
    $(".button-suscribe").on("click", function () {
      var suscription_id = $(this).data("id");
      var suscription_price = $(this).data("price");
      var suscription_name = $(this).data("name");
      var suscription_type = $(this).data("type");
      var address = $(this).data("address");
      $.ajax({
        type: "post",
        url: ajax_add_price_data.url,
        data: {
          action: ajax_add_price_data.action,
          _ajax_nonce: ajax_add_price_data._ajax_nonce,
          add_price: ajax_add_price_data.add_price,
          suscription_id: suscription_id,
          suscription_price: suscription_price,
          suscription_name: suscription_name,
          suscription_type: suscription_type,
          suscription_address: address,
        },
        beforeSend: function (result) {
          // console.log('before ' + suscription_id);
        },
        success: function (result) {
          // console.log('success ' + result);
        },
        error: function (result) {
          console.log("error " + result);
        },
      });
    });
  });
  $(document).ready(function () {
    $("#custom-next").on("click", function () {
      $.ajax({
        type: "post",
        url: ajax_add_custom_price_data.url,
        data: {
          action: ajax_add_custom_price_data.action,
          _ajax_nonce: ajax_add_custom_price_data._ajax_nonce,
          add_price_custom: ajax_add_custom_price_data.add_price_custom,
          suscription_id: $("#custom-price-input").data("id"),
          suscription_price: $("#custom-price-input").val(),
          suscription_name: $("#custom-price-input").data("name"),
          suscription_type: $("#custom-next").data("type"),
          suscription_address: $("#custom-price-input").data("address"),
        },
        beforeSend: function (result) {
          // console.log('before ' + suscription_id);
        },
        success: function (result) {
          // console.log('success ' + result);
        },
        error: function (result) {
          console.log("error " + result);
        },
      });
    });
  });

  $(document).ready(function () {
    $("#address-button").on("click", function () {
      var state = $("#state").val();
      var city = $("#city").val();
      var address = $("#address").val();
      var number = $("#number").val();
      var floor = $("#floor").val();
      var apt = $("#apt").val();
      var zip = $("#zip").val();
      var bstreet = $("#bstreet").val();
      var observations = $("#observations").val();
      $.ajax({
        type: "post",
        url: ajax_address.url,
        data: {
          action: ajax_address.action,
          _ajax_nonce: ajax_address._ajax_nonce,
          add_address: ajax_address.add_address,
          state: state,
          city: city,
          address: address,
          number: number,
          floor: floor,
          apt: apt,
          zip: zip,
          bstreet: bstreet,
          observations: observations,
        },
        success: function (result) {
          if (result.success) {
            $("#address-container").slideUp(400, function () {
              $("#payment-container").slideDown();
            });
          } else {
            $("#msg-ok").html(result.data[0].message);
          }
        },
        error: function (result) {
          console.log("error " + result);
        },
      });
    });
  });

  $(document).ready(function(){
    $('#discount-button').on('click',function(){
        $('.donation-container').slideUp(400,function(){
            $('#discount-data').slideDown();
        });
    });

    $('#next-contact').on('click', function(){
      $('#discount-data').slideUp(400,function(){
        $('#contact-form').slideDown();
      });
    });


    $('#next-discount').on('click',function(){
        var discount = $('#new_price').val();
        var type= $('#new_price').data('type');
        var name = $('#new_price').data('name');
        var id = $('#new_price').data('id');
        var payment = $(this).data('payment_page');

        $.ajax({
            type:'post',
            url:ajax_add_discount.url,
            data: {
                action: ajax_add_discount.action,
                _ajax_nonce: ajax_add_discount._ajax_nonce,
                add_discount: ajax_add_discount.add_discount,
               discount:discount,
               type:type,
               name:name,
               id:id
            },
            success: function(res){
                if(res.success){
                  window.location.href = payment;
                } else {
                  alert(res.data)
                }
            },
            error: function(res){
                console.log(res);
            }
        })
    });

    $('#send-contact').on('click',function(){
      var name = $('#name_support_us').val();
      var email = $('#email_support_us').val();
      var msg = $('#msg_support_us').val();

      if(name.length < 1) {
        $('#name_support_us').addClass('border border-danger');
      } else if(email.length < 1 || !validateEmail(email)) {
        $('#email_support_us').addClass('border border-danger');
      } else if(msg.length < 1){
        $('#msg_support_us').addClass('border border-danger')
      } else {
        $('#name_support_us').addClass('border border-success');
        $('#email_support_us').addClass('border border-success');
        $('#msg_support_us').addClass('border border-success');
        
        $.ajax({
          type:'post',
          url:ajax_contact.url,
          data:{
            action: ajax_contact.action,
            _ajax_nonce: ajax_contact._ajax_nonce,
            contact:ajax_contact.contact,
            name:name,
            email:email,
            msg:msg
          },
          success: function(res){
            if(res.success){
              $('#contact-form').slideUp(400,function(){
                $('#contact-thankyou').slideDown();
                $('#name-thanks').html(name);
              });
            } else {
              alert(res.data)
            }
          },
          error: function(res){
            console.log(res)
          }
        });
      }
    });
});

})(jQuery);
