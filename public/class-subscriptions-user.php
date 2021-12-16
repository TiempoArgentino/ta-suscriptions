<?php

class Subscriptions_User
{
    public function __construct()
    {
        add_action('panel_user_tabs', [$this, 'subscription_panel_tab'], 6);
        add_action('panel_user_content', [$this, 'subscription_panel_content']);
        add_action( 'rest_api_init', [$this, 'endpoint'] );
    }

    public function subscription_panel_tab($tab = '')
    {
        if(has_filter( 'panel_tabs_subscription' )) {
            apply_filters( 'panel_tabs_subscription', $tab );
        } else {
            echo '<a href="#subscription" class="tab-select" data-content="#subscription">' . __('Subscriptions', 'subscriptions') . '</a> ';
        }
    }

    public function subscription_panel_content()
    {
        if (locate_template('subscriptions-theme/auth/user-panel.php')) {
            require_once get_template_directory() . '/subscriptions-theme/auth/user-panel.php';
        } else {
            require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/auth/user-panel.php';
        }
    }

    public function endpoint()
    {
        register_rest_route( 'subscriptions/v1', 'user-prices',[
            'methods' => 'POST',
            'callback' => [$this, 'get_subscriptions_price_user'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function get_subscriptions_price_user(WP_REST_Request $request)
    {
        $membership = $request->get_param('subscription');

        if(!$membership) return;

        $prices = [
            get_post_meta($membership, '_s_price', true),
            get_post_meta($membership, '_prices_extra', true)
        ];

        return wp_send_json_success( $prices );
    }
    
}

function subscriptions_user()
{
    return new Subscriptions_User();
}
subscriptions_user();
