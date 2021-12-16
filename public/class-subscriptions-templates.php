<?php

/**
 * Docs:
 * https://developer.wordpress.org/reference/hooks/template_include/
 */ 

class Subscriptions_Template
{
    public function __construct()
    {
        add_filter('template_include', [$this, 'single_template'], 99);
        add_filter('template_include', [$this, 'taxonomy_template'], 99);
        add_filter('template_include', [$this, 'subscriptions_loop'], 99);
        add_filter('template_include', [$this, 'subscriptions_thankyou'],99);
        add_filter('template_include', [$this, 'subscriptions_login_form'], 99);
        add_filter('template_include', [$this, 'subscriptions_register_form'], 99);
        add_filter('template_include', [$this, 'subscriptions_lost_password_form'],99);
        add_filter('template_include', [$this, 'susucriptions_payment_page'], 99);
        add_filter('template_include', [$this, 'donations'], 99);
    }
    /**
     * You must create a folder called "subscriptions-theme" into your main theme and copy the php file to override then
     */
    public function suscription_load_template($filename = '')
    {
        if (!empty($filename)) {
            if (locate_template('subscriptions-theme/' . $filename)) {
                /**
                 * Folder in theme for subscriptions templates, this folder must be created into your theme.
                 */
                $template = locate_template('subscriptions-theme/' . $filename);
            } else {
                /**
                 * Default folder of templates
                 */
                $template = dirname(__FILE__) . '/partials/' . $filename;
            }
        }
        return $template;
    }

    public function single_template($template)
    {
        if (is_singular('subscriptions'))
            $template = $this->suscription_load_template('pages/suscription-post.php');
        return $template;
    }

    public function taxonomy_template($template)
    {
        if (is_tax('subscriptions_type'))
            $template = $this->suscription_load_template('pages/taxonomy-subscriptions_type.php');
        return $template;
    }

    public function subscriptions_loop($template)
    {
        if (is_page(get_option('subscriptions_loop_page')))
            $template = $this->suscription_load_template('pages/subscriptions-loop.php');
        return $template;
    }

    public function subscriptions_thankyou($template)
    {
        if (is_page(get_option('subscriptions_thankyou')))
            $template = $this->suscription_load_template('pages/subscriptions-thankyou.php');
        return $template;
    }

    public function subscriptions_login_form($template)
    {
        if(is_page(get_option('subscriptions_login_register_page')))
            $template = $this->suscription_load_template('auth/suscription-login.php');
        return $template;
    }

    public function subscriptions_register_form($template)
    {
        if(is_page(get_option('subscriptions_register_page')))
            $template = $this->suscription_load_template('auth/subscriptions-register.php');
        return $template;
    }

    public function subscriptions_lost_password_form($template)
    {
        if(is_page(get_option('subscriptions_lost_password_page')))
            $template = $this->suscription_load_template('auth/lost-password.php');
        return $template;
    }

    public function susucriptions_payment_page($template)
    {
        if (is_page(get_option('subscriptions_payment_page')))
            $template = $this->suscription_load_template('pages/subscriptions-payment-page.php');
        return $template;
    }

    public function donations($template)
    {
        if (is_page(get_option('donations')))
            $template = $this->suscription_load_template('pages/donations.php');
        return $template;
    }

}

$subscriptions_template = new Subscriptions_Template();
