<?php


class Subscriptions_Emails
{
    static public $sender;
    static public $home_url;
    static private $init = false;

    public static function init()
    {
        if (self::$init)
            return false;
        self::$init = true;
        /**
         * From emails
         */
        self::$sender = get_option('subscriptions_email_sender', get_bloginfo('admin_email')); //If options no exists, use admin email.
        self::$home_url = get_home_url();

        add_filter( 'wp_mail_content_type',[__CLASS__,'set_content_type'] );


    }
    /**
     * Headers email, with the filter "subscriptions_email_headers" you can add more headers for email, for example cc, bcc or Content-Type.
     */
    public static function email_headers()
    {
        $headers = [
            'From: ' . get_bloginfo('name') . ' <' . self::$sender . '>',
            'Content-Type: text/html; charset=UTF-8'
        ];

        if (has_filter('subscriptions_email_headers'))
            $headers = apply_filters('subscriptions_email_headers', $headers);

        return $headers;
    }
    /**
     * Email body
     */
    public static function smart_tags($content, $values = [])
    {
        $tags = [
            '{{site_name}}',
            '{{home_url}}',
            '{{email}}',
            '{{first_name}}',
            '{{last_name}}',
            '{{username}}',
            '{{subscription_name}}',
            '{{subscription_cost}}',
            '{{reference}}',
            '{{message}}',
            '{{password}}'
        ];

        $default_values = [
            'site_name' => get_bloginfo('name'),
            'home_url' => self::$home_url,
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'subscription_name' => '',
            'subscription_cost' => '',
            'reference' => '',
            'message' => '',
            'password' => ''
        ];
        $values = wp_parse_args($values, $default_values);

        /**
         * User data
         */
        if (!empty($values['email'])) {
            $user = get_user_by('email', $values['email']);
            $user_data = [
                'user_email' => $user->user_email,
                'first_name' => $user->user_firstname,
                'last_name' => $user->user_lastname,
                'username' => $user->user_login
            ];

            $values = array_merge($values, $user_data);
            $user_tags = array_keys($user_data);
            array_walk(
                $user_tags,
                function (&$value) {
                    $value = '{{' . trim($value, '{}') . '}}';
                }
            );
            $tags = array_merge($tags, $user_tags);
        }

        /**
         * Content
         */
        $content = str_replace($tags, array_values($values), $content);
        return $content;
    }

    /**
     * Register success email
     */
    public static function email_success_register($email)
    {
        $values = [
            'email' => $email
        ];
        
        $subject = get_option('subscriptions_email_register_subject', 'Hi {{first_name}} welcome to {{site_name}}');
        $content = get_option('subscriptions_email_register_body', 'Hi {{first_name}} {{last_name}} welcome to <a href="{{home_url}}" target="_blank">{{site_name}}');

        $subject = self::smart_tags($subject, $values);
        $content = self::smart_tags($content, $values);

        $headers = self::email_headers();
        return wp_mail($email, $subject, $content, $headers);
    }
    /**
     * Order Email
     */
    public static function email_order($status,$email,$suscripcion="")
    {
        $values = [
            'email' => $email,
            'subscription_name' => $suscripcion
        ];

        $email_content = SE()->get_status($status);

        $subject = $email_content->email_subject;
        $content = $email_content->email_body;

        $subject = self::smart_tags($subject, $values);
        $content = self::smart_tags($content, $values);

        $headers = self::email_headers();
        return wp_mail($email, $subject, $content, $headers);
    }
    /**
     * Customer email from edit and create membership admin
     */
    public static function membership_email($email,$password)
    {
        $values = [
            'email' => $email,
            'password' => $password
        ];

        $subject = get_option('subscriptions_membership_subject', 'Hola {{first_name}} creamos tu usuario en {{site_name}}');
        $content = get_option('subscriptions_membership_body', 'Hola {{first_name}} {{last_name}} bienvenid@ a <a href="{{home_url}}" target="_blank">{{site_name}}</a>. Creamos una cuenta para una membresía, en un momento te enviaremos los datos de tu membresía. Mientras tanto: <br /> Tu usuario es: {{email}} <br />Tu contraseña es: {{password}}. <br />Puedes cambiar tu contraseña en tu perfil.<br /> Estamos para ayudarte.');

        $subject = self::smart_tags($subject, $values);
        $content = self::smart_tags($content, $values);

        $headers = self::email_headers();
        return wp_mail($email, $subject, $content, $headers);
    }
    /**
     * Admin email
     */
    public static function admin_new_order_email($subscription)
    {
        $values = [
            'email' => get_option('subscriptions_email_sender',get_bloginfo('admin_email')),
            'subscription_name' => $subscription
        ];

        $subject = get_option('admin_order_email_subject', __('Hi administrator, you have a new member in {{site_name}}'));
        $content = get_option('admin_order_email_body', __('Hi administrator, you have a new member in your site {{site_name}}'));

        $subject = self::smart_tags($subject, $values);
        $content = self::smart_tags($content, $values);

        $headers = self::email_headers();
        return wp_mail(get_option('subscriptions_email_sender',get_bloginfo('admin_email')), $subject, $content, $headers);
    }

    public static function set_content_type()
    {
        return "text/html";
    }

}

Subscriptions_Emails::init();