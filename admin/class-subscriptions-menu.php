<?php

/**
 * This is simple: https://developer.wordpress.org/plugins/
 */

class Subscriptions_Menu
{
    private $subscriptions_options_options;

    public function __construct()
    {

        add_action('admin_menu', [$this, 'main_menu']);
        add_action('admin_menu', [$this, 'subscriptions_menu']);
        //add_action('admin_menu', [$this, 'subscriptions_types_menu']);
        //add_action('admin_menu', [$this, 'donations_menu']);
        add_action('admin_menu', [$this, 'memberships_menu']);
        add_action('admin_menu', [$this, 'subscriptions_emails']);
        add_action('admin_menu', [$this, 'subscriptions_payment']);
        add_action('admin_menu', [$this, 'subscriptions_options_add_plugin_page']);
        add_action('admin_init', [$this, 'subscriptions_options_page_init']);

        add_action('admin_menu', [$this, 'user_subscriptions_menu']);
    }


    /**
     * Admin main menu item
     */
    public function main_menu()
    {
        add_menu_page(
            __('TAR Subscriptions', 'subscriptions'),
            __('TAR Subscriptions', 'subscriptions'),
            'manage_options',
            'tar_admin',
            [$this, 'panel_view'],
            'dashicons-nametag',
            20
        );
    }
    /**
     * Admin subitems menu
     */

