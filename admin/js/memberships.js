/**
 * TODO:agregar editar la membresía
 */
var $ = (selector) => document.querySelector(selector);

const validateEmail = (email) => {
    return String(email)
        .toLowerCase()
        .match(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        );
};

function fadeIn(el, display) {
    el.style.opacity = 0;
    el.style.display = display || "block";
    (function fade() {
        var val = parseFloat(el.style.opacity);
        if (!((val += .1) > 1)) {
            el.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
};
function fadeOut(el) {
    el.style.opacity = 1;
    (function fade() {
        if ((el.style.opacity -= .1) < 0) {
            el.style.display = "none";
        } else {
            requestAnimationFrame(fade);
        }
    })();
};

if ($('#msg-create-membership')) {
    function msg(txt, cls) {
        fadeIn($('#msg-create-membership'), 'block');
        $('#msg-create-membership').innerHTML = '';
        $('#msg-create-membership').classList.remove('notice', cls, 'is-dismissible');
        $('#msg-create-membership').classList.add('notice', cls, 'is-dismissible');
        $('#msg-create-membership').innerHTML = `<p>${txt}</p>`;
        setTimeout(() => {
            $('#msg-create-membership').innerHTML = '';
            $('#msg-create-membership').classList.remove('notice', cls, 'is-dismissible');
            fadeOut($('#msg-create-membership'));
        }, 4000);
    }
}

if ($('#modal-membership-new')) {
    const modal = $('#modal-membership-new');
    const openForm = $('#new-membership');
    openForm.addEventListener('click', (e) => {
        e.preventDefault();
        fadeIn(modal, 'block');
    });
    $('#membership-close-form').addEventListener('click', () => {
        window.location.reload();
    })
}

/** subscriptions users */
if ($('#search-user-button')) {
    $('#search-user-button').addEventListener('click', () => {
        fadeOut($('#create-user-button'))
        fadeOut($('#search-user-button'))
        fadeIn($('#search-user-member'), 'block');
    });
    $('#cancel-search').addEventListener('click', () => {
        fadeOut($('#search-user-member'))
        fadeIn($('#create-user-button'), 'inline-block');
        fadeIn($('#search-user-button'), 'inline-block');
    })
}

var container = $('#search-user-member-results');
if (typeof (container) != 'undefined' && container != null) {
    container.innerHTML = '';
}


if ($('#search-user-member-input')) {
    $('#search-db').addEventListener('click', async () => {
        container.innerHTML = '';
        if ($('#search-user-member-input').value.length > 3) {

            fadeOut($('#user-buttons'));
            fadeIn(document.querySelector('.loading-user'), 'block');
            fadeOut($('#search-user-member-input'));

            const users = await searchUser($('#search-user-member-input').value);

            if (!users.success) {
                msg(users.data, 'notice-error');
                fadeIn($('#user-buttons'), 'block');
                fadeIn($('#search-user-member-input'), 'block');
                fadeOut(document.querySelector('.loading-user'));
                return;
            }

            fadeOut(document.querySelector('.loading-user'));
            $('#search-user-member-input').value = '';

            var userData = '';

            for (let user in users.data) {
                userData += `<span class="user-data-item" title="Seleccionar usuario" data-userid="${users.data[user].ID}" data-useremail="${users.data[user].user_email}">Encontrado: ${users.data[user].display_name} - ${users.data[user].user_email}</span>`;
            }

            container.innerHTML += userData;
            fadeIn($('#search-user-member-results'), 'block');
            fadeIn($('#cancel-search-user'), 'block');

            const list = document.querySelectorAll('.user-data-item');

            list.forEach(el => {
                el.addEventListener('click', async () => {
                    fadeIn(document.querySelector('.loading-user'), 'block');
                    const user_id = el.dataset.userid;
                    $('#user_id_membership').value = user_id;
                    const subscription = await searchSubscription(user_id);
                    //   console.log(subscription);
                    if (subscription.success) {
                        $('#search-user-member-results').innerHTML = `<p><strong>Usuario seleccionado: ${el.dataset.useremail}</strong></p>`;
                        $('#search-user-member-results').style.backgroundColor = 'green';
                        $('#search-user-member-results').style.color = 'white';
                        $('#search-user-member-results').style.padding = '5px 10px';
                        fadeOut($('#cancel-search-user'));
                        fadeOut(document.querySelector('.loading-user'));
                        fadeIn($('#subscription-exists'), 'block');
                        return;
                    }

                    $('#search-user-member-input').value = el.dataset.useremail;
                    $('#user_id').value = user_id;
                    $('#user_email').value = el.dataset.useremail;
                    $('#search-user-member').innerHTML = `<p><strong>Usuario seleccionado: ${el.dataset.useremail}</strong></p>`;
                    $('#search-user-member').style.backgroundColor = 'green';
                    $('#search-user-member').style.color = 'white';
                    $('#search-user-member').style.padding = '5px 10px';
                    $('#subscription-exists').remove();
                    fadeIn($('#subscription-not-exists'), 'block');

                    //   fadeOut(document.querySelector('.loading-user'));
                    //  fadeIn($('#select-user-button'), 'block');

                    container.innerHTML = '';
                })
            });
        }

    });

}

function hideContainers() {
    fadeOut($('#subscription-exists'));
    $('#search-user-member-input').value = '';
    fadeIn($('#search-user-member-input'), 'block');
    fadeIn($('#user-buttons'), 'block');
    $('#search-user-member-results').innerHTML = '';
    fadeOut($('#search-user-member-results'));
    container.innerHTML = '';
    fadeIn($('#select-member'), 'block');
    $('#search-user-member-results').style.backgroundColor = 'transparent';
    
    $('#search-user-member').style.backgroundColor = 'transparent';
    $('#search-user-member-results').style.color = '#3c434a';
    $('#search-user-member-results').style.padding = null;
    $('#search-user-member').style.color = '#3c434a';
    $('#search-user-member').style.padding = null;
}

var subscriptionExistsDiv = $('#cancel-subscription-exists');
if (subscriptionExistsDiv != null) {
    subscriptionExistsDiv.addEventListener('click', () => {
        hideContainers()
    })
}

if ($('#cancel-search-user')) {
    $('#cancel-search-user').addEventListener('click', () => {
        fadeOut($('#cancel-search-user'));
        hideContainers()
    })
};

if ($('#cancel-new-subscription')) {
    $('#cancel-new-subscription').addEventListener('click', () => {
        window.location.reload();
    });
}

const searchUser = async (email) => {
    const getUser = await fetch(api_subscriptions.getUser, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email
        })
    });
    const response = await getUser.json();
    return response;
}

