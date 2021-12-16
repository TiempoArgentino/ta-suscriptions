<?php

/**
 * Field to use: CBU, DNI or CUIT, terms and condition
 */

class Subscriptions_Bank extends Payment_Getways
{


    static private $init = false;

    static public $id;
    static public $title;
    static public $description;
    static public $active;
    static public $status_default;

    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;

        self::$id = 'banks';
        self::$title = get_option('banks_title', 'Automatic bank debit');
        self::$description = get_option('banks_descriptions', 'Description of pyment method');
        self::$active = get_option('banks_active', 1);
        self::$status_default =  'on-hold';

        self::save_conf();
        add_action('subscriptions_edit_actions', [__CLASS__, 'cancel_membership']);
        add_action('edit_form_extra', [self::class, 'edit_form_subscription']);
        add_action('init', [self::class, 'edit_process']);
    }
    /**
     * Metabox
     */

    public static function admin()
    {
        self::admin_panel_conf();
    }

    public static function front()
    {
        if ('1' === self::$active)
            self::show_payment();
    }
    /**
     * Configuration
     */
    public static function admin_panel_conf()
    {
        $body = '<label><input type="checkbox" name="banks_active" value="1" ' . checked(self::$active, '1', false) . ' /> ' . __('Active?', 'subscriptions') . '</label>';
        $body .= '<label>' . __('Title', 'subscriptions') . '</label>';
        $body .= '<input type="text" class="payment_input_field" name="banks_title" value="' . self::$title . '" />';
        $body .= '<label>' . __('Add or change description', 'subscriptions') . '</label>';
        $body .= '<textarea class="payment_input_textarea" name="banks_descriptions">' . self::$description . '</textarea>';
        $body .= '<input type="submit" name="bank_submit" class="button button-primary" value="' . __('Submit', 'subscriptions') . '" />';

        return self::admin_config_form(self::$id, self::$title, $body);
    }
    /**
     * Save configuration
     */
    public static function save_conf()
    {
        if (isset($_POST['bank_submit'])) {
            if (isset($_POST['banks_active']))
                update_option('banks_active', 1);
            else
                update_option('banks_active', 0);

            if (isset($_POST['banks_title']))
                update_option('banks_title', sanitize_text_field($_POST['banks_title']));

            if (isset($_POST['banks_descriptions']))
                update_option('banks_descriptions', esc_textarea($_POST['banks_descriptions']));


            header('Location: ' . admin_url('admin.php?page=tar_pagos'));
            exit();
        }
    }
    /**
     * Front
     */
    public static function show_payment()
    {
        $body = '<div class="payment-description alert alert-dark mt-3">' . self::$description . '</div>';
        $body .= self::form_payment();
        $title = '<label class="form-check-label payment-title"><input type="radio" name="payment_select_bank" class="payment-select ' . self::$id . ' form-check-input" value="' . self::$id . '" /> ' . self::$title . '</label>';
        return self::front_payment_view(self::$id, $title, $body);
    }

    public static function form_payment()
    {


        $form = '<div class="row">';
        $form .= '<div class="form-group col-md-4 col-12"><label>' . __('ID Type', 'subscriptions') . '</label>';
        $form .= '<select name="doc_type" id="doc_type" class="form-control" required>
                    <option value=""> ' . __('type', 'subscriptions') . ' </option>
                    <option value="DNI">' . __('DNI', 'subscriptions') . '</option>
                    <option value="CI">' . __('CI', 'subscriptions') . '</option>
                    <option value="LC">' . __('LC', 'subscriptions') . '</option>
                    <option value="LE">' . __('LE', 'subscriptions') . '</option>
                    <option value="other">' . __('Other', 'subscriptions') . '</option>
                  </select>';
        $form .= '</div>';
        $form .= '<div class="form-group col-md-8 col-12">';
        $form .= '<label>' . __('ID Number', 'subscriptions') . '</label>';
        $form .= '<input type="text" name="dni_number" id="dni_number" class="form-control" placeholder="Número" value="" required />';
        $form .= '</div></div>';
        $form .= '<div class="form-group">';
        $form .= '<label>' . __('CBU / Alias', 'subscriptions') . '</label>';
        $form .= '<input type="number" name="cbu_bank" id="cbu_bank" class="form-control" value="" placeholder="CBU" required />';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label>' . __('CUIT / CUIL', 'subscriptions') . '</label>';
        $form .= '<input type="number" name="dni_bank" id="dni_bank" class="form-control" placeholder="CUIT" value="" required />';
        $form .= '</div>';
        $form .= ' <input type="hidden" name="suscription_price" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_price'] . '">';
        $form .= '<div class="form-check mb-3">
                    <label class="form-check-label" for="payment-check"><input type="checkbox" id="terms-conditions_bank" class="form-check-input" name="terms-conditions_bank" value="1" required /><a href="' . get_permalink(get_option('subscriptions_terms_page')) . '" target="_blank"> ' . __('I accept terms and conditions', 'subscriptions') . '</a></label>
                </div>';
        $form .= '<input type="hidden" name="user_id" value="' . wp_get_current_user()->ID . '">
        <input type="hidden" name="suscription_id" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_id'] . '">
        <input type="hidden" name="suscription_name" value="' . Subscriptions_Sessions::get_session('subscriptions_add_session')['suscription_name'] . '">
        <span class="d-none" id="loader-address-bank">
            <img src="'.plugin_dir_url(__DIR__).'bank/img/loader-button.gif" />
            <span class="text-center d-block">Un momento por favor...</span>
        </span>
        <input type="submit" class="btn btn-lg btn-primary payment-button-submit" name="paymentBank" id="paymentBank" value="' . __('Send data', 'subscriptions') . '">';
        return $form;
    }
    /**
     * Payment proccess
     */
    public static function post_process($payment_filter = '')
    {
        if (isset($_POST['payment_select_bank']) && $_POST['payment_select_bank'] === self::$id) {

            $user = get_userdata($_POST['user_id']);

            $cbu = $_POST['cbu_bank'];
            $cuil = $_POST['dni_bank'];
            $dni = $_POST['dni_number'];


            if (!isset($cbu) || $cbu === '' || !isset($dni) || $dni === '') {

                Subscriptions_Sessions::set_flash_session('danger', __('All field are required', 'subscriptions'));

                wp_redirect(get_permalink(get_option('subscriptions_payment_page')));
                exit();
            } else {
                Subscriptions_Sessions::destroy_session('flash_messages');

                $order_reference = get_option('member_sku_prefix', 'TA-') . date('YmdHms');

                $create_order = self::create_order($order_reference, 'on-hold');


                if (!isset(Subscriptions_Sessions::get_session('subscriptions_add_session')['order_reference'])) {
                    Subscriptions_Sessions::update_session('subscriptions_add_session', 'order_reference', $order_reference);
                }

                if ($create_order) {

                    self::update_user($_POST['user_id'], $_POST['suscription_id'], 'inactive');

                    Subscriptions_Emails::email_order(self::$status_default, $user->user_email, get_post_meta($create_order, '_member_suscription_name', true));
                    Subscriptions_Emails::admin_new_order_email(get_post_meta($create_order, '_member_suscription_name', true));

                    $payment_data = [
                        'CBU' => $cbu,
                        'DNI' => $dni,
                        'CUIL' => $cuil
                    ];

                    add_post_meta($create_order, 'payment_data', $payment_data);

                    if (has_filter('bk_success_filter')) {
                        apply_filters('bk_success_filter', $payment_filter);
                    }

                    wp_redirect(get_permalink(get_option('subscriptions_thankyou')));
                    exit();
                } else {

                    Subscriptions_Sessions::set_flash_session('danger', __('An error occurred in order creation', 'subscriptions'));

                    if (has_filter('bk_error_filter')) {
                        apply_filters('bk_error_filter', $payment_filter);
                    }

                    wp_redirect(get_permalink(get_option('subscriptions_payment_page')));
                    exit();
                }
            }
        }
    }

    public static function create_order($order_reference, $user_status)
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

            $user = get_user_by('id', $_POST['user_id']);

            $order_data = [
                '_member_order_reference' => $order_reference,
                '_member_order_status' => self::$status_default,
                '_member_payment_method' => self::$id,
                '_member_payment_method_title' => self::$title,
                '_member_user_id' => $_POST['user_id'],
                '_member_renewal_date' => $sumo_mes,
                '_member_suscription_id' => $_POST['suscription_id'],
                '_member_suscription_name' => $_POST['suscription_name'],
                '_member_suscription_period' => $suscription_period,
                '_member_suscription_period_number' => $suscription_period_number,
                '_member_suscription_cost' => $_POST['suscription_price'],
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

            $update = update_post_meta($_POST['membership_id'], '_member_order_status', 'cancel');

            if ($update) {
                Subscriptions_Emails::email_order('cancel', $user->user_email, get_post_meta($_POST['membership_id'], '_member_suscription_name', true));

                delete_user_meta($user->ID, 'suscription');
                delete_user_meta($user->ID, 'suscription_name');
                update_user_meta($user->ID, 'user_status', 'inactive');

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

    public static function edit_form_fields()
    {
        $payment_data = get_post_meta(membership()->get_membership(wp_get_current_user()->ID)['id'], 'payment_data', true);
        $form = '<div id="paymentFormBankEdit">';
        $form .= '<p>La membresía quedará en espera hasta que el pago sea confirmado por un administrador.</p>';
        $form .= '<div class="row"><div class="form-group col-md-4 col-12"><label>' . __('ID Type', 'subscriptions') . '</label>';
        $form .= '<select name="doc_type" id="doc_type" class="form-control" required>
                    <option value=""> ' . __('type', 'subscriptions') . ' </option>
                    <option value="DNI" '.selected('DNI',$payment_data['TYPE'],false).'>' . __('DNI', 'subscriptions') . '</option>
                    <option value="CI" '.selected('CI',$payment_data['TYPE'],false).'>' . __('CI', 'subscriptions') . '</option>
                    <option value="LC" '.selected('LC',$payment_data['TYPE'],false).'>' . __('LC', 'subscriptions') . '</option>
                    <option value="LE" '.selected('LE',$payment_data['TYPE'],false).'>' . __('LE', 'subscriptions') . '</option>
                    <option value="other" '.selected('other',$payment_data['TYPE'],false).'>' . __('Other', 'subscriptions') . '</option>
                  </select>';
        $form .= '</div>';
        $form .= '<div class="form-group col-md-8 col-12">';
        $form .= '<label>' . __('ID Number', 'subscriptions') . '</label>';
        $form .= '<input type="text" name="dni_number" id="dni_number" class="form-control" placeholder="Número" value="'.$payment_data['DNI'].'" required />';
        $form .= '</div></div>';
        $form .= '<div class="form-group">';
        $form .= '<label>' . __('CBU / Alias', 'subscriptions') . '</label>';
        $form .= '<input type="number" name="cbu_bank" id="cbu_bank" class="form-control" value="'.$payment_data['CBU'].'" placeholder="CBU" required />';
        $form .= '</div>';
        $form .= '<div class="form-group">';
        $form .= '<label>' . __('CUIT / CUIL', 'subscriptions') . '</label>';
        $form .= '<input type="number" name="dni_bank" id="dni_bank" class="form-control" placeholder="CUIT" value="'.$payment_data['CUIL'].'" required /></div>';
        $form .= '</div>';
        return $form;
    }

    public static function edit_form_subscription()
    {
        if (membership()->get_membership(wp_get_current_user()->ID)['payment'] === self::$id) {
            echo self::edit_form_fields();
        }
    }

    public static function edit_process()
    {
        if(isset($_POST['action']) &&  $_POST['action'] === 'edit_membership' && $_POST['payment_method_id'] == self::$id) {

            update_post_meta($_POST['membership_id'], '_member_suscription_cost', $_POST['amount']);
            update_post_meta($_POST['membership_id'], '_member_suscription_id', $_POST['subs_change']);
            update_post_meta($_POST['membership_id'], '_member_suscription_name', $_POST['subscription_name']);
            update_user_meta($_POST['user_id'], 'suscription', $_POST['subs_change']);
            update_user_meta($_POST['user_id'], 'suscription_name', $_POST['subscription_name']);
            update_user_meta($_POST['user_id'], '_user_status', 'on-hold');

            if(isset($_POST['cbu_bank']) && isset($_POST['dni_number']) && isset($_POST['dni_bank'])) {
                $payment_data = [
                    'TYPE' => $_POST['doc_type'],
                    'CBU' => $_POST['cbu_bank'],
                    'DNI' => $_POST['dni_number'],
                    'CUIL' => $_POST['dni_bank']
                ];

                update_post_meta($_POST['membership_id'], 'payment_data', $payment_data);
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
}
