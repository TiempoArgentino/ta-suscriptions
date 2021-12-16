<?php

class Subs_Update_Prices
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this,'scripts']);
        add_action('subscriptions_update_prices', [$this,'panel']);

        add_action('rest_api_init', [$this,'prices_endopoints']);
    }

    public function scripts()
    {
        wp_enqueue_script( 'update-prices', plugin_dir_url( __DIR__ ).'admin/js/update-prices.js', false, SUSCRIPTIONS_VERSION, true );
        wp_localize_script('update-prices', 'update_prices', [
            'getSubsPrices' => rest_url('subscriptions/v1/get-subscription-prices'),
            'getSubsInfo' => rest_url('subscriptions/v1/get-subscription-info')
        ]);
    }
    public function panel()
    {
        require  __DIR__ .'/partials/subscriptions-update-prices.php';
    }

    public function get_subscriptions()
    {
        $args = [
            'post_type' => 'subscriptions',
            'post_status' => 'publish'
        ];

        $subscriptions = get_posts($args);
        return $subscriptions;
    }
    
    public function prices_endopoints()
    {
        register_rest_route(  'subscriptions/v1', 'get-subscription-prices', [
            'methods' => 'POST',
            'callback' => [$this,'get_subscription_prices'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route(  'subscriptions/v1', 'get-subscription-info', [
            'methods' => 'POST',
            'callback' => [$this,'get_subscription_id'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function get_subscription_prices(WP_REST_Request $request)
    {
        $post_id = $request->get_param('post_id');
        $args = [
            'post_type' => 'memberships',
            'fields' => 'ids',
            'numberposts' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_member_suscription_id',
                    'value' => $post_id
                ],
                [
                    'key' => '_member_payment_method',
                    'value' => 'mp'
                ],
                [
                    'key' => '_member_order_status',
                    'value' => 'completed'
                ],
                [
                    'key' => 'payment_app_id',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'payment_app_id',
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'key' => 'id_subscription_data',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'id_subscription_data',
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'key' => '_member_suscription_cost',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => '_member_suscription_cost',
                    'compare' => '< '.get_post_meta($post_id, '_s_price', true)
                ]
            ]
        ];

        $prices_ids = get_posts($args);
        if($prices_ids) {
            return wp_send_json_success( $prices_ids ); 
        }
        return wp_send_json_error( 'Nada para sincronizar.' );
    }

    public function get_subscription_id(WP_REST_Request $request)
    {
        $membership_id = $request->get_param('membership_id');
        $subscription_id = $request->get_param('subscription_id');

        $application_id = get_post_meta($membership_id, 'payment_app_id', true);
        $mp_id_subscription = get_post_meta($membership_id, 'id_subscription_data', true);
        $price_new = get_post_meta($subscription_id, '_s_price', true);

        if(null == $price_new) {
            return wp_send_json_error( 'No se pudo obtener el precio nuevo' );
        }

        if(null == $application_id) {
            return wp_send_json_error( 'No se guardo el APP ID, no se actualizo' );
        }

        if(null == $mp_id_subscription) {
            return wp_send_json_error( 'No se guardo el ID de suscripción proveninte de MP, no se actualizo' );
        }
        
        $data = [
            'application_id' => $application_id,
            'auto_recurring' => [
                'currency_id' => 'ARS',
                'transaction_amount' => $price_new,
            ]
        ];
  
        $edit = Mercadopago()->edit_subscription($data, $mp_id_subscription, get_option('mp_access_token'));

        if(!$edit) {
            return wp_send_json_error( 'No se actualizo el precio' );
        }	
        
        return wp_send_json_success( $edit );
    }
}

function update_prices()
{
    return new Subs_Update_Prices();
}
update_prices();

