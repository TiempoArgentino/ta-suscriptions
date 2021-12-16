
//const pay = document.getElementById('paymentMP');
const pay = document.querySelector('.send-edit-subscription');
const formSubscriptions = document.getElementById('paymentFormmp');
const cardNumber = document.getElementById('cardNumber');


if (typeof (cardNumber) != 'undefined' && cardNumber != null) {
    pay.disabled = true;
    /** dato para cosas */
    window.Mercadopago.setPublishableKey(mp_vars.public_key);
    /** los tipos de documentos, funciona con un select que tiene que ser asi: <select id="docType" name="docType" data-checkout="docType" type="text"></select>  */
    window.Mercadopago.getIdentificationTypes();
    /** id de metodo de pago */
    cardNumber.addEventListener('change', guessPaymentMethod);
    /** leemos los 6 digitos de la tarjeta */
    function guessPaymentMethod(event) {
        let cardnumber = document.getElementById("cardNumber").value;
        if (cardnumber.length >= 6) {
            let bin = cardnumber.substring(0, 6);
            window.Mercadopago.getPaymentMethod({
                "bin": bin
            }, setPaymentMethod);
        }
    };
    /** vemos el ID de metodo de pago */
    function setPaymentMethod(status, response) {
        if (status == 200) {
            let paymentMethod = response[0];
            document.getElementById('paymentMethodId').value = paymentMethod.id;
            //getIssuers(paymentMethod.id);
            //console.log(paymentMethod.id);
        } else {
            console.log(response);
            const error_msg = document.getElementById('error-tarjeta');
            error_msg.display = 'block';
            error_msg.innerHTML = '';
            error_msg.append('Ocurrio un error con tu tarjeta');
        }
    }
    /** generar token necesita el tipo de doucmento y el numero o no anda. */

    /* paramos el form porque demora en generarlo */
    doSubmit = false;

    function getCardToken(event) {
        event.preventDefault();
        document.getElementById('panel-mp-buttons').style.display = 'none';
        document.getElementById('msg-edit-mp').innerHTML = 'Un momento por favor...';
        if (!doSubmit) {
            window.Mercadopago.createToken(formSubscriptions, setCardTokenAndPay);
            return false;
        }
    };

    function setCardTokenAndPay(status, response) {
        if (status == 200 || status == 201) {
            let card = document.getElementById('token');
            card.value = response.id;
            doSubmit = true;
            document.getElementById('msg-edit-mp').innerHTML = 'La membresía se edito correctamente...';
            setTimeout(() => {
                document.getElementById('msg-edit-mp').innerHTML = 'Recargando...';
                formSubscriptions.submit();
            }, 1500)

        } else {
            console.log(JSON.stringify(response, null, 4));
            const error_msg = document.getElementById('error-tarjeta');
            error_msg.display = 'block';
            error_msg.innerHTML = '';
            error_msg.append('Ocurrio un error con tu tarjeta');

        }
    };

    const inputCode = document.getElementById('securityCode');
    inputCode.addEventListener('keyup', e => {
        if (inputCode.value.length > 2) {
            pay.disabled = false;
        }
    });


    if (pay) {

        pay.addEventListener('click', getCardToken);
    }

}
