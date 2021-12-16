<?php


class Subscriptions_Forms_Auth
{

    static private $init = false;

    static private $login_page;
    static private $profile_page;

    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;

        self::$login_page = get_option('subscriptions_login_register_page');
        self::$profile_page = get_option('subscriptions_profile');

        add_action('user_login_actions', [self::class, 'user_already_logged']);
        add_action('user_register_actions', [self::class, 'user_already_logged']);


        add_action('subscriptions_before_login_form', [self::class, 'show_login_error']);
        add_action('template_redirect', [self::class, 'subscriptions_login_proccess']);
        add_action('subscriptions_before_login_form', [self::class, 'login_unauthorized']);
        add_action('subscriptions_before_register_form', [self::class, 'registration_function']);

        add_action('login_form_lostpassword',  [self::class, 'redirect_to_lost_password']);
        add_action('login_form_lostpassword', [self::class, 'do_password_lost']);

        add_action('subscriptions_password_lost_errors', [self::class, 'password_errors']);

        add_action('subscriptions_before_login_form', [self::class, 'password_recived']);
    }

    public static function subscriptions_login_proccess()
    {
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $creds = array(
                'user_login'    => trim(wp_unslash($_POST['username'])),
                'user_password' => $_POST['password'],
                'remember'      => false
            );

            $login = wp_signon($creds, is_ssl());

            if (is_wp_error($login)) {
                wp_redirect(get_permalink(self::$login_page) . '?login=login_error');
                exit();
            }

            wp_redirect(esc_url_raw($_POST['redirect_to']));
            exit();
        }
    }
    public static function show_login_error()
    {
        if(isset($_GET['login']) && $_GET['login'] === 'login_error'){
            Subscriptions_Messages::messages('danger', __('Ocurrio un error, por favor revisa tus datos.', 'diplomaturas'));
        }
    }
    /**
     * Registration validation
     */
    public static function registration_validate($first_name, $last_name, $email, $password, $password2)
    {
        global $reg_errors;
        $reg_errors = new WP_Error();
        /**
         * Empty fields
         */
        if (isset($_POST['submit-register'])) {
            if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($password2)) {
                $reg_errors->add('field', __('All Fields are required.', 'subscriptions'));
            }
            /**
             * Password length
             */
            if (isset($password) && 6 > strlen($password)) {
                $reg_errors->add('password', __('Password length must be greater than 6 characters.', 'subscriptions'));
            }
            /**
             * Email valid
             */
            if (!is_email($email)) {
                $reg_errors->add('email_invalid', __('Email is not valid', 'subscriptions'));
            }
            /**
             * Email (or user) exists
             */
            if (email_exists($email)) {
                $reg_errors->add('email', __('Email Already in use', 'subscriptions'));
            }
            /**
             * password 2
             */
            if ($password !== $password2) {
                $reg_errors->add('password2', __('The passwords must be equals 2.', 'subscriptions'));
            }
        }
        /**
         * Show errors
         */
        if (sizeof($reg_errors->get_error_messages()) > 0) {
            foreach ($reg_errors->get_error_messages() as $error) {
                Subscriptions_Messages::messages('danger', $error);
            }
            return false;
        } else {
            return true;
        }
    }
    /**
     * Registration
     */
    public static function registration($first_name, $last_name, $email, $password, $subscriptor_type)
    {
        global $reg_errors;
        $reg_errors = new WP_Error();

        if (count($reg_errors->get_error_messages()) < 1) {
            /** 
             * If there are no errors then add the user
             */
            $userdata = [
                'user_login' => $email,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_pass' => $password,
                'user_registered' => date('Y-m-d H:i:s'),
                'show_admin_bar_front' => false,
                'role' => $subscriptor_type
            ];
            $user = wp_insert_user($userdata);
            return $user; //Returns the ID of the created user
        }
    }
    /**
     * Registration function
     */
    public static function registration_function()
    {
        if (isset($_POST['submit-register'])) {
           
            /**
             * Sanitize Fields
             */
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $email = sanitize_email($_POST['email']);
            $password = esc_attr($_POST['password']);
            $password2 = esc_attr($_POST['password2']);
            $subscriptor_type = $_POST['subscriptor_type'] === 'digital' ? get_option('subscription_digital_role') : get_option('default_sucription_role');
            /**
             * Validation
             */
            $validate = self::registration_validate(
                $first_name,
                $last_name,
                $email,
                $password,
                $password2
            );
            if (!$validate) {
                return;
            }
            /**
             * Create user
             */
            $user_create = self::registration(
                $first_name,
                $last_name,
                $email,
                $password,
                $subscriptor_type
            );
            /**
             * Email for success subscriptions
             */
            if ($user_create) {
                wp_set_current_user($user_create);
                wp_set_auth_cookie($user_create);
                if (Subscriptions_Emails::email_success_register($email)) {
                    wp_redirect(esc_url_raw($_POST['register_redirect']));
                    exit();
                }
            }
        }
    }
    /**
     * User login error redirect
     */
    public static function user_logged()
    {
        if (!is_user_logged_in()) {
            wp_redirect(get_permalink(self::$login_page) . '?login=unauthorized');
            exit();
        }
    }
    /**
     * Redirect
     */
    public static function user_already_logged()
    {
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit();
        }
    }
    /**
     * Unauthorized message
     */
    public static function login_unauthorized()
    {
        if (isset($_GET['login']) && $_GET['login'] === 'unauthorized') {
            Subscriptions_Messages::messages('danger', __('You must be logged to access to this section', 'subscriptions'));
        }
    }
    /**
     * Lost password
     */
    public static function redirect_to_lost_password()
    {
        if ('GET' == $_SERVER['REQUEST_METHOD']) {
            wp_redirect(get_permalink(get_option('subscriptions_lost_password_page')));
            exit;
        }
    }
    /**
     * Lost password function
     */
    public static function do_password_lost()
    {
        if (isset($_POST['send-password'])) {
            $errors = retrieve_password();
            if (is_wp_error($errors)) {
                $redirect_url = get_permalink(get_option('subscriptions_lost_password_page'));
                $redirect_url = add_query_arg('errors', join(',', $errors->get_error_codes()), $redirect_url);
            } else {
                $redirect_url = get_permalink(get_option('subscriptions_login_register_page'));
                $redirect_url = add_query_arg('checkemail', 'confirm', $redirect_url);
            }

            wp_redirect($redirect_url);
            exit;
        }
    }
    /**
     * Errors
     */
    public static function password_errors()
    {
        $attributes['errors'] = array();
        if (isset($_REQUEST['errors'])) {
            $error_codes = explode(',', $_REQUEST['errors']);

            foreach ($error_codes as $error_code) {

                if ($error_code === 'empty_username') {
                    $attributes['errors'][] = Subscriptions_Messages::messages('danger', __('You must provide a email', 'subscriptions'));
                }

                if ($error_code === 'invalid_email') {
                    $attributes['errors'][] = Subscriptions_Messages::messages('danger', __('This email is not in our database', 'subscriptions'));
                }
            }
        }
        return $attributes['errors'];
    }
    /**
     * Password lost ok recived
     */
    public static function password_recived()
    {
        if (isset($_REQUEST['checkemail']) && $_REQUEST['checkemail'] == 'confirm') {
            Subscriptions_Messages::messages('success', __('Check your email for a link to reset your password.', 'subscriptions'));
        }
    }
}

Subscriptions_Forms_Auth::init();
