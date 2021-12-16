<?php


class Subscriptions_Proccess
{

    private $nonce = 'subscriptions-nonce';
    private $action = 'subscriptions-ajax-action';
    private $add_session = 'subscriptions_add_session';
    private $url;

    public function __construct()
    {
        $this->url = admin_url('admin-ajax.php');

        add_action('wp_enqueue_scripts', [$this, 'subscriptions_ajax_script']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'login_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'login_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'register_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'register_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_with_price_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_with_price_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_with_custom_price_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_with_custom_price_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'add_address_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'add_address_ajax']);

        add_action('after_setup_theme',[$this,'verify_subscription'],10,1);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'discount_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'discount_ajax']);

        add_action('wp_ajax_nopriv_' . $this->action, [$this, 'contact_ajax']);
        add_action('wp_ajax_' . $this->action, [$this, 'contact_ajax']);

    }
    /**
     * Verify
     */
    public function verify_subscription($user_id)
    {
        if(is_page(get_option('subscriptions_loop_page'))){
            if(is_user_logged_in()){
                $subscription = get_user_meta($user_id, 'suscription', true);
                $donation = get_user_meta($user_id,'_is_donation',true);
                if($subscription !== '' && $donation === null) {
                    if(null !== Subscriptions_Sessions::get_session($this->add_session)) {
                        Subscriptions_Sessions::destroy_session($this->add_session);
                    }
                    return true;
                }
            }
        } 
    }
    /**
     * Ajax script
     */
    public function subscriptions_ajax_script($extra = '')
    {
        wp_enqueue_script('subscriptions_ajax_script', plugin_dir_url(__FILE__) . 'js/subscriptions-ajax.js', array('jquery'), SUSCRIPTIONS_VERSION, true);
        $this->login_vars();
        $this->register_vars();
        $this->add_with_price_vars();
        $this->add_with_custom_price_vars();
        $this->add_address_vars();
        $this->discount_vars();
        $this->contact_vars();

        if(has_filter('subscriptions_ajax_ext')){
            apply_filters( 'subscriptions_ajax_ext',$extra );
        }
    }
    /**
     * Subscriptions localize scripts
     */
    public function subscriptions_localize_script($var_data, $data)
    {
        $fields = [
            'url'    => $this->url,
            '_ajax_nonce'  => wp_create_nonce($this->nonce),
            'action' => $this->action,
            'sending' => __('Checking...', 'subscriptions')
        ];

        $fields = array_merge($fields, $data);

        wp_localize_script('subscriptions_ajax_script', $var_data, $fields);
    }
    /**
     * Add suscription with price
     */
    public function add_with_price_vars()
    {
        $add_price = isset($_POST['add_price']) ? $_POST['add_price'] : '';
        $suscription_id = isset($_POST['suscription_id']) ? sanitize_text_field($_POST['suscription_id']) : '';
        $suscription_price = isset($_POST['suscription_price']) ? sanitize_text_field($_POST['suscription_price']) : '';
        $suscription_name = isset($_POST['suscription_name']) ? sanitize_text_field($_POST['suscription_name']) : '';
        $suscription_type = isset($_POST['suscription_type']) ? sanitize_text_field($_POST['suscription_type']) : '';
        $suscription_address = isset($_POST['suscription_address']) ? $_POST['suscription_address'] : '';
        $suscription_role = isset($_POST['suscription_role']) ? $_POST['suscription_role'] : '';
        
        $fields = [
            'add_price' => $add_price,
            'suscription_id' => $suscription_id,
            'suscription_price' => $suscription_price,
            'suscription_name' => $suscription_name,
            'suscription_type' => $suscription_type,
            'suscription_address' => $suscription_address,
            'suscription_role' => $suscription_role
        ];

        return $this->subscriptions_localize_script('ajax_add_price_data', $fields);
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

            if(null !== Subscriptions_Sessions::get_session($this->add_session)) {
                Subscriptions_Sessions::destroy_session($this->add_session);
            }

            $data = [
                'suscription_id' => sanitize_text_field($_POST['suscription_id']),
                'suscription_price' => sanitize_text_field($_POST['suscription_price']),
                'suscription_name' => sanitize_text_field($_POST['suscription_name']),
                'suscription_type' => sanitize_text_field($_POST['suscription_type']),
                'suscription_address' => isset($_POST['suscription_address']) ? sanitize_text_field($_POST['suscription_address']) : null,
                'suscription_role' => $_POST['suscription_role']
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
        $suscription_id = isset($_POST['suscription_id']) ? sanitize_text_field($_POST['suscription_id']) : '';
        $suscription_price = isset($_POST['suscription_price']) ? sanitize_text_field($_POST['suscription_price']) : '';
        $suscription_name = isset($_POST['suscription_name']) ? sanitize_text_field($_POST['suscription_name']) : '';
        $suscription_type = isset($_POST['suscription_type']) ? sanitize_text_field($_POST['suscription_type']) : '';
        $suscription_address = isset($_POST['suscription_address']) ? $_POST['suscription_address'] : '';
        $suscription_role = isset($_POST['suscription_role']) ? $_POST['suscription_role'] : '';

        $fields = [
            'add_price_custom' => $add_price_custom,
            'suscription_id' => $suscription_id,
            'suscription_price' => $suscription_price,
            'suscription_name' => $suscription_name,
            'suscription_type' => $suscription_type,
            'suscription_address' => $suscription_address,
            'suscription_role' => $suscription_role
        ];

        return $this->subscriptions_localize_script('ajax_add_custom_price_data', $fields);
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

            if(null !== Subscriptions_Sessions::get_session($this->add_session)) {
                Subscriptions_Sessions::destroy_session($this->add_session);
            }

            $data = [
                'suscription_id' => sanitize_text_field($_POST['suscription_id']),
                'suscription_price' => sanitize_text_field($_POST['suscription_price']),
                'suscription_name' => sanitize_text_field($_POST['suscription_name']),
                'suscription_type' => sanitize_text_field($_POST['suscription_type']),
                'suscription_address' => sanitize_text_field($_POST['suscription_address']),
                'suscription_role' => $_POST['suscription_role']
            ];

            Subscriptions_Sessions::set_session($this->add_session, $data);
            wp_die();
        }
    }
    /**
     * Login
     */
    public function login_vars()
    {
        $send_login = isset($_POST['send-login']) ? $_POST['send-login'] : '';
        $username = isset($_POST['username']) ? sanitize_email($_POST['username']) : '';
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
        $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : '';
        $fields = [
            'login' => $send_login,
            'username' => $username,
            'password' => $password,
            'redirect_to' => $redirect_to
        ];
        return $this->subscriptions_localize_script('ajax_login_data', $fields);
    }
    /**
     * Ajax login response
     */
    public function login_ajax()
    {
        //$ok = false;
        if (isset($_POST['login'])) {
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if (isset($_POST['username']) && isset($_POST['password'])) {
                $creds = array(
                    'user_login'    => trim(wp_unslash($_POST['username'])),
                    'user_password' => $_POST['password'],
                    'remember'      => false
                );
    
                $login = wp_signon($creds, is_ssl());
                
                if (is_wp_error($login)) {
                    Subscriptions_Messages::messages('danger', $login->get_error_message());
                } else {
                    if(get_user_meta($login->{'ID'}, 'suscription', true) === null){
                        // var_dump(get_user_meta($login->{'ID'}, 'suscription', true));
                        echo wp_send_json_error('member');
                        
                        if(null !== Subscriptions_Sessions::get_session($this->add_session)) {
                            Subscriptions_Sessions::destroy_session($this->add_session);
                        }
                        wp_die();
                    }
                   echo wp_send_json_success();
                   wp_die();                    
                }
            }
            wp_die();
        }
    }
    /**
     * Register ajax
     */
    public function register_vars()
    {
        $register = isset($_POST['register']) ? $_POST['register'] : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
        $password2 = isset($_POST['password2']) ? sanitize_text_field($_POST['password2']) : '';
        $subscriptor_type = isset($_POST['subscriptor_type']) ? $_POST['subscriptor_type']: '';
        $fields = [
            'register' => $register,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
            'password2' => $password2,
            'subscriptor_type' => $subscriptor_type
        ];

        return $this->subscriptions_localize_script('ajax_register_data', $fields);
    }
    /**
     * Ajax register response
     */
    public function register_ajax()
    {
        if (isset($_POST['register'])) {
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $email = sanitize_email($_POST['email']);
            $password = sanitize_text_field($_POST['password']);
            $password2 = sanitize_text_field($_POST['password2']);
            $subscriptor_type = $_POST['subscriptor_type'] === 'digital' ? get_option('subscription_digital_role') : get_option('default_sucription_role');

            if (!is_email($email)) {
                echo wp_send_json_error(__('Ingrese un email valido', 'subscriptions'));
                wp_die();
            }

            if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
                echo wp_send_json_error(__('All fields are required', 'subscriptions'));
                wp_die();
            }

            if (isset($password) && 6 > strlen($password)) {
                echo wp_send_json_error(__('Password length must be greater than 6 characters.', 'subscriptions'));
                wp_die();
            }

            if($password !== $password2){
                echo wp_send_json_error(__('Las contraseñas no coinciden.', 'subscriptions'));
                wp_die();
            }

            if (email_exists($email)) {
                echo wp_send_json_error(__('Email Already in use', 'subscriptions'));
                wp_die();
            }

            if(empty($subscriptor_type) || !isset($subscriptor_type)) {
                echo wp_send_json_error(__('Ocurrio un error con el tipo de usuario.', 'subscriptions'));
                wp_die();
            }

            $user_create = Subscriptions_Forms_Auth::registration($first_name, $last_name, $email, $password, $subscriptor_type);

            if ($user_create) {
                wp_set_current_user($user_create);
                wp_set_auth_cookie($user_create);
                Subscriptions_Emails::email_success_register($email);
                echo wp_send_json_success();
                wp_die();
            }
        }
    }
    /**
     * Address
     */
    public function add_address_vars()
    {
        $add_address = isset($_POST['add_address']) ? sanitize_text_field( $_POST['add_address'] ) : '';
        $state = isset($_POST['state']) ? sanitize_text_field( $_POST['state'] ) : '';
        $city = isset($_POST['city']) ? sanitize_text_field( $_POST['city'] ) : '';
        $address = isset($_POST['address']) ? sanitize_text_field( $_POST['address'] ) : '';
        $number = isset($_POST['number']) ? sanitize_text_field( $_POST['number'] ) : '';
        $floor = isset($_POST['floor']) ? sanitize_text_field( $_POST['floor'] ) : '';
        $apt = isset($_POST['apt']) ? sanitize_text_field( $_POST['apt'] ) : '';
        $zip = isset($_POST['zip']) ? sanitize_text_field( $_POST['zip'] ) : '';
        $bstreet = isset($_POSt['bstreet']) ? sanitize_text_field( $_POST['bstreet'] ) : '';
        $observations = isset($_POST['observations']) ? sanitize_text_field( $_POST['observations'] ) : '';
        
        $fields = [
            'add_address' => $add_address,
            'state' => $state,
            'city' => $city,
            'address' => $address,
            'number' => $number,
            'floor' => $floor,
            'apt' => $apt,
            'zip' => $zip,
            'bstreet' => $bstreet,
            'observations' => $observations
        ];

        return $this->subscriptions_localize_script('ajax_address', $fields);
    }

