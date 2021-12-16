<div class="admin-subscriptions-container">
    <h1><?php echo __('Subscriptors data', 'subscriptions'); ?></h1>
    <div class="container-payment-settings">
        <form class="search-user">
            <input type="email" name="search-user" class="regular-text" id="search-user-email" placeholder="<?php _e('Search by email', 'subscriptions') ?>" value="" />
            inicio <input type="date" name="date-after" id="date-after" placeholder="<?php echo __('Date After', 'subscriptions') ?>">
            fin <input type="date" name="date-before" id="date-before" placeholder="<?php echo __('Date Before', 'subscriptions') ?>">
            <button class="button button-primary" id="user-search-button"><?php _e('Search User', 'subscriptions') ?></button>
        </form>
        <div id="view-user-info">
            <div id="user-error">
            </div>

            <form method="post" class="export" id="form-export">
                <div id="user-info">
                </div>
                <?php wp_nonce_field('export_action', 'export_field'); ?>
                <button type="submit" name="export-button" id="export-button">Export</button>
            </form>
        </div>
        <?php do_action('subscriptions_users_data') ?>
    </div>
</div>