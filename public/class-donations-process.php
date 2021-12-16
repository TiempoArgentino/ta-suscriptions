<?php


class Donations_Proccess
{

    private $nonce = 'donations-nonce';
    private $action = 'donations-ajax-action';
    private $add_session = 'donations_add_session';
    private $url;

    public function __construct()
    {
        $this->url = admin_url('admin-ajax.php');

        add_action('wp_enqueue_scripts', [$this, 'donations_ajax_script']);


        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_with_price_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_with_price_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_with_custom_price_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_with_custom_price_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_user_session']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_user_session']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'discount_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'discount_ajax']);
    }
    /**
     * Ajax script
     */
    public function donations_ajax_script($extra = '')
    {
        wp_enqueue_script('donations_ajax_script', plugin_dir_url(__FILE__) . 'js/donations-ajax.js', array('jquery'), SUSCRIPTIONS_VERSION, true);
        $this->add_user_vars();
        $this->add_with_price_vars();
        $this->add_with_custom_price_vars();
        

        if (has_filter('donations_ajax_ext')) {
            apply_filters('donations_ajax_ext', $extra);
        }
    }
    /**
     * Subscriptions localize scripts
     */
    public function donations_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => $this->url,
            '_ajax_nonce'  => wp_create_nonce($this->nonce),
            'action' => $this->action,
            'sending' => __('Checking...', 'subscriptions')
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('donations_ajax_script', $var_data, $fields);
    }
    /**
     * Add suscription with price
     */
    public function add_with_price_vars()
    {
        $add_price = isset($_POST['add_price']) ? $_POST['add_price'] : '';
        $donations_id = isset($_POST['donations_id']) ? sanitize_text_field($_POST['donations_id']) : '';
        $donations_price = isset($_POST['donations_price']) ? sanitize_text_field($_POST['donations_price']) : '';
        $donations_name = isset($_POST['donations_name']) ? sanitize_text_field($_POST['donations_name']) : '';
        $donations_type = isset($_POST['donations_type']) ? sanitize_text_field($_POST['donations_type']) : '';
        $fields = [
            'add_price' => $add_price,
            'donations_id' => $donations_id,
            'donations_price' => $donations_price,
            'donations_name' => $donations_name,
            'donations_type' => $donations_type
        ];

        return $this->donations_localize_script('ajax_add_price_data', $fields);
    }
    /**
     * Ajax add price response
     */
    public function add_with_price_ajax()
    {

        if (isset($_POST['add_price'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if (null !== Subscriptions_Sessions::get_session($this->add_session)) {
                Subscriptions_Sessions::destroy_session($this->add_session);
            }

            $data = [
                'donations_id' => sanitize_text_field($_POST['donations_id']),
                'donations_price' => sanitize_text_field($_POST['donations_price']),
                'donations_name' => sanitize_text_field($_POST['donations_name']),
                'donations_type' => sanitize_text_field($_POST['suscription_type'])
            ];

            Subscriptions_Sessions::set_session($this->add_session, $data);
            wp_die();
        }
    }
    /**
     * Ajax add with custom price
     */
    public function add_with_custom_price_vars()
    {
        $add_price_custom = isset($_POST['add_price_custom']) ? $_POST['add_price_custom'] : '';
        $donations_id = isset($_POST['donations_id']) ? sanitize_text_field($_POST['donations_id']) : '';
        $donations_price = isset($_POST['donations_price']) ? sanitize_text_field($_POST['donations_price']) : '';
        $donations_name = isset($_POST['donations_name']) ? sanitize_text_field($_POST['donations_name']) : '';
        $donations_type = isset($_POST['donations_type']) ? sanitize_text_field($_POST['donations_type']) : '';
        $fields = [
            'add_price_custom' => $add_price_custom,
            'donations_id' => $donations_id,
            'donations_price' => $donations_price,
            'donations_name' => $donations_name,
            'suscription_type' => $donations_type
        ];

        return $this->donations_localize_script('ajax_add_custom_price_data', $fields);
    }
    /**
     * Ajax add with custom price response
     */
    public function add_with_custom_price_ajax()
    {

        if (isset($_POST['add_price_custom'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if (null !== Subscriptions_Sessions::get_session($this->add_session)) {
                Subscriptions_Sessions::destroy_session($this->add_session);
            }

            $data = [
                'donation_id' => sanitize_text_field($_POST['donations_id']),
                'donation_price' => sanitize_text_field($_POST['donations_price']),
                'donation_name' => sanitize_text_field($_POST['donations_name']),
                'suscription_type' => sanitize_text_field($_POST['suscription_type'])
            ];

            Subscriptions_Sessions::set_session($this->add_session, $data);
            wp_die();
        }
    }
    /**
     * add user
     */
    public function add_user_vars()
    {
        $add_user = isset($_POSt['add_user']) ? $_POST['add_user'] : '';
        $name = isset($_POST['donations_name']) ? $_POST['donations_name'] : '';
        $lastname = isset($_POST['donations_lastname']) ? $_POST['donations_lastname'] : '';
        $email = isset($_POST['donations_email']) ? $_POST['donations_email'] : '';

        $fields = [
            'add_user' => $add_user,
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email
        ];

        return $this->donations_localize_script('ajax_add_user', $fields);
    }
    /**
     * Add user to session
     */
    public function add_user_session()
    {
        if (isset($_POST['add_user'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if (null !== Subscriptions_Sessions::get_session($this->add_session)) {
                Subscriptions_Sessions::update_session($this->add_session, 'user_name', sanitize_text_field($_POST['name']));
                Subscriptions_Sessions::update_session($this->add_session, 'user_lastname', sanitize_text_field($_POST['lastname']));
                Subscriptions_Sessions::update_session($this->add_session, 'user_email', sanitize_text_field($_POST['email']));
            }
            wp_die();
        }
    }
   
}


$donations_proccess = new Donations_Proccess();
