<div class="admin-subscriptions-container">
    <h1><?php echo __('Subscriptions payments settings', 'subscriptions'); ?></h1>
    <div class="container-payment-settings">
        <?php do_action('subscriptions_pyament_methods')?>
    </div>
    <div class="container-update-prices">
        <?php do_action('subscriptions_update_prices')?>
    </div>
</div>