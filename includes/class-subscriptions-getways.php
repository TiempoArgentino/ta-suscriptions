<?php


class Payment_Getways
{

    public $dir;

    public function __construct()
    {
        $this->dir = plugin_dir_path(dirname(__FILE__)) . 'includes/payment-getways/';
        $this->load_default_payments_method();
        $this->payment_load_classes();

        add_action('payment_getways', [$this, 'payment_load_front']);
        add_action('subscriptions_pyament_methods', [$this, 'payment_load_admin']);
        add_action('subscriptions_payment_page_header', [$this, 'payment_post_process']);
        add_action('payment_messages', [$this, 'show_flash_session']);
        add_action('messages_thankyou', [$this, 'show_flash_session']);

        add_action('membership_edit_messages',[$this, 'show_flash_session']);

        add_action('edit_form_subscriptions',[$this,'edit_form']);
    }
    /**
     * Default payment class
     */
    public function default_payment_methods()
    {
        $default_getways = [
            'Subscriptions_Bank',
            'Subscriptions_MP'
        ];

        if (has_filter('custom_getway')) {
            $custom_getway = apply_filters('custom_getway', $custom_getway);
        }

        return $default_getways;
    }
    /**
     * Require files
     */
    public function load_default_payments_method()
    {
        $file_getways = array_diff(scandir($this->dir), array('..', '.'));
        foreach ($file_getways as $file) {
            if (!strpos($file, '.')) {
                require $this->dir . $file . '/' . $file . '.php';
            }
        }
    }
    /**
     * Init classes
     */
    public function payment_load_classes()
    {
        foreach ($this->default_payment_methods() as $getway) {
            if (is_string($getway) && class_exists($getway)) {
                $payments = $getway::init();
            }
        }
    }
    /**
     * Payments admin
     */
    public function payment_load_admin()
    {
        foreach ($this->default_payment_methods() as $getway) {
            if (is_string($getway) && class_exists($getway)) {
                $payments = $getway::admin();
            }
        }
    }
    /**
     * Form admin
     */
    public static function admin_config_form($id, $title, $body)
    {
        $output = '<div class="panel">';
        $output .= '<div class="panel-header" data-id="#' . $id . '"><h4>' . $title . ' <span class="dashicons dashicons-arrow-down-alt2"></span></h4></div>';
        $output .= '<form method="post" class="panel-form" id="' . $id . '">';
        $output .= $body;
        $output .= '</form>';
        $output .= '</div>';
        echo $output;
    }
    /**
     * Front
     */
    public function payment_load_front()
    {
        foreach ($this->default_payment_methods() as $getway) {
            if (is_string($getway) && class_exists($getway)) {
                $payments = $getway::front();
            }
        }
    }
    /**
     * View and select payment front
     */
    public static function front_payment_view($id, $title, $body)
    {
        $output = '<form method="post" class="form row text-center subscritpions-form" id="paymentForm' . $id . '">';
        $output .= '<li class="payment-panel-front payment-col mb-3" data-id="' . $id . '">';
        $output .= $title;
        $output .= '<div class="payment-body pb-3" id="description-' . $id . '">' . $body . '</div>';
        $output .= '</li>';
        $output .= '</form>';
        echo $output;
    }

    /**
     * Post proccess
     */
    public function payment_post_process()
    {
        foreach ($this->default_payment_methods() as $getway) {
            if (is_string($getway) && class_exists($getway)) {
                $payments = $getway::post_process();
            }
        }
    }
    /**
     * show
     */
    public function show_flash_session()
    {
        $session = Subscriptions_Sessions::get_session('flash_messages');
        if ($session) {
            Subscriptions_Messages::messages($session['name'], $session['msg']);
        }
    }

    public function edit_form()
    {

    }
   
}


$subscriptions_payment_getways = new Payment_Getways();
