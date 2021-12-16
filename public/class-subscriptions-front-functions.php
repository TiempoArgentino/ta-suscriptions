<?php

class Subscriptions_Front
{

    public function __construct()
    {
        add_filter('the_content', [$this, 'protected_content'], 1,2);
    }

    public function protected_content($content,$filter = '')
    {
        global $wp_query;
        if (!is_single()) {
            return $content;
        }

        $s_private = get_post_meta($wp_query->get_queried_object_id(), 'suscription_private', true);

        if ($s_private === 'on' && !is_user_logged_in()) {
            if(has_filter('protected_content')) {
                $show = apply_filters( 'protected_content', $filter );
                return $show;
            } else {
                $message = $this->show_message();
                $message .= '<a href="' . get_permalink(get_option('subscriptions_login_register_page')) . '" class="btn btn-primary">' . __('Go to login page', 'subscriptions') . '</a>';
                return $message;
            }
        } else {
            if(has_filter('user_logged_content')){
                $message = apply_filters('user_logged_content',$filter='');
                return $message;
            }
    
            return $content;
        }
    }

    public function show_message()
    {
        $message = get_option('protected_content_message', 'Hello, this content is private, you must log in or register to read it.');
        return $message;
    }
}

function SF()
{
    return new Subscriptions_Front();
}