const searchSubscription = async (user_id) => {
    const getSubscription = await fetch(api_subscriptions.getSubscription, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id
        })
    });
    const response = await getSubscription.json();
    return response;
};
/* edit membership */
const editMembershipButton = $('#edit-subscription-exists');

if (typeof (editMembershipButton) != 'undefined' && editMembershipButton != null) {
    editMembershipButton.addEventListener('click', async () => {
        const showMembership = $('#edit-membership');
        const editmsg = $('#edit-membership-msg');
        fadeOut($('#subscription-exists'));
        fadeIn(showMembership, 'block');
        editmsg.innerHTML = 'Recuperando datos, un momento por favor...';
        const user_id = document.querySelector('#user_id_membership').value;
        const membership = await getUserMembership(user_id);

        // console.log(membership);

        if (!membership.success) {
            fadeIn($('#subscription-not-exists'), 'block');
            return;
        }
        const membershipInfoShow = await getMembershipInfo(membership.data[0]);
        if (!membershipInfoShow.success) {
            editmsg.innerHTML = 'Error al recuperar los datos de la suscripción';
            return;
        }
        // console.log(membershipInfoShow.data);
        fadeOut(editmsg);
        editmsg.innerHTML = '';

        fadeIn($('#edit-membership-form'), 'block');
        $('#mem-ref').innerHTML = membershipInfoShow.data.reference;
        $('#mem-name').innerHTML = membershipInfoShow.data.subscription;
        $('#id_membership').value = membershipInfoShow.data.id;
        // $('#user_id_membership').value = membershipInfoShow.data.user_id;

        //bank
        if (membershipInfoShow.data.payment_method_id) {
            $('#cbu').value = membershipInfoShow.data.payment_data.CBU;
            $('#dni').value = membershipInfoShow.data.payment_data.DNI;
            $('#cuil').value = membershipInfoShow.data.payment_data.CUIL;
        }
    });

    const selectSubscription = $('#edit-select-subscription');
    const priceDiv = $('#edit-membership-price');
    const selectPrice = $('#edit-select-price');
    const cancelCustomPrice = $('.cancel-edit');
    const cancelEnd = $('#cancel-edit');
    const customPrice = $('#custom-price');
    const changePayment = $('#change-payment');
    const paymentSelect = $('#edit-membership-payment');
    const selectPayment = $('#edit-select-payment');
    const getMPInfo = $('#get-mp-info');
    const emailMP = $('#email_mp');
    const editEnd = $('#edit-end');


    selectSubscription.addEventListener('change', async () => {
        fadeIn($('#edit-membership-msg'), 'block');
        $('#edit-membership-msg').innerHTML = 'Recuperando datos, un momento por favor...';

        for(i = $('#edit-select-price').length - 1; i >= 0; i--) {
            $('#edit-select-price').remove(i);
        }

        const customPriceOption = document.createElement('option');
        customPriceOption.value = 'custom';
        customPriceOption.innerHTML = 'Precio personalizado';
        $('#edit-select-price').append(customPriceOption);

        const prices = await subscriptionPrices(selectSubscription.value);
        if (!prices.success) {
            $('#edit-membership-msg').innerHTML = 'Error al recuperar los precios de la suscripción';
            return;
        }
        fadeOut($('#edit-membership-msg'));
        fadeIn($('#edit-membership-price'), 'block');

        $('#name_subscription').value = selectSubscription.options[selectSubscription.selectedIndex].text;
        $('#id_subscription').value = selectSubscription.value;

        prices.data.forEach(el => {
            var option = document.createElement('option');
            option.value = el;
            option.innerHTML = el;
            $('#edit-select-price').appendChild(option);
        });
    });

    selectPrice.addEventListener('change', async () => {
        if (selectPrice.value == 'custom') {
            fadeIn($('#custom-price-div'), 'block');
            return;
        }
        customPrice.value = '';
        fadeIn($('#edit-finish'), 'block');
        $('#price_subscription').value = selectPrice.value;
        fadeOut(priceDiv);
    });

    cancelCustomPrice.addEventListener('click', () => {
        fadeOut(cancelCustomPrice.parentElement);
        fadeOut($('#edit-finish'));
        selectPrice.value = '';
        selectPrice.style.display = 'block';
    });

    customPrice.addEventListener('keyup', () => {
        if (customPrice.value.length >= 2) {
            fadeIn($('#edit-finish'), 'block');
            $('#price_subscription').value = customPrice.value;
        }
    });

    changePayment.addEventListener('click', () => {
        fadeIn(paymentSelect, 'block');
        fadeOut(cancelEnd.parentElement);
    });


    selectPayment.addEventListener('change', () => {
        if (selectPayment.value == 'mp') {
            fadeIn($('#mp-data'), 'block');
            if ($('#bank-data').offsetParent != null) {
                fadeOut($('#bank-data'));
            }
        }

        if (selectPayment.value == 'bank') {
            fadeIn($('#bank-data'), 'block');
            if ($('#mp-data').offsetParent != null) {
                fadeOut($('#mp-data'))
            }
            fadeIn($('#edit-finish'), 'block');
        }
    });

    getMPInfo.addEventListener('click', async () => {
        getMPInfo.style.display = 'none';
        mpInfoFetch(getMPInfo, emailMP, $('#show-info'), $('#loading-mp'), $('#edit-finish'));
    });

    cancelEnd.addEventListener('click', () => {
        $('#modal-membership-new').innerHTML = 'Cancelando, un momento por favor';
        window.location.reload();
    });

    editEnd.addEventListener('click', async () => {
        const payment_id = selectPayment.value;
        if (payment_id == 'bank') {
            if(!$('#cbu').value || !$('#dni').value || !$('#cuil').value) {
                alert('Debe completar los datos de la cuenta bancaria');
                return;
            }

            if($('#cbu').value.length > 22){
                alert('El CBU no puede tener mas de 22 numeros');
                return;
            }

            if($('#dni').value.length > 8){
                alert('El DNI no puede tener mas de 8 numeros');
                return;
            }

            if($('#cuil').value.length > 11){
                alert('El CUIL no puede tener mas de 11 numeros');
                return;
            }

            $('#payment_data').value = bankData(
                $('#cbu').value,
                $('#dni').value,
                $('#cuil').value,
            );
        }

        fadeOut($('#edit-finish'));
        
        fadeIn($('#msg-finish-edit'), 'block');

       

        const update = await updateMembership(
            $('#id_membership').value,
            $('#user_id_membership').value,
            $('#id_subscription').value,
            $('#name_subscription').value,
            $('#price_subscription').value,
            payment_id,
            $('#payment_data').value
        );

        if (!update.success) {
            fadeOut($('#msg-finish-edit'));
            fadeIn($('#msg-finish-edit'), 'block');
            msg('Ocurrio un error al actualizar la membresía', 'notice-error')
            return;
        }

        $('#modal-membership-new').innerHTML = `Membresía actualizada, un momento por favor...`;
        window.location.reload();
    });
}