    /**
     * Subscriptions
     */
    public function subscriptions_menu()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Subscriptions', 'subscriptions'),
            __('Subscriptions', 'subscriptions'),
            'edit_posts',
            'edit.php?post_type=subscriptions'
        );
    }
    /**
     * Subscriptions types
     */
    public function subscriptions_types_menu()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Subscriptions Types', 'subscriptions'),
            __('Subscriptions Types', 'subscriptions'),
            'edit_posts',
            'edit-tags.php?taxonomy=subscriptions_type&post_type=subscriptions'
        );
    }
    /**
     * Donations
     */
    public function donations_menu()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Donations', 'subscriptions'),
            __('Donations', 'subscriptions'),
            'edit_posts',
            'edit.php?post_type=donations'
        );
    }
    /**
     * Options
     */
    public function subscriptions_options_add_plugin_page()
    {
        add_submenu_page(
            'tar_admin',
            __('Subscriptions Options', 'subscriptions'),
            __('Settings', 'subscriptions'),
            'manage_options',
            'subscriptions-options',
            [$this, 'subscriptions_options_create_admin_page'],
            10
        );
    }
    /**
     * Memberships
     */
    public function memberships_menu()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Memberships', 'subscriptions'),
            __('Memberships', 'subscriptions'),
            'edit_posts',
            'edit.php?post_type=memberships'
        );
    }
    /**
     * Payments
     */
    public function subscriptions_payment()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Payments', 'subscriptions'),
            __('Payment Settings', 'subscriptions'),
            'manage_options',
            'tar_pagos',
            [$this, 'payments_view'],
            20
        );
    }
    /**
     * Emails
     */
    public function subscriptions_emails()
    {
        add_submenu_page(
            'tar_admin',
            __('TAR Emails and Status', 'subscriptions'),
            __('Emails and Status', 'subscriptions'),
            'manage_options',
            'tar_emails',
            [$this, 'emails_view'],
            30
        );
    }
    /***
     * Users
     */
    public function user_subscriptions_menu()
    {
        add_submenu_page(
            'tar_admin',
            __('User Data', 'user-panel'),
            __('User Data', 'user-panel'),
            'manage_options',
            'user-panel-subscriptions',
            [$this, 'user_panel_subscriptions']
        );
    }

    /**
     * Menus Views
     */
    /**
     * Panel
     */
    public function panel_view()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/subscriptions-admin-panel-display.php';
    }
    /**
     * Options
     */
    public function subscriptions_options_create_admin_page()
    {
        $this->subscriptions_options_options = get_option('subscriptions_options_option_name');
?>

        <div class="wrap">
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('subscriptions_options_option_group');
                do_settings_sections('subscriptions-options-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php }

    public function subscriptions_options_page_init()
    {
        register_setting(
            'subscriptions_options_option_group', // option_group
            'subscriptions_options_option_name', // option_name
            [$this, 'subscriptions_options_sanitize'] // sanitize_callback
        );

        add_settings_section(
            'subscriptions_options_setting_section', // id
            __('Settings', 'subscriptions'), // title
            [$this, 'subscriptions_options_section_info'], // callback
            'subscriptions-options-admin' // page
        );

        add_settings_field(
            'load_bootstrap', // id
            __('Load Bootstrap CSS 4.6? (only css file)', 'subscriptions'), // title
            array($this, 'load_bootstrap_callback'), // callback
            'subscriptions-options-admin', // page
            'subscriptions_options_setting_section' // section
        );

        add_settings_field(
            'load_bootstrap_js', // id
            __('Load Bootstrap JS 4.6? (only js file)', 'subscriptions'), // title
            array($this, 'load_bootstrap_js_callback'), // callback
            'subscriptions-options-admin', // page
            'subscriptions_options_setting_section' // section
        );

        add_settings_field(
            'use_the_private_content_funtion_0', // id
            __('Use the private content function?', 'subscriptions'), // title
            [$this, 'use_the_private_content_funtion_0_callback'], // callback
            'subscriptions-options-admin', // page
            'subscriptions_options_setting_section' // section
        );

        add_settings_field(
            'protected_post_type_1', // id
            __('Protected post type', 'subscriptions'), // title
            array($this, 'protected_post_type_1_callback'), // callback
            'subscriptions-options-admin', // page
            'subscriptions_options_setting_section' // section
        );

        add_settings_field(
            'protected_message_1', // id
            __('Protected post message', 'subscriptions'), // title
            array($this, 'protected_message_1_callback'), // callback
            'subscriptions-options-admin', // page
            'subscriptions_options_setting_section' // section
        );

    }

    public function subscriptions_options_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['use_the_private_content_funtion_0'])) {
            $sanitary_values['use_the_private_content_funtion_0'] = $input['use_the_private_content_funtion_0'];
        }

        if (isset($input['protected_post_type_1'])) {
            $sanitary_values['protected_post_type_1'] = $input['protected_post_type_1'];
        }

        if (isset($input['load_bootstrap'])) {
            $sanitary_values['load_bootstrap'] = $input['load_bootstrap'];
        }

        if (isset($input['load_bootstrap_js'])) {
            $sanitary_values['load_bootstrap_js'] = $input['load_bootstrap_js'];
        }

        if(isset($_POST['protected_content_message'])){
            update_option('protected_content_message',$_POST['protected_content_message'],true);
        }

        return $sanitary_values;
    }
    /**
     * Some settings
     */
    public function subscriptions_options_section_info()
    {
        echo '<h3>' . __('General Settings', 'subscriptions') . '</h3>
        <p>' . __('Some options, very usefuls', 'subscriptions') . '</p>';
    }
    /**
     * Get all post types
     */
    public function post_types_get()
    {
        $args = array(
            'public'   => true,
        );
        $output = 'names';
        $operator = 'and';
        $post_types = get_post_types($args, $output, $operator);
        return $post_types;
    }
    /**
     * Options fields
     */
    public function load_bootstrap_callback()
    {
        printf(
            '<input type="checkbox" name="subscriptions_options_option_name[load_bootstrap]" id="load_bootstrap" value="load_bootstrap" %s>',
            (isset($this->subscriptions_options_options['load_bootstrap']) && $this->subscriptions_options_options['load_bootstrap'] === 'load_bootstrap') ? 'checked' : ''
        );
        echo '<p class="tip">' . __('<a href="https://getbootstrap.com/docs/4.5/getting-started/introduction/" target="_blank">More info</a>', 'subscriptions') . '</div>';
    }

    public function load_bootstrap_js_callback()
    {
        printf(
            '<input type="checkbox" name="subscriptions_options_option_name[load_bootstrap_js]" id="load_bootstrap_js" value="load_bootstrap_js" %s>',
            (isset($this->subscriptions_options_options['load_bootstrap_js']) && $this->subscriptions_options_options['load_bootstrap_js'] === 'load_bootstrap_js') ? 'checked' : ''
        );
        echo '<p class="tip">' . __('<a href="https://getbootstrap.com/docs/4.5/getting-started/introduction/" target="_blank">More info</a>', 'subscriptions') . '</div>';
    }

    public function use_the_private_content_funtion_0_callback()
    {
        printf(
            '<input type="checkbox" name="subscriptions_options_option_name[use_the_private_content_funtion_0]" id="use_the_private_content_funtion_0" value="use_the_private_content_funtion_0" %s>',
            (isset($this->subscriptions_options_options['use_the_private_content_funtion_0']) && $this->subscriptions_options_options['use_the_private_content_funtion_0'] === 'use_the_private_content_funtion_0') ? 'checked' : ''
        );
    }

    public function protected_post_type_1_callback()
    {
        $default_post_types = ['attachment', 'revision', 'nav_menu_item', 'memberships', 'subscriptions'];
        /**
         * Show the post types
         */
        foreach ($this->post_types_get() as $pt) {
            if (!in_array($pt, $default_post_types)) {
                printf(
                    '<label style="text-transform:uppercase; margin-right:5px">' . $pt . '
                    <input type="checkbox" name="subscriptions_options_option_name[protected_post_type_1][]" id="protected_post_type_1" value="' . $pt . '" %s></label><br />',
                    (isset($this->subscriptions_options_options['protected_post_type_1']) && in_array($pt, $this->subscriptions_options_options['protected_post_type_1'])) ? 'checked' : ''
                );
            }
        }
        echo '<p class="tip">' . __('Post types with options for privacy.', 'subscriptions') . '</p>';
    }

    public function protected_message_1_callback()
    {
        printf('<textarea name="protected_content_message" class="large-text">%s</textarea>',(isset($this->subscriptions_options_options['protected_message_1'])) ? $this->subscriptions_options_options['protected_message_1'] : get_option('protected_content_message','Hello, this content is private, you must log in or register to read it.'));
    }

    /**
     * Emails
     */
    public function emails_view()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/subscriptions-admin-emails-display.php';
    }
    /**
     * Payments
     */
    public function payments_view()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/subscriptions-admin-payments-display.php';
    }

    public function user_panel_subscriptions()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/subscriptions-admin-users-display.php';
    }
}

$subscriptions_menu = new Subscriptions_Menu();
