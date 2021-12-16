<?php

class Subscriptions_Memberships_Actions
{
    public $wpdb;
    public $prefix;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->prefix = $wpdb->prefix;

        add_action('views_edit-memberships', [$this, 'add_custom_button']);
        add_action('admin_enqueue_scripts', [$this, 'add_js']);

        add_action('rest_api_init', [$this, 'endpoints']);
    }

    public function add_custom_button($views)
    {
        global $post_type_object;
        if ($post_type_object->name === 'memberships') {
            $views['edit-mbs'] = '<li class="edit-mbs"><a href="#" id="new-membership">Crear o Editar Membresía</a></li>';
            $this->modal_membership();
        }

        return $views;
    }

    public function add_js()
    {
        wp_enqueue_script('memberships-admin-js', plugin_dir_url(__FILE__) . 'js/memberships.js', array('jquery'), SUSCRIPTIONS_VERSION, true);
        wp_localize_script('memberships-admin-js', 'api_subscriptions', [
            'getPrices' => rest_url('subscriptions/v1/prices'),
            'getUser' => rest_url('subscriptions/v1/members'),
            'getSubscription' => rest_url('subscriptions/v1/members-subscription'),
            'getMembership' => rest_url('subscriptions/v1/user-membership'),
            'getMembershipInfo' => rest_url('subscriptions/v1/membership-info'),
            'getMPInfo' => rest_url('subscriptions/v1/get-mp-info'),
            'finishEdit' => rest_url('subscriptions/v1/finish-edit'),
            'createMembership' => rest_url('subscriptions/v1/create-membership'),
            'createUser' => rest_url('subscriptions/v1/create-user')
        ]);
    }

    public function modal_membership()
    {
        require __DIR__ . '/partials/subscription-admin-create-membership.php';
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

    public function endpoints()
    {
        //get_subscritpions_price
        register_rest_route('subscriptions/v1', '/prices', [
            'methods' => 'POST',
            'callback' => [$this, 'get_subscriptions_price'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/members', [
            'methods' => 'POST',
            'callback' => [$this, 'get_members'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/members-subscription', [
            'methods' => 'POST',
            'callback' => [$this, 'get_user_subscriptions'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/user-membership', [
            'methods' => 'POST',
            'callback' => [$this, 'get_user_membership'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/membership-info', [
            'methods' => 'POST',
            'callback' => [$this, 'get_membership_info'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/finish-edit', [
            'methods' => 'POST',
            'callback' => [$this, 'finish_edit'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/get-mp-info', [
            'methods' => 'POST',
            'callback' => [$this, 'get_mp_info'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/create-membership', [
            'methods' => 'POST',
            'callback' => [$this, 'create_membership'],
            'permissions_callback' => '__return_true',
        ]);

        register_rest_route('subscriptions/v1', '/create-user', [
            'methods' => 'POST',
            'callback' => [$this, 'create_user'],
            'permissions_callback' => '__return_true',
        ]);
    }

    public function get_subscriptions_price(WP_REST_Request $request)
    {
        $data = $request->get_param('id');

        if (!$data) {
            wp_send_json_error('Selecciona una suscripción por favor.', 403);
            exit();
        }

        $s_price = get_post_meta($data, '_s_price', true);
        $e_price = get_post_meta($data, '_prices_extra', true);

        $prices = [];
        $prices[] = $s_price;

        foreach ($e_price as $key => $value) {
            $prices[] = $value;
        }

        wp_send_json_success($prices);
    }

    public function get_members(WP_REST_Request $request)
    {
        $email = $request->get_param('email');

        $sql = $this->wpdb->get_results(
            $this->wpdb->prepare("SELECT ID, user_email, display_name FROM {$this->prefix}users WHERE user_email LIKE %s", '%' . $this->wpdb->esc_like($email) . '%')
        );

        if (null == $sql || !$sql) {
            return wp_send_json_error('No hay usuarios con ese email.');
            exit();
        }

        return wp_send_json_success($sql);
    }

    public function get_user_subscriptions(WP_REST_Request $request)
    {
        $user_id = $request->get_param('user_id');
        $sql = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->prefix}usermeta WHERE user_id = %d AND meta_key = 'suscription'", $user_id)
        );

        if (null == $sql || !$sql) {
            return wp_send_json_error('No hay suscripciones para el usuario.');
        }

        return wp_send_json_success($user_id);
    }

    public function get_user_membership(WP_REST_Request $request)
    {
        $user_id = $request->get_param('user_id');
        $args = [
            'post_type' => 'memberships',
            'post_status' => 'publish',
            'fields' => 'ids',
            'numberposts' => 1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_member_user_id',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => '_member_order_status',
                    'value' => ['completed','on-hold'],
                    'compare' => 'IN'
                ]
            ]
        ];

        $id = get_posts($args);

        if ($id) {
            return wp_send_json_success($id);
        }

        return wp_send_json_error('El usuario no tiene una membresía activa.');
    }

    
    public function get_membership_info(WP_REST_Request $request)
    {
        $membership_id = $request->get_param('membership_id');
        $membership = [];
        $membership['id'] = $membership_id;
        $membership['reference'] = get_post_meta($membership_id, '_member_order_reference', true);
        $membership['created'] = get_the_date('Y-m-d H:m_s', $membership_id);
        $membership['status'] = get_post_meta($membership_id, '_member_order_status', true);
        $membership['subscription_id'] = get_post_meta($membership_id, '_member_suscription_id', true);
        $membership['subscription'] = get_post_meta($membership_id, '_member_suscription_name', true);
        $membership['period'] = get_post_meta($membership_id, '_member_suscription_period_number', true) . ' ' . get_post_meta($membership_id, '_member_suscription_period', true) == 'Months' ? 'Mes/es' : 'Día/s';
        $membership['payment_method_title'] = get_post_meta($membership_id, '_member_payment_method_title', true);
        $membership['payment_method_id'] = get_post_meta($membership_id, '_member_payment_method', true);
        $membership['payment_data'] = get_post_meta($membership_id, 'payment_data', true);
        $membership['user_id'] = get_post_meta($membership_id, '_member_user_id', true);

        if (!$membership || null == $membership) {
            return wp_send_json_error('Sin datos.');
        }

        return wp_send_json_success($membership);
    }

    public function get_mp_info(WP_REST_Request $request)
    {
        $email = $request->get_param('email');

        if ($email == null) {
            return wp_send_json_error('Falta el email');
        }

        $info = Mercadopago()->search_subscription($email, get_option('mp_access_token'));

        return wp_send_json_success($info);
    }

    public function payment_data_insert($membership_id,$payment_data,$payment_id,$ref = null)
    {
        if ($payment_data != '' && $payment_id == 'mp') {
            
            $response = json_decode($payment_data);
            $payment = [
                'ID Suscripción' => $response->{'id'},
                'Referencia Externa' => $ref == null ? $response->{'ref'} : $ref,
                'ID Cliente' => $response->{'client'},
                'Estado MP' => $response->{'status'},
                'Suscripción MP' => $response->{'name'},
                'Creada' => $response->{'date'},
                'Init Point' => $response->{'init_point'},
                'Sandbox Init Point' => $response->{'sandbox_init_point'},
                'ID Plan' => 'Sin plan asociado',
                'ID Medio de Pago' => $response->{'payment_method'},
                'Frecuencia' => $response->{'frecuency'},
                'Pago' => $response->{'payment'},
                'Inicio' => $response->{'start'}
            ];

            
            update_post_meta($membership_id, '_member_payment_method_title', 'Mercadopago');
            update_post_meta($membership_id, '_member_payment_method', $payment_id);
            update_post_meta($membership_id, 'payment_data', $payment);
            update_post_meta($membership_id, 'id_subscription_data', $response->{'id'});
            update_post_meta($membership_id, 'payment_app_id', $response->{'application_id'});
        }

        if ($payment_data != '' && $payment_id == 'bank') {
            
            $response = json_decode($payment_data);
            $payment = [
                'CBU' => $response->{'CBU'},
                'DNI' => $response->{'DNI'},
                'CUIL' => $response->{'CUIL'}
            ];
            update_post_meta($membership_id, '_member_payment_method_title', 'Automatic bank debit');
            update_post_meta($membership_id, '_member_payment_method', $payment_id);
            update_post_meta($membership_id, 'payment_data', $payment);
        }
    }

    public function update_price_mp($membership_id,$price_new)
    {
        $payment_id = get_post_meta($membership_id,'_member_payment_method',true);

        if($payment_id == 'mp'){
            $data = [
                'application_id' => get_post_meta($membership_id, 'payment_app_id', true),
                'auto_recurring' => [
                    'currency_id' => 'ARS',
                    'transaction_amount' => $price_new,
                ]
            ];
    
            Mercadopago()->edit_subscription($data, get_post_meta($membership_id, 'id_subscription_data', true), get_option('mp_access_token'));
        }
    }

    public function update_user_role($user_id,$subscription_id)
    {
         $user = get_userdata($user_id);
         $user_roles = $user->roles;
 
         if(in_array('subscriber', $user_roles) || in_array('digital', $user_roles)){
             $role = get_post_meta($subscription_id, '_is_type', true) == 'not_digital' ? get_option('default_sucription_role') : get_option('subscription_digital_role');
 
             if (!in_array($role, $user_roles)) {
                 $ur = new WP_User($user_id);
                 $ur->set_role($role);
             }
         }
    }

    public function finish_edit(WP_REST_Request $request)
    {
        $membership_id = $request->get_param('membership_id');
        $user_id = $request->get_param('user_id');
        $subscription_id = $request->get_param('subscription_id');
        $subscription_name = $request->get_param('subscription_name');
        $subscription_price = $request->get_param('subscription_price');
        $payment_id = $request->get_param('payment_id');
        $payment_data = $request->get_param('payment_data');

        if ($membership_id == null) {
            return wp_send_json_error('Error al actualizar');
        }

        if ($subscription_id == null ||  $subscription_name == null || $subscription_price == null) {
            return wp_send_json_error('Faltan datos para editar la membresia.');
        }

        //update membership metas
        update_post_meta($membership_id, '_member_suscription_id', $subscription_id);
        update_post_meta($membership_id, '_member_order_status', 'completed');
        update_post_meta($membership_id, '_member_suscription_name', $subscription_name);
        update_post_meta($membership_id, '_member_suscription_cost', $subscription_price);

        //update user role
        $this->update_user_role($user_id,$subscription_id);

        //payment info if is necesary
        $this->payment_data_insert($membership_id,$payment_data,$payment_id);
        $this->update_price_mp($membership_id,$subscription_price);

        //update user meta
        update_user_meta($user_id, '_user_status', 'active');
        update_user_meta($user_id, 'suscription', $subscription_id);
        update_user_meta($user_id, 'suscription_name', $subscription_name);

        return wp_send_json_success('Membresia actualizada.');
    }

    public function create_new_membership($order_reference,$subscription_id,$subscription_name,$subscription_price,$user_id,$payment_id,$payment_title)
    {
        $create_membership = [
            'post_title' => $order_reference,
            'post_status'   => 'publish',
            'post_type'     => 'memberships',
            'post_author'   => 1,
        ];

        $create = wp_insert_post($create_membership);

        if($create) {
            $suscription_period = get_post_meta($subscription_id, '_period_time', true) === 'period_months' ? __('Months', 'suscrptions') : __('Days', 'subscriptions');
            $period = get_post_meta($subscription_id, '_period_time', true) === 'period_months' ? 'month' : 'day';
            $suscription_period_number = get_post_meta($subscription_id, '_period_number', true);

            $hoy = date('Y-m-d H:i:s');
            $sumo_mes = date("Y-m-d H:i:s", strtotime($hoy . '+ ' . $suscription_period_number . ' ' . $period));

            $user = get_user_by('id',$user_id);

            $order_data = [
                '_member_order_reference' => $order_reference,
                '_member_order_status' => 'completed',
                '_member_payment_method' => $payment_id,
                '_member_payment_method_title' => $payment_title,
                '_member_user_id' => $user_id,
                '_member_renewal_date' => $sumo_mes,
                '_member_suscription_id' => $subscription_id,
                '_member_suscription_name' => $subscription_name,
                '_member_suscription_period' => $suscription_period,
                '_member_suscription_period_number' => $suscription_period_number,
                '_member_suscription_cost' => $subscription_price,
                '_member_suscription_user_email' => $user->user_email,
                'payment_type' => 'subscription'
            ];
            update_user_meta($user_id, '_user_status', 'active');
            update_user_meta($user_id, 'suscription', $subscription_id);
            update_user_meta($user_id, 'suscription_name', $subscription_name);

            foreach ($order_data as $key => $value) {
                update_post_meta($create, $key, $value);
            }
            return $create;
        }
        return false;
    }

    public function create_membership(WP_REST_Request $request)
    {
        $order_reference = get_option('member_sku_prefix', 'TA-') . date('YmdHms');
        $user_id = $request->get_param('user_id');
        $subscription_id = $request->get_param('subscription_id');
        $subscription_name = $request->get_param('subscription_name');
        $subscription_price = $request->get_param('subscription_price');
        $payment_id = $request->get_param('payment_id');
        $payment_title = $request->get_param('payment_title');
        $payment_data = $request->get_param('payment_data');

        $membership = $this->create_new_membership($order_reference,$subscription_id,$subscription_name,$subscription_price,$user_id,$payment_id,$payment_title);

        if(!$membership) {
            return wp_send_json_error( 'Ocurrio un error al crear la membresía.' );
        }
        
        //update user role
        $this->update_user_role($user_id,$subscription_id);

        //payment info if is necesary
        $this->payment_data_insert($membership,$payment_data,$payment_id,$order_reference);
        $this->update_price_mp($membership,$subscription_price);

        return wp_send_json_success( $membership );

    }

    public function random_password($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

    public function create_user(WP_REST_Request $request)
    {
        $name = $request->get_param('name');
        $lastname = $request->get_param('lastname');
        $email = $request->get_param('email');
        $sendEmail = $request->get_param('sendEmail');

        
        if($name == '' || $lastname == '' || $email == '') {
            return wp_send_json_error('Faltan datos para crear el usuario.');
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return wp_send_json_error('El email no es valido.');
        }

        $check = get_user_by('email',$email);
        
        if($check) {
            return wp_send_json_error('El email ya esta registrado.');
        }
        $password = $this->random_password(8);
        $user_data = [
            'user_pass' => $password,
            'user_login' => $email,
            'user_email' => $email,
            'display_name' => $name . ' ' . $lastname,
            'first_name' => $name,
            'last_name' => $lastname,
            'nickname' => $email,
            'role' => get_option('default_sucription_role'),
            'show_admin_bar_front' => false
        ];

        $user = wp_insert_user($user_data);

        if(is_wp_error( $user )){
            return wp_send_json_error( 'Ocurrio un error al crear el usuario' );
        }

        update_user_meta($user, '_user_status', 'on-hold');

        if($sendEmail == 1) {
            Subscriptions_Emails::membership_email($email,$password);
        }

        return wp_send_json_success( $user );
    }
}

function mem_actions()
{
    return new Subscriptions_Memberships_Actions();
}

mem_actions();
