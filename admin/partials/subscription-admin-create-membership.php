<div class="wrap" id="modal-membership-new">
    <h2>Crear o editar membresía <span class="dashicons dashicons-no" id="membership-close-form"></span></h2>
    <div id="msg-create-membership"></div>
    <!-- select member -->
    <div id="select-member" class="step step-members">
        <h3>Seleccionar usuario</h3>
        <p>Selecciona un usuario para asignar a la membresía.</p>
        <span id="search-user-button" class="button button-primary">Buscar Usuario</span> <span id="create-user-button" class="button button-primary">Crear uno nuevo</span>
        <div id="search-user-member" class="memberships-field-container">
            <input type="email" autocomplete="off" placeholder="Ingresé un email o parte de un email por favor..." id="search-user-member-input" class="memberships-fields">
            <div class="spinner-1 loading-user"></div>
            <div id="search-user-member-results"></div>
            <button type="button" id="cancel-search-user" class="button button-primary">Cancelar</button>
            <div id="user-buttons">
                <button class="button button-primary" id="search-db">Buscar</button> <button id="cancel-search" class="button button-primary">Cancelar</button>
            </div>
            <input type="hidden" name="user_id" id="user_id" value="">
            <input type="hidden" name="user_email" id="user_email" value="">
        </div>
        <!-- subscription exists --->
        <div id="subscription-exists">
            <h3>El usuario ya tiene una membresía activa.</h3>
            <button id="edit-subscription-exists" class="button button-primary">Editar la Suscripción</button>
            <button id="cancel-subscription-exists" class="button button-primary">Cancelar</button>
        </div>

        <div id="subscription-not-exists">
            <h3>El usuario no tiene una membresía activa</h3>
            <button id="create-new-subscription" class="button button-primary">Crear una nueva</button>
            <button id="cancel-new-subscription" class="button button-primary">Cancelar</button>
        </div>
    </div>
    <div id="msg-create-user"></div>
    <div id="create-new-user">
        <h3>Crear un usuario nuevo</h3>
        <label>Nombre</label>
        <input type="text" id="name" value="" class="memberships-fields">
        <br />
        <label for="">Apellido</label>
        <input type="text" id="lastname" value="" class="memberships-fields">
        <br />
        <label for="">Email</label>
        <input type="email" id="email" value="" class="memberships-fields">
        <br />
        <label><input type="checkbox" id="send_email" value="" /> Enviar datos al usuario.</label>
        <br />
        <button id="create-user" class="button button-primary">Crear Usuario</button> <button id="cancel-create-user" class="button button-primary">Cancelar</button>
    </div>
    <!-- edit membership -->
    <div id="edit-membership">
        <div id="edit-membership-msg"></div>
        <div id="edit-membership-form">
            <h3>Editar la membresía: <span id="mem-ref"></span></h3>
            <p>Selecciona una suscripción (Actual: <strong id="mem-name"></strong>)</p>
            <select id="edit-select-subscription" class="memberships-fields">
                <option value="">Selecciona una suscripción</option>
                <?php foreach (mem_actions()->get_subscriptions() as $subscription) : ?>
                    <option value="<?php echo $subscription->ID; ?>"><?php echo $subscription->post_title; ?></option>
                <?php endforeach; ?>
            </select>
            <div id="edit-membership-price">
                <h3>Seleccionar un precio</h3>
                <select id="edit-select-price" class="memberships-fields">
                    <option value="">-- seleccionar precio --</option>
                    <option value="custom">Precio personalizado</option>
                </select>
                <div id="custom-price-div">
                    <h4>Precio personalizado</h4>
                    <input type="text" class="memberships-fields" id="custom-price" value="">
                    <span class="cancel-edit button">Cancelar</span>
                </div>
            </div>
            <div id="edit-membership-payment">
                <h3>Seleccionar medio de Pago</h3>
                <select id="edit-select-payment" class="memberships-fields">
                    <option value=""> -- seleccionar -- </option>
                    <option value="mp">Mercadopago</option>
                    <option value="bank">Transferencia Bancaria</option>
                </select>
                <div id="bank-data">
                    <label for="">CBU</label>
                    <input type="number" id="cbu" value="" class="memberships-fields bankPayment[]">
                    <label for="">Tipo DNI</label>
                    <select id="doc_type" class="memberships-fields bankPayment[]">
                        <option value="DNI">DNI</option>
                        <option value="CI">CI</option>
                        <option value="LC">LC</option>
                        <option value="LE">LE</option>
                        <option value="other">Otro</option>
                    </select>
                    <label for="">DNI Número</label>
                    <input type="number" id="dni" value="" class="memberships-fields bankPayment[]">
                    <label for="">CUIL / CUIT</label>
                    <input type="number" id="cuil" value="" class="memberships-fields bankPayment[]">
                </div>
                <div id="mp-data">
                    <label style="display: block;">Email con el que se pago en MP</label>
                    <input type="email" id="email_mp" value="" class="memberships-fields-middle"> <button class="button" id="get-mp-info">Obtener información del pago</button>
                    <div id="loading-mp"></div>
                    <div id="show-info"></div>
                </div>
            </div>
            <div id="msg-finish-edit"></div>
            <div id="edit-finish" style="margin-top: 5px;">
                <button id="change-payment" class="button button-primary">Cambiar medio de pago</button>
                <button id="edit-end" class="button button-primary">Finalizar</button>
                <button id="cancel-edit" class="button button-primary">Cancelar</button>
            </div>
        </div>

    </div>
    <!-- create new membership -->
    <div id="new-membership-div">
        <!-- select subscription -->
        <div id="select-subscription" class="step step-membership">
            <h3>Selecciona una suscripción</h3>
            <p id="msg-prices">Selecciona una suscripción para crear una membresía.</p>
            <select id="select-subscription-select" class="memberships-fields">
                <option value="">Selecciona una suscripción</option>
                <?php foreach (mem_actions()->get_subscriptions() as $subscription) : ?>
                    <option value="<?php echo $subscription->ID; ?>"><?php echo $subscription->post_title; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="show_prices_new">
            <h3>Selecciona un precio</h3>
            <select id="select-price-new" class="memberships-fields">
                <option value="">-- seleccionar precio --</option>
                <option value="custom">Precio personalizado</option>
            </select>
            <div id="custom-price-div-new">
                <h4>Precio personalizado</h4>
                <input type="text" class="memberships-fields" id="custom-price-new" value="">
                <span class="cancel-new button">Cancelar</span>
            </div>
        </div>
        <div id="new-membership-payment">
            <h3>Seleccionar medio de Pago</h3>
            <select id="new-select-payment" class="memberships-fields">
                <option value=""> -- seleccionar -- </option>
                <option value="mp">Mercadopago</option>
                <option value="bank">Transferencia Bancaria</option>
            </select>
            <div id="bank-data-new">
                <label for="">CBU</label>
                <input type="number" id="cbu_new" value="" class="memberships-fields">
                <label for="">Tipo DNI</label>
                <select id="doc_type_new" class="memberships-fields">
                    <option value="DNI">DNI</option>
                    <option value="CI">CI</option>
                    <option value="LC">LC</option>
                    <option value="LE">LE</option>
                    <option value="other">Otro</option>
                </select>
                <label for="">DNI Número</label>
                <input type="number" id="dni_new" value="" class="memberships-fields">
                <label for="">CUIL / CUIT</label>
                <input type="number" id="cuil_new" value="" class="memberships-fields">
            </div>
            <div id="mp-data-new">
                <label style="display: block;">Email con el que se pago en MP</label>
                <input type="email" id="email_mp_new" value="" class="memberships-fields-middle"> <button class="button" id="get-mp-info-new">Obtener información del pago</button>
                <div id="loading-mp-new"></div>
                <button id="cancelmp" class="button">Cancelar</button>
                <div id="show-info-new"></div>
            </div>
        </div>
        <div class="spinner-1 loading-membership" id="loading-new"></div>
        <div id="msg-finish-new"></div>
        <div id="new-finish" style="margin-top: 5px;">
            <button id="change-payment-new" class="button button-primary">Seleccionar medio de pago</button>
            <button id="new-end" class="button button-primary">Finalizar</button>
            <button id="cancel-new" class="button button-primary">Cancelar</button>
        </div>
    </div>


    <input type="hidden" id="id_membership" value="">
    <input type="hidden" id="user_id_membership" value="">
    <input type="hidden" id="id_subscription" value="">
    <input type="hidden" id="price_subscription" value="">
    <input type="hidden" id="name_subscription" value="">
    <input type="hidden" id="payment_membership_title" value="">
    <input type="hidden" id="payment_membership_id" value="">
    <input type="hidden" id="payment_data" value="">
</div>