const getUserMembership = async (user_id) => {
    const getMembership = await fetch(api_subscriptions.getMembership, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id
        })
    });
    const response = await getMembership.json();
    return response;
}

const getMembershipInfo = async (membership_id) => {
    const membershipInfo = await fetch(api_subscriptions.getMembershipInfo, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            membership_id
        })
    });
    const response = await membershipInfo.json();
    return response;
}

const getMPInfoApi = async (email) => {
    const mpinfo = await fetch(api_subscriptions.getMPInfo, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email
        })
    });
    const response = await mpinfo.json();
    return response;
}

function bankData(cbu, dni, cuil) {
    const paymentData = {
        'CBU': cbu,
        'DNI': dni,
        'CUIL': cuil,
    };
    return JSON.stringify(paymentData);
}

async function mpInfoFetch(button, field, infoDiv, loader, finish) {
    infoDiv.innerHTML = '';

    if (field.value.length < 5) {
        infoDiv.innerHTML = '<p><strong>FALTA EL EMAIL O NO ES UN EMAIL VALIDO</strong></p>';
        button.style.display = 'inline-block';
        return;
    }

    if (!validateEmail(field.value)) {
        infoDiv.innerHTML = '<p><strong>FALTA EL EMAIL O NO ES UN EMAIL VALIDO</strong></p>';
        button.style.display = 'inline-block';
        return;
    }

    loader.style.display = 'inline-block';
    const info = await getMPInfoApi(field.value);

    if (!info.success) {
        msg('Error al procesar la llamada a Mercadopago', 'notice-error')
        loader.style.display = 'none';
        button.style.display = 'inline-block';
        return;
    }
    const data = JSON.parse(info.data);

    if (data.results.length > 1) {
        infoDiv.innerHTML = '<p>No hay un pago asociado a ese email en Mercadopago. <strong>Cambie el medio de pago o agregue un email correcto por favor</strong></p>';
        fadeOut(finish);
        loader.style.display = 'none';
        button.style.display = 'inline-block';
        return;
    }

    infoDiv.innerHTML = '';
    loader.style.display = 'none';
    button.style.display = 'none';
    field.style.width = '100%';

    // console.log(data.results[0]);
    const dataMP = {
        'id': data.results[0].id,
        'ref': data.results[0].external_reference,
        'client': data.results[0].payer_id,
        'status': data.results[0].status,
        'name': data.results[0].reason,
        'date': data.results[0].date_created,
        'id_plan': data.results[0].preapproval_plan_id,
        'payment_method': data.results[0].payment_method_id,
        'frecuency': `${data.results[0].auto_recurring.frequency} ${data.results[0].auto_recurring.frequency_type}`,
        'payment': `ARS ${data.results[0].auto_recurring.transaction_amount}`,
        'start': data.results[0].auto_recurring.start_date,
        'application_id': data.results[0].application_id,
    };

    infoDiv.innerHTML = `
        <p><strong>ID:</strong> ${data.results[0].id}</p>
        <p><strong>Suscripción:</strong> ${data.results[0].reason}</p>
        <p><strong>Pago:</strong> ARS ${data.results[0].auto_recurring.transaction_amount} </p>
        <p><strong>Estado: </strong> ${data.results[0].status}</p>
    `;

    $('#payment_data').value = JSON.stringify(dataMP);

    fadeIn(finish, 'block');
}

