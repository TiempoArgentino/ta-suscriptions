<?php

class Subscriptions_MP extends Payment_Getways
{

    static private $init = false;

    static public $id;
    static public $title;
    static public $description;
    static public $active;
    static public $status_default;

    static public $public_key;
    static public $access_token;
    static public $collector_id;
    static public $log;


    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;

        self::$id = 'mp';
        self::$title = get_option('mp_title', 'Mercadopago');
        self::$description = get_option('mp_descriptions', 'Pagar con MP (solo Argentina)');
        self::$active = get_option('mp_active', 0);
        self::$public_key = get_option('mp_public_key');
        self::$collector_id = get_option('mp_collector_id');
        self::$access_token =  get_option('mp_access_token');
        self::$log = get_option('mp_log');
        self::$status_default =  'completed';

        self::save_conf();
        self::mercadopago_class();


        add_action('wp_enqueue_scripts', [self::class, 'main_script']);
        add_action('wp_enqueue_scripts', [self::class, 'js_preferences']);

        add_action('add_meta_boxes',  [self::class, 'plan_metabox']);
        add_action('save_post_subscriptions', [self::class, 'plan_save']);

        // add_filter('generate_rewrite_rules', [self::class, 'get_notification_url']);
        // add_filter('query_vars', [self::class, 'query_notifications']);
        // add_action('template_redirect', [self::class, 'get_notification_body']);

        add_action('subscriptions_edit_actions', [self::class, 'cancel_membership']);

        add_action('edit_form_extra', [self::class, 'edit_form_subscription']);
        add_action('init', [self::class, 'edit_process']);

        //add_action('save_post_subscriptions', [self::class, 'update_all_price'], 10, 3);

