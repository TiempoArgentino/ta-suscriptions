<h2>Actualizar precios de suscripciones</h2>
<p>Esta función permite sincronizar los precios de las Suscripciones con Mercapago.</p>
<div class="content-updates">
    <div class="form-content" id="prices-update-content">
        <label>Seleccione una suscripción</label>
        <select name="subscription_id" id="subscription_id">
            <option value=""> -- seleccionar --</option>
            <?php foreach(update_prices()->get_subscriptions() as $subscription): ?>
                <option value="<?php echo $subscription->ID; ?>"><?php echo $subscription->post_title; ?></option>
            <?php endforeach; ?>
        </select><br />
        <button type="button" id="sincronize_prices">Sincronizar</button>
    </div>
    <div id="messages-prices" style="display: none;"></div>
</div>