const updateMembership = async (membership_id,
    user_id,
    subscription_id,
    subscription_name,
    subscription_price,
    payment_id,
    payment_data) => {
    const finish = await fetch(api_subscriptions.finishEdit, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            membership_id,
            user_id,
            subscription_id,
            subscription_name,
            subscription_price,
            payment_id,
            payment_data
        })
    });
    const response = await finish.json();
    return response;
}

/** create new */
const buttonCreate = $('#create-new-subscription');

if (typeof (buttonCreate) != 'undefined' && buttonCreate != null) {
    buttonCreate.addEventListener('click', () => {
        fadeOut($('#subscription-not-exists'));
        fadeIn($('#select-subscription'), 'block');
    });

    const selectNewSusbcription = $('#select-subscription-select');
    const selectNewPrice = $('#select-price-new');
    const customPriceNew = $('#custom-price-new');
    const cancelNewCustomPrice = $('.cancel-new');
    const newFinish = $('#new-finish');
    const newEnd = $('#new-end');
    const cancelNew = $('#cancel-new');
    const changePaymentNew = $('#change-payment-new');
    const newMembershipPayment = $('#new-membership-payment');
    const selectNewPayment = $('#new-select-payment');
    const mpInfoNew = $('#get-mp-info-new');

    selectNewSusbcription.addEventListener('change', async () => {
        fadeOut($('#select-subscription'));
        fadeIn($('#loading-new'), 'block');
    
        $('#name_subscription').value = selectNewSusbcription.options[selectNewSusbcription.selectedIndex].text;
        $('#id_subscription').value = selectNewSusbcription.value;

        const prices = await subscriptionPrices(selectNewSusbcription.value);

        if (!prices.success) {
            msg('Error al recuperar los precios de la suscripción', 'notice-error');
            fadeIn($('#select-subscription'), 'block');
            return;
        }

        fadeOut($('#loading-new'));
        fadeIn($('#show_prices_new'), 'block');

        prices.data.forEach(el => {
            var option = document.createElement('option');
            option.value = el;
            option.innerHTML = el;
            selectNewPrice.appendChild(option);
        });
    });

    selectNewPrice.addEventListener('change', () => {
        if (selectNewPrice.value == 'custom') {
            fadeIn($('#custom-price-div-new'), 'block');
            return;
        }
        customPriceNew.value = '';
        fadeIn(newFinish, 'block');
        $('#price_subscription').value = selectNewPrice.value;
        fadeOut($('#show_prices_new'));
    });

    customPriceNew.addEventListener('keyup', () => {
        if (customPriceNew.value.length >= 2) {
            fadeIn(newFinish, 'block');
            $('#price_subscription').value = customPriceNew.value;
        }
    });

    changePaymentNew.addEventListener('click', () => {
        fadeOut(newFinish);
        fadeIn(newMembershipPayment, 'block');
    });

    cancelNewCustomPrice.addEventListener('click', () => {
        fadeOut(cancelNewCustomPrice.parentElement);
        fadeOut(newFinish);
        selectNewPrice.value = '';
        selectNewPrice.style.display = 'block';
    });

    selectNewPayment.addEventListener('change', () => {
        $('#payment_membership_title').value = selectNewPayment.options[selectNewPayment.selectedIndex].text;
        $('#payment_membership_id').value = selectNewPayment.value;

        if (selectNewPayment.value == 'mp') {
            fadeIn($('#mp-data-new'), 'block');
            fadeOut($('#new-finish'));
            if ($('#bank-data-new').offsetParent != null) {
                fadeOut($('#bank-data-new'));
            }
        }

        if (selectNewPayment.value == 'bank') {
            fadeIn($('#bank-data-new'), 'block');
            if ($('#mp-data-new').offsetParent != null) {
                fadeOut($('#mp-data-new'))
            }
            fadeIn($('#new-finish'), 'block');
            changePaymentNew.style.display = 'none';
            fadeIn($('#new-end'), 'inline-block');
        }
    });

    $('#cancelmp').addEventListener('click', ()=>{
        fadeOut($('#mp-data-new'));
        fadeIn(selectNewPayment, 'block');
        $('#get-mp-info-new').style.display = 'inline-block';
        $('#email_mp_new').style.width = '80%';
        $('#show-info-new').innerHTML = '';
        $('#email_mp_new').value = '';
        selectNewPayment.value = '';
    })

    mpInfoNew.addEventListener('click', async () => {
        mpInfoNew.style.display = 'none';
        mpInfoFetch(mpInfoNew, $('#email_mp_new'), $('#show-info-new'), $('#loading-mp-new'), $('#new-finish'));
        
        changePaymentNew.style.display = 'none';
        fadeIn($('#new-end'), 'inline-block');
    });

    cancelNew.addEventListener('click', () => {
        $('#modal-membership-new').innerHTML = 'Cancelando, un momento por favor';
        window.location.reload();
    });

    newEnd.addEventListener('click', async () => {
        if (selectNewPayment.value == 'bank') {

            if(!$('#cbu_new').value || !$('#dni_new').value || !$('#cuil_new').value) {
                alert('Debe completar los datos de la cuenta bancaria');
                return;
            }

            if($('#cbu_new').value.length > 22){
                alert('El CBU no puede tener mas de 22 numeros');
                return;
            }

            if($('#dni_new').value.length > 8){
                alert('El DNI no puede tener mas de 8 numeros');
                return;
            }

            if($('#cuil_new').value.length > 11){
                alert('El CUIL no puede tener mas de 11 numeros');
                return;
            }

            $('#payment_data').value = bankData(
                $('#cbu_new').value,
                $('#dni_new').value,
                $('#cuil_new').value,
            );
        }
       
        fadeOut(newFinish);
        fadeIn($('#loading-new'), 'block');

        const user_id = $('#user_id_membership').value;
        const subscription_id = $('#id_subscription').value;
        const subscription_name = $('#name_subscription').value;
        const subscription_price = $('#price_subscription').value;
        const payment_id = $('#payment_membership_id').value;
        const payment_t = $('#payment_membership_title').value;
        const payment_data = $('#payment_data').value;

        const createMem = await createMembership(user_id, subscription_id, subscription_name, subscription_price, payment_id, payment_t, payment_data);

        if (!createMem.success) {
            fadeOut($('#loading-new'));
            fadeIn(newFinish, 'block');
            $('#msg-finish-new').innerHTML = createMem.data;
            return;
        }

       $('#modal-membership-new').innerHTML = 'Suscripción creada correctamente, un momento por favor...';
       window.location.reload();
    })
}