        add_action( 'rest_api_init', [self::class,'notifitions_endpoint']);
    }


    public static function admin()
    {
        self::admin_panel_conf();
    }

    public static function front()
    {
        if ('1' === self::$active) {
            if (self::$public_key && self::$access_token && self::$collector_id) {
                self::show_payment();
            }
        }
    }
    /**
     * Metabox
     */
    public static function plan_metabox()
    {
        if ('1' === self::$active) {
            add_meta_box(
                'plan_id',
                __('Mercadopago Plan ID', 'subscriptions'),
                [__CLASS__, 'plan_callback'],
                ['subscriptions'],
                'side',
                'high'
            );
        }
    }
    /**
     * Plan callback
     */
    public static function plan_callback($post)
    {
        wp_nonce_field('mp_inner_custom_box', 'mp_inner_custom_box_nonce');

        $plan_id = get_post_meta($post->ID, 'plan_id', true);

        $form = '<label>' . __('Plan ID', 'subscriptions') . '</label>';
        $form .= '<input type="text" name="plan_id" value="' . $plan_id . '" />';
        echo $form;
    }
    /**
     * Plan save
     */
    public static function plan_save($post_id)
    {
        if (!isset($_POST['mp_inner_custom_box_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST['mp_inner_custom_box_nonce'];

        if (!wp_verify_nonce($nonce, 'mp_inner_custom_box')) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        if (isset($_POST['plan_id'])) {
            update_post_meta($post_id, 'plan_id', sanitize_text_field($_POST['plan_id']));
        }
    }
    /**
     * Mercadopago class
     */
    public static function mercadopago_class()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . '/mp/Mercadopago.php';
    }
    /**
     * Configuration
     */
    public static function admin_panel_conf()
    {
        $body = '<label><input type="checkbox" name="mp_active" value="1" ' . checked(self::$active, '1', false) . ' /> ' . __('Active?', 'subscriptions') . '</label>';
        $body .= '<label>' . __('Title', 'subscriptions') . '</label>';
        $body .= '<input type="text" class="payment_input_field" name="mp_title" value="' . self::$title . '" />';
        $body .= '<label>' . __('Add or change description', 'subscriptions') . '</label>';
        $body .= '<textarea class="payment_input_textarea" name="mp_descriptions">' . self::$description . '</textarea>';
        $body .= '<label>' . __('Mercadopago Public key', 'subscriptions') . '</label>';
        $body .= '<input type="text" class="payment_input_field" name="mp_public_key" value="' . self::$public_key . '" />';
        $body .= '<label>' . __('Mercadopago Access Token', 'subscriptions') . '</label>';
        $body .= '<input type="text" class="payment_input_field" name="mp_access_token" value="' . self::$access_token . '" />';
        $body .= '<label>' . __('Collector ID', 'subscriptions') . '</label>';
        $body .= '<input type="text" class="payment_input_field" name="mp_collector_id" value="' . self::$collector_id . '" />';
        $body .= '<label><input type="checkbox" class="payment_input_field" name="mp_log" value="' . self::$log . '" ' . checked(self::$log, '1', false) . ' />' . __('Payment Log', 'subscriptions') . '</label>';
        $body .= '<input type="submit" name="mp_submit" class="button button-primary" value="' . __('Submit', 'subscriptions') . '" />';

        return self::admin_config_form(self::$id, self::$title, $body);
    }
    /**
     * Save configuration
     */
    public static function save_conf()
    {
        if (isset($_POST['mp_submit'])) {
            if (isset($_POST['mp_active']))
                update_option('mp_active', 1);
            else
                update_option('mp_active', 0);

            if (isset($_POST['mp_title']))
                update_option('mp_title', sanitize_text_field($_POST['mp_title']));

            if (isset($_POST['mp_descriptions']))
                update_option('mp_descriptions', esc_textarea($_POST['mp_descriptions']));

            if (isset($_POST['mp_public_key']))
                update_option('mp_public_key', sanitize_text_field($_POST['mp_public_key']));

            if (isset($_POST['mp_access_token']))
                update_option('mp_access_token', sanitize_text_field($_POST['mp_access_token']));

            if (isset($_POST['mp_collector_id']))
                update_option('mp_collector_id', sanitize_text_field($_POST['mp_collector_id']));

            if (isset($_POST['mp_log']))
                update_option('mp_log', 1);
            else
                update_option('mp_log', 0);

            header('Location: ' . admin_url('admin.php?page=tar_pagos'));
            exit();
        }
    }
    /**
     * log
     */
    public static function payment_log($log_msg)
    {
        $log_filename = plugin_dir_path(dirname(__FILE__)) . '/mp/log';
        if (!file_exists($log_filename)) {
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename . '/log_' . date('d-M-Y-H-m-i') . '.log';
        file_put_contents($log_file_data, $log_msg . "\n\n", FILE_APPEND);
    }
    /**
     * Front
     */
    public static function show_payment()
    {
        $body = '<div class="payment-description alert alert-dark mt-3">' . self::$description . '</div>';
        $body .= self::form_payment();
        $title = '<label class="form-check-label payment-title"><input type="radio" checked="checked" name="payment_select_mp" class="payment-select ' . self::$id . ' form-check-input" value="' . self::$id . '" id="payment_select_mp" />' . self::$title . '</label>';
        return self::front_payment_view(self::$id, $title, $body);
    }

    public static function form_payment()
    {

        $form = '<div>
        <div id="error-tarjeta" class="alert alert-danger"></div>
        <div class="form-group">
            <label for="cardholderName">Titular de la tarjeta</label>
            <input id="cardholderName" class="form-control" data-checkout="cardholderName" placeholder="Titular de la tarjeta" type="text">
        </div>
        <div class="row">
            <div class="form-group col-md-4 col-12">
                <label for="docType">Tipo de documento</label>
                <select id="docType" class="form-control" name="docType" data-checkout="docType" type="text"></select>
            </div>
            <div class="form-group col-md-8 col-12">
                <label for="docNumber">Número de documento</label>
                <input id="docNumber" class="form-control" name="docNumber" data-checkout="docNumber" placeholder="Número de documento" type="text" />
            </div>
        </div>
        <div class="row">
        <div class="form-group col-md-6 col-12">
            <label for="">Fecha de vencimiento</label>
                <input type="text" class="form-control" placeholder="MM" id="cardExpirationMonth" data-checkout="cardExpirationMonth" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
        </div>
        <div class="form-group col-md-6 col-12">
                <input type="text" class="form-control" placeholder="AA" id="cardExpirationYear" data-checkout="cardExpirationYear" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
        </div>
        </div>
        <div class="form-group">
            <label for="cardNumber">Número de la tarjeta</label>
            <input type="text" class="form-control" id="cardNumber" placeholder="Número de la tarjeta" data-checkout="cardNumber" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
        </div>
        <div class="form-group">
            <label for="securityCode">Código de seguridad</label>
            <input id="securityCode" class="form-control" placeholder="Código de seguridad" data-checkout="securityCode" type="text" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
        </div>
        <input type="hidden" name="token" id="token" value="">
        <input type="hidden" name="transactionAmount" id="transactionAmount" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_price'] . '" />
        <input type="hidden" id="email" name="email" class="form-control" value="' . wp_get_current_user()->user_email . '" />
        <input type="hidden" name="paymentMethodId" id="paymentMethodId" />
        <br> ';
        $form .= '<input type="hidden" name="user_id" value="' . wp_get_current_user()->ID . '">
        <input type="hidden" name="suscription_id" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_id'] . '">
        <input type="hidden" name="suscription_name" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_name'] . '">
        <input type="button" class="btn btn-lg payment-button-submit btn-primary" name="paymentMP" id="paymentMP" value="' . __('Pay with Card', 'subscriptions') . '"></div>';
        return $form;
    }
    /**
     * Create date
     */
    public static function start_date()
    {
        $date = new DateTime('NOW', new DateTimeZone("America/Argentina/Buenos_Aires"));
        $date->modify('+10 min');

        $start_date = $date->format("Y-m-d\TH:i:s.") . floor($date->format("u") / 1000) . '-03:00';
        return $start_date;
    }
    /**
     * Payment proccess
     */
    public static function post_process($payment_filter = '')
    {
        if (isset($_POST['payment_select_mp'])) {
            $plan = get_post_meta($_POST['suscription_id'], 'plan_id', true);
            $user = get_userdata($_POST['user_id']);
            $order_reference = get_option('member_sku_prefix', 'TA-') . date('YmdHms');
            Subscriptions_Sessions::destroy_session('flash_messages');

            if ($plan) {
                $suscription_plan = self::plan_suscription($plan, $_POST['token'], $user->user_email, $order_reference, $_POST['transactionAmount']);
                $response = json_decode($suscription_plan);
                /**
                 * Log payment
                 */
                if (get_option('mp_log') === '1') {
                    self::payment_log($suscription_plan);
                }

                if ($response->{'status'} == 400 || $response->{'status'} == 401 || $response->{'status'} == 403 || $response->{'status'} == 404) {
                    $message = 'Error: ' . $response->{'status'} . ' ' . $response->{'message'};
                    Subscriptions_Sessions::set_flash_session('danger', $message);

                    if(has_filter('mp_error_filter')) {
                        apply_filters( 'mp_error_filter', $payment_filter );
                    }

                    wp_redirect(get_permalink(get_option('subscriptions_payment_page')));
                    exit();
                }


                $suscription_status = $response->{'status'};

                if ($suscription_status === 'authorized') {
                    $status = self::$status_default;
                    $status_user = 'active';
                } else if ($suscription_status === 'pending' || $suscription_status === 'in_process') {
                    $status = 'on-hold';
                    $status_user = 'on-hold';
                } else if ($suscription_status === 'rejected') {
                    $status = 'error';
                    $status_user = 'error';
                }

                $payment_data = [
                    'ID Suscripción' => $response->{'id'},
                    'Referencia Externa' => $response->{'external_reference'},
                    'ID Cliente' => $response->{'payer_id'},
                    'Estado MP' => $response->{'status'},
                    'Suscripción MP' => $response->{'reason'},
                    'Creada' => $response->{'date_created'},
                    'Init Point' => $response->{'init_point'},
                    'Sandbox Init Point' => $response->{'sandbox_init_point'},
                    'ID Plan' => $response->{'preapproval_plan_id'},
                    'ID Medio de Pago' => $response->{'payment_method_id'},
                    'Frecuencia' => $response->{'auto_recurring'}->{'frequency'} . ' ' . $response->{'auto_recurring'}->{'frequency_type'},
                    'Pago' => $response->{'auto_recurring'}->{'transaction_amount'} . ' ' . $response->{'auto_recurring'}->{'currency_id'},
                    'Inicio' => $response->{'auto_recurring'}->{'start_date'}
                ];
                $id_subscription = $response->{'id'};
                $app_id = $response->{'application_id'};
            } else {
                $suscription = get_post($_POST['suscription_id']);
                $frecuency = get_post_meta($_POST['suscription_id'], '_period_number', true);
                $frecuency_meta = get_post_meta($_POST['suscription_id'], '_period_time', true);
                $frecuency_type = $frecuency_meta === 'period_days' ? 'days' : 'months';

                $suscription_create = self::suscription_single($suscription->post_title, $_POST['token'], $user->user_email, $order_reference, $frecuency, $frecuency_type, $_POST['transactionAmount'], self::start_date());

                $response = json_decode($suscription_create);
                /**
                 * Log pyament
                 */
                if (get_option('mp_log') === '1') {
                    self::payment_log($suscription_create);
                }
                /**
                 * Errores
                 */
                if ($response->{'status'} === 401 || $response->{'status'} === 401 || $response->{'status'} === 403 || $response->{'status'} === 404) {
                    $message = 'Error: ' . $response->{'status'} . ' ' . $response->{'message'};
                    Subscriptions_Sessions::set_flash_session('danger', $message);

                    wp_redirect(get_permalink(get_option('subscriptions_payment_page')));
                    exit();
                }

                $suscription_status = $response->{'status'};

                if ($suscription_status === 'authorized') {
                    $status = self::$status_default;
                    $status_user = 'active';
                } else if ($suscription_status === 'pending' || $suscription_status === 'in_process') {
                    $status = 'on-hold';
                    $status_user = 'on-hold';
                } else if ($suscription_status === 'rejected') {
                    $status = 'error';
                    $status_user = 'error';
                }

                $payment_data = [
                    'ID Suscripción' => $response->{'id'},
                    'Referencia Externa' => $response->{'external_reference'},
                    'ID Cliente' => $response->{'payer_id'},
                    'Estado MP' => $response->{'status'},
                    'Suscripción MP' => $response->{'reason'},
                    'Creada' => $response->{'date_created'},
                    'Init Point' => $response->{'init_point'},
                    'Sandbox Init Point' => $response->{'sandbox_init_point'},
                    'ID Plan' => 'Sin plan asociado',
                    'ID Medio de Pago' => $response->{'payment_method_id'},
                    'Frecuencia' => $response->{'auto_recurring'}->{'frequency'} . ' ' . $response->{'auto_recurring'}->{'frequency_type'},
                    'Pago' => $response->{'auto_recurring'}->{'transaction_amount'} . ' ' . $response->{'auto_recurring'}->{'currency_id'},
                    'Inicio' => $response->{'auto_recurring'}->{'start_date'}
                ];
                $id_subscription = $response->{'id'};
                $app_id = $response->{'application_id'};
            }
            $create_order = self::create_order($order_reference, $status, $status_user);
            if ($create_order) {

                self::update_user($_POST['user_id'], $_POST['suscription_id'], 'active');

                add_post_meta($create_order, 'payment_data', $payment_data);
                add_post_meta($create_order, 'payment_app_id', $app_id);
                add_post_meta($create_order, 'id_subscription_data', $id_subscription);

                Subscriptions_Emails::email_order(self::$status_default, $user->user_email, get_post_meta($create_order, '_member_suscription_name', true));
                Subscriptions_Emails::admin_new_order_email(get_post_meta($create_order, '_member_suscription_name', true));

                if(has_filter('mp_success_filter')) {
                    apply_filters( 'mp_success_filter', $payment_filter );
                }

                wp_redirect(get_permalink(get_option('subscriptions_thankyou')));

                exit();
            } else {

                Subscriptions_Sessions::set_flash_session('danger', __('An error occurred in order creation', 'subscriptions'));

                if(has_filter('mp_error_filter')) {
                    apply_filters( 'mp_error_filter', $payment_filter );
                }

                wp_redirect(get_permalink(get_option('subscriptions_payment_page')));
                exit();
            }
        }
    }

    private static function create_order($order_reference, $status, $user_status)
    {

        $create_order = [
            'post_title' => $order_reference,
            'post_status'   => 'publish',
            'post_type'     => 'memberships',
            'post_author'   => 1,
        ];

        $create = wp_insert_post($create_order);

        if ($create) {

            $suscription_period = get_post_meta($_POST['suscription_id'], '_period_time', true) === 'period_months' ? __('Months', 'suscrptions') : __('Days', 'subscriptions');
            $period = get_post_meta($_POST['suscription_id'], '_period_time', true) === 'period_months' ? 'month' : 'day';
            $suscription_period_number = get_post_meta($_POST['suscription_id'], '_period_number', true);

            $hoy = date('Y-m-d H:i:s');
            $sumo_mes = date("Y-m-d H:i:s", strtotime($hoy . '+ ' . $suscription_period_number . ' ' . $period));

            $type = Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_type'];

            $user = get_user_by('id',$_POST['user_id']);

            $order_data = [
                '_member_order_reference' => $order_reference,
                '_member_order_status' => $status,
                '_member_payment_method' => self::$id,
                '_member_payment_method_title' => self::$title,
                '_member_user_id' => $_POST['user_id'],
                '_member_renewal_date' => $sumo_mes,
                '_member_suscription_id' => $_POST['suscription_id'],
                '_member_suscription_name' => $_POST['suscription_name'],
                '_member_suscription_period' => $suscription_period,
                '_member_suscription_period_number' => $suscription_period_number,
                '_member_suscription_cost' => $_POST['transactionAmount'],
                '_member_suscription_user_email' => $user->user_email,
                'payment_type' => $type
            ];
            update_user_meta($_POST['user_id'], '_user_status', $user_status);

            foreach ($order_data as $key => $value) {
                update_post_meta($create, $key, $value);
            }
            return $create;
        }
    }
    /**
     * Scripts
     */
    public static function main_script()
    {
        if (is_page(get_option('subscriptions_payment_page')) || is_page(get_option('user_panel_page'))) {
            wp_enqueue_script('mercadopago_script', 'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js');
        }
    }

    public static function js_preferences()
    {
        if (is_page(get_option('subscriptions_payment_page')) || is_page(get_option('user_panel_page'))) {
            wp_enqueue_script('mp_preferences_js', plugin_dir_url(__FILE__) . 'js/index.js', null, SUSCRIPTIONS_VERSION, true);
            wp_localize_script('mp_preferences_js', 'mp_vars', [
                'public_key' => get_option('mp_public_key')
            ]);
        }
    }
    /**
     * subscriptions with plan
     */
    public static function plan_suscription($plan_id, $card_token, $email, $reference, $amount)
    {
        $data = [
            'preapproval_plan_id' => $plan_id,
            'card_token_id' => $card_token,
            'payer_email' => $email,
            'external_reference' => $reference,
            'back_url' => get_permalink(get_option('subscriptions_thankyou')),
            'notification_url' => home_url() . 'notifications/',
            'auto_recurring' => [
                'transaction_amount' => (float)$amount,
            ]

        ];
        return Mercadopago()->subscriptions($data, get_option('mp_access_token'));
    }
    /**
     * suscription without plan
     */
    public static function suscription_single($reason, $card_token, $email, $reference, $frecuency, $frecuency_type, $amount, $start_date)
    {
        $data = [
            'reason' => $reason,
            'collector_id' => get_option('mp_collector_id'),
            'status' => 'authorized',
            'back_url' => get_permalink(get_option('subscriptions_thankyou')),
            'notification_url' => home_url() . 'notifications/',
            'external_reference' => $reference, // referencia interna
            'payer_email' => $email,
            'card_token_id' => $card_token,
            'auto_recurring' => [
                'frequency' => (int)$frecuency, // frecuencia de cobro
                'frequency_type' => $frecuency_type, // se cobraria cada dia o mes segun frequency
                'transaction_amount' => (float)$amount, // monto a cobrar
                'currency_id' => 'ARS', // moneda con la que se cobrar
                'start_date' => $start_date,
            ],
        ];
        return Mercadopago()->subscriptions($data, get_option('mp_access_token'));
    }

    public static function dump($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    /**
     * Notifications
     */
    public static function get_notification_url($wp_rewrite)
    {
        $wp_rewrite->rules = array_merge(
            ['notifications/(\d+)/?$/(\d+)/?$' => 'index.php?topic=$matches[1]&id=$matches[2]'],
            $wp_rewrite->rules
        );
    }

    public static function query_notifications($query_vars)
    {
        $query_vars[] = 'topic';
        $query_vars[] = 'id';
        return $query_vars;
    }


    public static function notifitions_endpoint()
    {
        register_rest_route( 'subscriptions/v1', '/notifications', array(
            'methods' => 'POST',
            'callback' => [self::class,'notifications_function'],
            'permission_callback' => '__return_true',
          ) );
    }


    public static function notifications_function(WP_REST_Request $request)
    {
       $topic = $request->get_param('topic');
       $id = $request->get_param('id');

      return self::get_notification_body($topic,$id);
    }
    

    public static function get_notification_body($topic,$id)
    {
        global $wpdb;
        
          if ($topic && $topic === 'payment' && $id) {
            
            header("HTTP/1.1 200 OK");
            
            $subscription = Mercadopago()->get_subscription($id, get_option('mp_access_token'));

            $data = json_decode($subscription);
            $subscription_id = $data->{'metadata'}->{'preapproval_id'};

            //$get_notification = json_decode(file_get_contents('php://input'));
            // $myfile = fopen(dirname(__FILE__) . "/log/" . $id . "-" . date('ymdHis') . ".json", "w") or die("Unable to open file!");
            // $txt = $subscription;
            // fwrite($myfile, $txt);
    
            // fclose($myfile);

            $post_id = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_value=%s",$subscription_id));
          
            $post = get_post($post_id->{'post_id'});

            if ($data->{'operation_type'} === 'recurring_payment') {
                if ($data->{'status'} == 'approved') {

                    if ($post !== null) {
                        $args = [
                            'post_title' => $post->post_title,
                            'post_status'   => 'publish',
                            'post_type'     => 'memberships',
                            'post_author'   => 1,
                        ];

                        $new_order = wp_insert_post($args);

                        $old_post_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id=$post->ID");

                        if (count($old_post_meta) !== 0) {
                            $query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                            foreach ($old_post_meta as $meta) {
                                $key = $meta->meta_key;
                                $val = $meta->meta_value;

                                if ($key == '_member_order_status') continue;
                                if ($key == '_member_renewal_date') continue;

                                $sql_query[] = "SELECT $new_order, '$key', '$val'";
                            }
                            $query .= implode(" UNION ALL ", $sql_query);
                            $wpdb->query($query);

                            $hoy = date('Y-m-d H:i:s');
                            
                            $period = get_post_meta($post->ID, '_period_time', true) === 'period_months' ? 'month' : 'day';
                            $suscription_period_number = get_post_meta($post->ID, '_period_number', true);

                            $sumo_mes = date("Y-m-d H:i:s", strtotime($hoy . '+ ' . $suscription_period_number . ' ' . $period));


                            update_post_meta($new_order, '_member_order_status', 'renewal');
                            update_post_meta($new_order, '_member_renewal_date', $sumo_mes);
                        }
                        exit();
                    }
                } else if($data->{'status'} == 'rejected') {
                    if($post !== null) {

                        $args = [
                            'post_title' => $post->post_title,
                            'post_status'   => 'publish',
                            'post_type'     => 'memberships',
                            'post_author'   => 1,
                        ];

                        $new_order = wp_insert_post($args);

                        $old_post_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id=$post->ID");

                        if (count($old_post_meta) !== 0) {
                            $query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                            foreach ($old_post_meta as $meta) {
                                $key = $meta->meta_key;
                                $val = $meta->meta_value;

                                if ($key == '_member_order_status') continue;
                                if ($key == '_member_renewal_date') continue;

                                $sql_query[] = "SELECT $new_order, '$key', '$val'";
                            }
                            $query .= implode(" UNION ALL ", $sql_query);
                            $wpdb->query($query);

                            $hoy = date('Y-m-d H:i:s');
                            
                            $period = get_post_meta($post->ID, '_period_time', true) === 'period_months' ? 'month' : 'day';
                            $suscription_period_number = get_post_meta($post->ID, '_period_number', true);

                            $sumo_mes = date("Y-m-d H:i:s", strtotime($hoy . '+ ' . $suscription_period_number . ' ' . $period));


                            update_post_meta($new_order, '_member_order_status', 'error');
                            update_post_meta($new_order, '_member_renewal_date', $sumo_mes);

                            $user_meta = get_post_meta($post->ID,'_member_user_id',true);

                            update_user_meta($user_meta, '_user_status', 'inactive');
                        }
                    }
                    
                     exit();
                }
            }
        } 
    }
    /**
     * User
     */
    private static function update_user($id_user, $id_suscription, $status)
    {
        $suscription = get_post($id_suscription);
        $update_meta = update_user_meta($id_user, 'suscription', $id_suscription);
        $update_meta .= update_user_meta($id_user, 'suscription_name', $suscription->post_title);

        return $update_meta;
    }
    /**
     * Cancel
     */
    public static function cancel_membership()
    {
        if (isset($_POST['action']) && $_POST['action'] === 'cancel_membership' && isset($_POST['payment_method_id']) && $_POST['payment_method_id'] === self::$id) {
            $user = get_userdata($_POST['user_id']);


            foreach (get_post_meta($_POST['membership_id'], 'payment_data', false) as $key => $val) {
                $id_mp = array_shift($val);
            }

            $id_subscription = get_post_meta($_POST['membership_id'], 'id_subscription_data', true);
            $update = update_post_meta($_POST['membership_id'], '_member_order_status', 'cancel');
            $cancel = Mercadopago()->cancel_subscription($id_subscription, get_option('mp_access_token'));
            /**
             * Log pyament
             */
            if (get_option('mp_log') === '1') {
                self::payment_log($cancel);
            }
            if ($update) {
                Subscriptions_Emails::email_order('cancel', $user->user_email, get_post_meta($_POST['membership_id'], '_member_suscription_name', true));

                delete_user_meta($user->ID, 'suscription');
                delete_user_meta($user->ID, 'suscription_name');
                update_user_meta($user->ID, '_user_status', 'inactive');

                Subscriptions_Sessions::set_flash_session('success', __('Your membership is now cancel', 'subscriptions'));
                wp_redirect(get_permalink(get_option('subscriptions_profile')) . '#subscription');
                exit();
            } else {
                Subscriptions_Sessions::set_flash_session('danger', __('Error in transaction', 'subscriptions'));
                wp_redirect(get_permalink(get_option('subscriptions_profile')) . '#subscription');
                exit();
            }
        }
    }
    /**
     * edit form
     */
    public static function edit_form_fields()
    {
        $form = '<div id="paymentFormmp" class="">
            <div class="form-group">
                <label for="cardholderName">Titular de la tarjeta</label>
                <input id="cardholderName" class="form-control" data-checkout="cardholderName" placeholder="Titular de la tarjeta" type="text">
            </div>
            <div class="row">
                <div class="form-group col-md-4 col-12">
                    <label for="docType">Tipo de documento</label>
                    <select id="docType" class="form-control" name="docType" data-checkout="docType" type="text"></select>
                </div>
                <div class="form-group col-md-8 col-12">
                    <label for="docNumber">Número de documento</label>
                    <input id="docNumber" class="form-control" name="docNumber" data-checkout="docNumber" placeholder="Número de documento" type="text" />
                </div>
            </div>
            <div class="row">
            <div class="form-group col-md-6 col-12">
                <label for="">Fecha de vencimiento</label>
                    <input type="text" class="form-control" placeholder="MM" id="cardExpirationMonth" data-checkout="cardExpirationMonth" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
            </div>
            <div class="form-group col-md-6 col-12">
                    <input type="text" class="form-control" placeholder="AA" id="cardExpirationYear" data-checkout="cardExpirationYear" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
            </div>
            </div>
            <div class="form-group">
                <label for="cardNumber">Número de la tarjeta</label>
                <input type="text" class="form-control" id="cardNumber" placeholder="Número de la tarjeta" data-checkout="cardNumber" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
            </div>
            <div class="form-group">
                <label for="securityCode">Código de seguridad</label>
                <input id="securityCode" class="form-control" placeholder="Código de seguridad" data-checkout="securityCode" type="text" onselectstart="return false" onpaste="return false" oncopy="return false" oncut="return false" ondrag="return false" ondrop="return false" autocomplete=off>
            </div>
            <input type="hidden" name="token" id="token" value="">
            <input type="hidden" name="paymentMethodId" id="paymentMethodId" /></div>';
            
        return $form;
    }

    public static function edit_process()
    {
        if (isset($_POST['action']) &&  $_POST['action'] === 'edit_membership' && isset($_POST['token']) && $_POST['payment_method_id'] == self::$id) {
 
            $id_subscription = get_post_meta($_POST['membership_id'], 'id_subscription_data', true);
            $data = [
                'external_reference' => get_post_meta($_POST['membership_id'], '_member_order_reference', true),
                'application_id' => get_post_meta($_POST['membership_id'], 'payment_app_id', true),
                'auto_recurring' => [
                    'currency_id' => 'ARS',
                    'transaction_amount' => $_POST['amount'],
                ],
                'card_token_id' => $_POST['token']

            ];

            if (get_option('mp_log') === '1') {
                 self::payment_log(json_encode($data));
            }
           

            $edit = Mercadopago()->edit_subscription($data, $id_subscription, get_option('mp_access_token'));
            /**
             * Log pyament
             */
            if (get_option('mp_log') === '1') {
                self::payment_log(json_encode($edit));
            }
            if ($edit) {
                update_post_meta($_POST['membership_id'], '_member_suscription_cost', $_POST['amount']);
                update_post_meta($_POST['membership_id'], '_member_suscription_id', $_POST['subs_change']);
                update_post_meta($_POST['membership_id'], '_member_suscription_name', $_POST['subscription_name']);
                update_user_meta($_POST['user_id'], 'suscription', $_POST['subs_change']);
                update_user_meta($_POST['user_id'], 'suscription_name', $_POST['subscription_name']);
                update_user_meta($_POST['user_id'], '_user_status', 'active');
            }

            if(isset($_POST['paper'])){
                update_user_meta($_POST['user_id'], '_user_paper', 1);
             } else {
                 update_user_meta($_POST['user_id'], '_user_paper', 0);
             }

            Subscriptions_Sessions::set_flash_session('success', __('Your membership is edit', 'subscriptions'));
            wp_redirect(get_permalink(get_option('subscriptions_profile')) . '#subscription');
            exit();
        }
    }

    public static function edit_form_subscription()
    {
        if (membership()->get_membership(wp_get_current_user()->ID)['payment'] === self::$id) {
            echo self::edit_form_fields();
        }
    }

    public static function update_all_price($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ('subscriptions' !== $post->post_type) {
            return;
        }


        $args = [
            'post_type' => 'memberships',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_member_suscription_id',
                    'value' => $post_id,
                ],
                [
                    'key' => '_member_order_status',
                    'value' => 'completed'
                ]
            ]
        ];

        $query = get_posts($args);

        foreach ($query as $q) {

            $id_subscription = get_post_meta($q->{'ID'}, 'id_subscription_data', true);

            $price_old = get_post_meta($q->{'ID'}, '_member_suscription_cost', true);
            $price_new = get_post_meta($post_id, '_s_price', true);

            if ($price_old < $price_new) {
                $data = [
                    'application_id' => get_post_meta($q->{'ID'}, 'payment_app_id', true),
                    'auto_recurring' => [
                        'currency_id' => 'ARS',
                        'transaction_amount' => $price_new,
                    ]
                ];

                $edit = Mercadopago()->edit_subscription($data, $id_subscription, get_option('mp_access_token'));

                if (get_option('mp_log') === '1') {
                    self::payment_log($edit);
                }
                if ($edit) {
                    update_post_meta($q->{'ID'}, '_member_suscription_cost', $price_new);
                    update_post_meta($q->{'ID'}, '_member_suscription_name', get_the_title($post_id));
                }
            }
        }
    }
}