    public function add_address_ajax()
    {
        if (isset($_POST['add_address'])) {

            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if(!isset($_POST['state']) || empty($_POST['state']) || !isset($_POST['city']) || empty($_POST['city']) || !isset($_POST['address']) || !isset($_POST['number']) || !isset($_POST['zip'])){
                $error = new WP_Error( '001', __('There are empty field!') );
                wp_send_json_error($error);
                wp_die();

            } else {
                $address = [
                    'state' =>  $_POST['state'],
                    'city' => $_POST['city'],
                    'address' => $_POST['address'],
                    'number' => $_POST['number'],
                    'floor' => $_POST['floor'],
                    'apt' => $_POST['apt'],
                    'zip' => $_POST['zip'],
                    'bstreet' => $_POST['bstreet'],
                    'observations' => $_POST['observations'] ?  $_POST['observations'] : ''
                ];
                update_user_meta( wp_get_current_user()->ID, '_user_address', $address);
                wp_send_json_success();
                wp_die();
            }
            wp_die();
        }
    }
     /**
     * discount
     */
    public function discount_vars()
    {
        $add_discount = isset($_POST['add_discount']) ? $_POST['add_discount'] : '';
        $discount = isset($_POST['discount']) ? $_POST['discount'] : '';
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $id = isset($_POST['id']) ? $_POST['id'] : '';

        $fields = [
            'add_discount' => $add_discount,
            'discount' => $discount,
            'type' => $type,
            'name' => $name,
            'id' => $id
        ];

        return $this->subscriptions_localize_script('ajax_add_discount', $fields);
    }