const createMembership = async (
    user_id,
    subscription_id,
    subscription_name,
    subscription_price,
    payment_id,
    payment_title,
    payment_data
) => {
    const create = await fetch(api_subscriptions.createMembership, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id,
            subscription_id,
            subscription_name,
            subscription_price,
            payment_id,
            payment_title,
            payment_data
        })
    });
    const response = await create.json();
    return response;
}

/** create user */

const createUserButton = $('#create-user-button');

if (typeof (createUserButton) != 'undefined' && createUserButton != null) {
    const newUserDiv = $('#create-new-user');
    const cancelUserButton = $('#cancel-create-user');
    const sendUserButton = $('#create-user');
    const name = $('#name');
    const lastname = $('#lastname');
    const email = $('#email');
 

    createUserButton.addEventListener('click', () => {
        fadeIn(newUserDiv, 'block');
        fadeOut($('#select-member'));
    })

    cancelUserButton.addEventListener('click', () => {
        fadeIn($('#select-member'), 'block');
        fadeOut(newUserDiv);
        name.value = '';
        lastname.value = '';
        email.value = '';
    });

    sendUserButton.addEventListener('click', async () => {

        $('#msg-create-user').innerHTML = 'Creando usuario, un momento por favor...';
        fadeOut(newUserDiv);

       const sendEmail = $('#send_email').checked ? 1 : 0;

        const user = await createUser(name.value, lastname.value, email.value, sendEmail);
//        console.log(user);
        if (!user.success) {
            $('#msg-create-user').innerHTML = `<strong style="color:red">${user.data}</strong>`;
            fadeIn(newUserDiv, 'block');
            return;
        }
        $('#msg-create-user').innerHTML = 'Usuario creado, un momento por favor...';
        $('#user_id_membership').value = user.data;
        setTimeout(() => {
            fadeIn($('#new-membership-div'), 'block');
            fadeIn($('#select-subscription'), 'block');
            $('#msg-create-user').innerHTML = '';
        }, 500);

    });
}

const createUser = async (name, lastname, email, sendEmail) => {
    const user = await fetch(api_subscriptions.createUser, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name,
            lastname,
            email,
            sendEmail
        })
    });
    const response = await user.json();
    return response;
}

/** subscriptions prices */

const subscriptionPrices = async (subscriptionID) => {
    const getPrices = await fetch(api_subscriptions.getPrices, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: subscriptionID
        })
    });
    const response = await getPrices.json();
    return response;
}