    public function discount_ajax()
    {
        if (isset($_POST['add_discount'])) {
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if (isset($_POST['discount'])) {
                if (null !== Subscriptions_Sessions::get_session($this->add_session)) {
                    Subscriptions_Sessions::destroy_session($this->add_session);
                }

                $data = [
                    'donation_id' => sanitize_text_field($_POST['id']),
                    'donation_price' => sanitize_text_field($_POST['discount']),
                    'donation_name' => sanitize_text_field($_POST['name']),
                    'suscription_type' => sanitize_text_field($_POST['type'])
                ];

                Subscriptions_Sessions::set_session($this->add_session, $data);

                wp_send_json_success();

                wp_die();
            } else {
                wp_send_json_error( __('Error in discount!','subscriptions') );
                wp_die();
            }
        }
    }
    /**
     * contact
     */
    public function contact_vars()
    {
        $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $msg = isset($_POST['msg']) ? $_POST['msg'] : '';

        $fields = [
            'contact' => $contact,
            'name' => $name,
            'email' => $email,
            'msg' => $msg
        ];

        return $this->subscriptions_localize_script('ajax_contact', $fields);
    }
    public function contact_ajax()
    {
        if(isset($_POST['contact'])){
            $nonce = sanitize_text_field($_POST['_ajax_nonce']);

            if (!wp_verify_nonce($nonce, $this->nonce)) {
                die(__('So sorry, wp_nonce is broken!', 'subscriptions'));
            }

            if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['msg'])) {
                if (null !== Subscriptions_Sessions::get_session($this->add_session)) {
                    Subscriptions_Sessions::destroy_session($this->add_session);
                }
                $headers = [
                    'From: ' . get_bloginfo('name') . ' <' . $_POST['email'] . '>',
                    'Content-Type: text/html; charset=UTF-8'
                ];

                $body = __('Name and Lastname: ','subscriptions'). ' '.sanitize_text_field( $_POST['name'] ).'<br />';
                $body .= __('Email: ','subscriptions'). ' '.sanitize_text_field( $_POST['email'] ).'<br />';
                $body .= '<p>'.__('Message','subscriptions') .'<br />'.sanitize_text_field( $_POST['msg'] ).' </p>';

                $email = wp_mail(get_option('subscriptions_email_sender', get_bloginfo('admin_email')), __('Discount request in Support us','subscriptions'), $body, $headers);
                if($email) {
                    wp_send_json_success();
                    wp_die();
                } else {
                    wp_send_json_error(__('Error sending email','subscriptions') );
                    wp_die();
                }
            } else {
                wp_send_json_error(__('All field are required','subscriptions') );
                wp_die();
            }
        }
    }
}


$subscriptions_proccess = new Subscriptions_Proccess();

function subscriptions_proccess()
{
    return new Subscriptions_Proccess();
}

subscriptions_proccess();