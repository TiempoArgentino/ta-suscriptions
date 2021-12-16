<?php

class Subscriptions_Options
{

    public function __construct()
    {
        add_action('admin_init', [$this, 'subscriptions_permalink_init']);
        add_action('admin_init', [$this, 'change_default_role_init']);
        add_action('admin_init', [$this, 'currency_memberships_options_init']);
        add_action('admin_init', [$this, 'pages_options_init']);
        add_action('admin_init', [$this, 'email_options_init']);
        add_action('admin_init', [$this, 'subscriptions_emails_defaults_options']);

        add_action('admin_init', [$this, 'create_digital_role']);

        $this->subscriptions_permalink_save();
        $this->subscriptions_change_role_save();
        $this->subscriptions_currency_memberships_options_save();
        $this->subscriptions_loop_page_options_save();
        $this->subscriptions_emails_save();
        $this->subscriptions_emails_register_save();

        add_action('rest_api_init', [$this, 'sync_role_user_endpoint']);
        add_action('admin_enqueue_scripts', [$this, 'add_js_admin']);

        //add_action('admin_init', [$this,'set_sus']);
    }

    public function add_js_admin()
    {
        wp_enqueue_script('admin-subscriptions-js-functions', plugin_dir_url(__FILE__) . '/js/functions.js', null, SUSCRIPTIONS_VERSION, true);
        wp_localize_script('admin-subscriptions-js-functions', 'var_func', [
            'syncPost' => rest_url('subscriptions/v1/sync-role-users'),
            'getUsers' => rest_url('subscriptions/v1/get-users')
        ]);
    }
    /**
     * Permalink options
     */
    public function subscriptions_permalink_init()
    {
        register_setting('permalink', 'subscriptions_permalink');

        add_settings_section(
            'subscriptions_permalinks_setting_section',
            __('Subscriptions and Donations permalinks options', 'subscriptions'),
            [$this, 'subscriptions_permalink'],
            'permalink'
        );

        add_settings_field(
            'subscriptions_permalinks_taxonomy_slug',
            __('Subscriptions', 'subscriptions'),
            [$this, 'subscriptions_post_type_input'],
            'permalink'
        );

        add_settings_field(
            'subscriptions_permalinks_taxonomy_slug',
            __('Subscriptions Type', 'subscriptions'),
            [$this, 'subscriptions_taxonomy_input'],
            'permalink'
        );
    }
    /**
     * Permalinks Input taxonomy
     */
    public function subscriptions_taxonomy_input()
    {
        return '<input name="suscription_taxonomy_slug" type="text" class="regular-text code" value="' . get_option('suscription_taxonomy_slug', 'subscriptions_type') . '" placeholder="' . get_option('suscription_taxonomy_slug', 'subscriptions_type') . '" />';
    }
    /**
     * Permalinks Input posts
     */
    public function subscriptions_post_type_input()
    {
        return '<input name="suscription_post_type_slug" type="text" class="regular-text code" value="' . get_option('suscription_post_type_slug', 'suscription') . '" placeholder="' . get_option('suscription_post_type_slug', 'suscription') . '" />';
    }

    /**
     * Permalinks UI
     */
    public function subscriptions_permalink()
    {
        echo '<p>' . __('This option is used to change or translate the slugs of our subscriptions and donations, only names, not the structure.', 'subscriptions') . '</p>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Subscriptions Base', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_post_type_input() . '</td>
                </tr>
                <tr>
                    <th><label>' . __('Subscriptions Type Base', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_taxonomy_input() . '</td>
                </tr>
            </tbody>
        </table>';
    }
    /**
     * Permalinks Save
     */
    public function subscriptions_permalink_save()
    {
        if (!is_admin()) {
            return;
        }
        if (isset($_POST['suscription_taxonomy_slug'], $_POST['suscription_post_type_slug'])) {
            if (isset($_POST['suscription_taxonomy_slug']))
                update_option('suscription_taxonomy_slug', sanitize_title($_POST['suscription_taxonomy_slug']));

            if (isset($_POST['suscription_post_type_slug']))
                update_option('suscription_post_type_slug', sanitize_title($_POST['suscription_post_type_slug']));
        }
    }
    /**
     * Extra options settings
     */
    /* --------- */
    /**
     * Change default suscriber role
     */
    public function change_default_role_init()
    {
        register_setting('subscriptions_role', 'subscriptions_change_role');

        add_settings_section(
            'subscriptions_options_role_section', // id
            __('Default suscriptors role', 'subscriptions'), // title
            [$this, 'subscriptions_change_role'], // callback
            'subscriptions-options-admin' // page
        );

        add_settings_field(
            'change_role', // id
            __('Change default role', 'subscriptions'), // title
            [$this, 'select_subscriptions_role'], // callback
            'subscriptions_options_role_section' // section
        );

        add_settings_field(
            'change_digital_role',
            __('Socio digital rol', 'subscriptions'),
            [$this, 'select_digital_role'],
            'subscriptions_options_role_section'
        );
    }

    public function all_roles()
    {
        $default_roles = get_editable_roles();
        foreach ($default_roles as $role => $details) {
            $rol['role'] = esc_attr($role);
            $rol['name'] = translate_user_role($details['name']);
            $roles[] = $rol;
        }
        return $roles;
    }

    public function select_subscriptions_role()
    {
        $role_default = get_option('default_sucription_role');
        $select = '<select name="role_sus">';
        foreach ($this->all_roles() as $r) {
            if ($r['role'] !== 'administrator')
                $select .= '<option value="' . $r['role'] . '" ' . selected($role_default, $r['role'], false) . '>' . $r['name'] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function select_digital_role()
    {
        $role_digital = get_option('subscription_digital_role');

        if (!$role_digital) {
            return '<input type="submit" class="button button-primary" name="create_role" value="Crear rol Soci@ Digital" />';
        }

        $select = '<select name="role_digital">';
        $select .= '<option value=""> -- seleccionar -- </option>';
        foreach ($this->all_roles() as $r) {
            if ($r['role'] !== 'administrator')
                $select .= '<option value="' . $r['role'] . '" ' . selected($role_digital, $r['role'], false) . '>' . $r['name'] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function create_digital_role()
    {
        if (isset($_POST['create_role'])) {
            add_role('digital', 'Soci@ Digital', array('read' => true, 'edit_posts' => false, 'delete_posts' => false));
            update_option('subscription_digital_role', 'digital');
        }
    }
    /** sync users roles */
    public function button_sync()
    {
        if (get_option('subscription_digital_role')) {
            return '<span id="loading-spinner" style="display:none"><img src="'.plugin_dir_url(__FILE__).'/img/loading.gif'.'" /></span><span class="button button-regular" data-users="'.count_users()['total_users'].'" id="sync-role-user">Sincronizar Roles Usuarios</span>';
        }
    }
    public function sync_role_user(WP_REST_Request $request)
    {
        if($request->get_param('id')) {
            $subscription = get_user_meta($request->get_param('id'),'suscription',true);
            $subscription_type = get_post_meta($subscription,'_is_type',true);
            if($subscription_type === 'digital') {
                $user = new WP_User($request->get_param('id'));
                $user->set_role(get_option('subscription_digital_role'));
                wp_send_json_success();
            }
            wp_send_json_success();
        }
    }

    public function get_users()
    {    
        $args = [
            'fields' => [
                'ID',
            ],
            'role__not_in' => ['administrator','shop_manager','editor','author','contributor','translator','ta_fotografo','ta_ads','ta_redactor','ta_socios_manager','ta_talleres'],
            'meta_key' => 'suscription',
            'meta_compare' => 'EXISTS'
        ];
        /**
         * Get Users By Roles
         */
        $users = get_users($args);
        wp_send_json($users);
    }

    public function sync_role_user_endpoint()
    {
        register_rest_route('subscriptions/v1', '/sync-role-users', array(
            'methods' => 'POST',
            'callback' => [$this, 'sync_role_user'],
            'permission_callback' => '__return_true',
        ));

        register_rest_route('subscriptions/v1', '/get-users', [
            'methods' => 'GET',
            'callback' => [$this, 'get_users'],
            'permission_callback' => '__return_true'
        ]);
    }

    

    /** sync users roles */
    public function subscriptions_change_role()
    {
        echo '<p class="warning">' . __('By default users are subscribers, you can change this here, note that you may have errors with old users.', 'subscriptions') . '</p>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Cambiar / Crear Rol Suscriptor', 'subscriptions') . '</label></th>
                    <td>' . $this->select_subscriptions_role() . '</td>
                </tr>
                <tr>
                    <th><label>' . __('Cambiar / Crear Rol Soci@ Digital', 'subscriptions') . '</label></th>
                    <td>' . $this->select_digital_role() . '</td>
                </tr>';
        if (get_option('subscription_digital_role')) {
            echo ' <tr>
                    <th><label>' . __('Sincronizar Usuarios', 'subscriptions') . '</label></th>
                    <td>' . $this->button_sync() . '</td>
                </tr>';
        }
        echo   '</tbody>
        </table>';
    }
    /**
     * Role save
     */
    public function subscriptions_change_role_save()
    {
        if (isset($_POST['role_sus']))
            update_option('default_sucription_role', sanitize_text_field($_POST['role_sus']));

        if (isset($_POST['role_digital']))
            update_option('subscription_digital_role', sanitize_text_field($_POST['role_digital']));
    }
    /**
     * Currency and memberships options
     */
    public function currency_memberships_options_init()
    {
        register_setting('subscriptions_m_c_options', 'subscriptions_currency_memberships_options');

        add_settings_section(
            'subscriptions_currency_section', // id
            __('Currency and Memberships options', 'subscriptions'), // title
            [$this, 'subscriptions_currency_memberships_options'], // callback
            'subscriptions-options-admin' // page
        );

        add_settings_field(
            'change_currency_symbol', // id
            __('Currency Symbol', 'subscriptions'), // title
            [$this, 'currency_symbol_input'], // callback
            'subscriptions_currency_section' // section
        );

        add_settings_field(
            'change_membership_prefix', // id
            __('Membership order prefix', 'subscriptions'), // title
            [$this, 'membership_prefix_order_input'], // callback
            'subscriptions_currency_section' // section
        );
    }
    /**
     * Fields
     */
    public function currency_symbol_input()
    {
        $symbol = get_option('subscriptions_currency_symbol', 'ARS');
        return '<input type="text" name="suscription_currency_symbol" value="' . $symbol . '" style="width: 60px;" />';
    }

    public function membership_prefix_order_input()
    {
        $prefix = get_option('member_sku_prefix', 'TA-');
        return '<input type="text" name="sucription_membership_prefix" maxlength="5" value="' . $prefix . '" style="width: 60px;" />';
    }
    /**
     * Callback currency symbols
     */
    public function subscriptions_currency_memberships_options()
    {
        echo '<p class="warning">' . __('Currency symbol and memberships order prefix', 'subscriptions') . '</p>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Currency Symbol', 'subscriptions') . '</label></th>
                    <td>' . $this->currency_symbol_input() . '<p class="tip">' . __('ISO 4217 list: ', 'subscriptions') . ' <a href="https://es.wikipedia.org/wiki/ISO_4217" target="_blank">Wikipedia</a></p></td>
                </tr>
                <tr>
                    <th><label>' . __('Membership order prefix', 'subscriptions') . '</label></th>
                    <td>' . $this->membership_prefix_order_input() . '<p class="tip">' . __('Max length: 5 chars', 'subscriptions') . '</p></td>
                </tr>
            </tbody>
        </table>';
    }
    /**
     * Save currency symbols
     */
    public function subscriptions_currency_memberships_options_save()
    {
        if (isset($_POST['suscription_currency_symbol']))
            update_option('subscriptions_currency_symbol', sanitize_text_field($_POST['suscription_currency_symbol']));

        if (isset($_POST['sucription_membership_prefix']))
            update_option('member_sku_prefix', sanitize_text_field($_POST['sucription_membership_prefix']));
    }
    /**
     * Page options
     */
    public function pages_options_init()
    {
        register_setting('subscriptions_pages_options', 'subscriptions_pages_slug_options');

        add_settings_section(
            'subscriptions_pages_section', // id
            __('Default pages', 'subscriptions'), // title
            [$this, 'subscriptions_pages_slug_options'], // callback
            'subscriptions-options-admin' // page
        );
    }

    public function get_pages()
    {
        $args = [
            'post_type' => 'page',
            'status'    => 'publish',
            'numberposts' => -1
        ];
        $pages = get_posts($args);

        return $pages;
    }

    public function subscriptions_loop_page_input()
    {
        $page_slug = get_option('subscriptions_loop_page');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_loop_page">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_thankyou_input()
    {
        $page_slug = get_option('subscriptions_thankyou');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_thankyou">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_login_register_page_input()
    {
        $page_slug = get_option('subscriptions_login_register_page');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_login_register_page">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_register_page_input()
    {
        $page_slug = get_option('subscriptions_register_page');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_register_page">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_profile_page_input()
    {
        $page_slug = get_option('subscriptions_profile');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_profile_page_input">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_payment_input()
    {
        $page_slug = get_option('subscriptions_payment_page');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_payment_page">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function subscriptions_terms_input()
    {
        $page_slug = get_option('subscriptions_terms_page');
        $pages = $this->get_pages();

        $select = '<select name="subscriptions_terms_page">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }

    public function donations_input()
    {
        $page_slug = get_option('donations');
        $pages = $this->get_pages();

        $select = '<select name="donations">';
        $select .= '<option value=""> -- select a page -- </option>';
        foreach ($pages as $p) {
            $select .= '<option value="' . $p->ID . '" ' . selected($page_slug, $p->ID, false) . '>' . $p->post_title . ' </option>';
        }
        $select .= '</select>';
        return $select;
    }


    public function subscriptions_pages_slug_options()
    {
        echo '<p class="warning">' . __('Define these pages so that Subscriptions know where to send their users to become members.', 'subscriptions') . '</p>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Loop page', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_loop_page_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/subscriptions/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Thank you page', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_thankyou_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/thank-you/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Login', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_login_register_page_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/sign-in/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Register page', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_register_page_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/sign-up/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('User Profile', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_profile_page_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/my-profile/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Payment Page', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_payment_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/payment-page/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Terms and Coditions', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_terms_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/terms-and-conditions/') . '</p></td>
                </tr>
                <tr>
                    <th><label>' . __('Donations', 'subscriptions') . '</label></th>
                    <td>' . $this->donations_input() . '<p class="tip">' . __('URL default: ', 'subscriptions') . ' ' . home_url('/donations/') . '</p></td>
                </tr>
            </tbody>
        </table>';
    }

    public function subscriptions_loop_page_options_save()
    {
        if (isset($_POST['subscriptions_loop_page']))
            update_option('subscriptions_loop_page', sanitize_text_field(sanitize_title($_POST['subscriptions_loop_page'])));

        if (isset($_POST['subscriptions_thankyou']))
            update_option('subscriptions_thankyou', sanitize_text_field(sanitize_title($_POST['subscriptions_thankyou'])));

        if (isset($_POST['subscriptions_login_register_page']))
            update_option('subscriptions_login_register_page', sanitize_text_field(sanitize_title($_POST['subscriptions_login_register_page'])));

        if (isset($_POST['subscriptions_register_page']))
            update_option('subscriptions_register_page', sanitize_text_field(sanitize_title($_POST['subscriptions_register_page'])));

        if (isset($_POST['subscriptions_profile_page_input']))
            update_option('subscriptions_profile', sanitize_text_field(sanitize_title($_POST['subscriptions_profile_page_input'])));

        if (isset($_POST['subscriptions_payment_page']))
            update_option('subscriptions_payment_page', sanitize_text_field(sanitize_title($_POST['subscriptions_payment_page'])));

        if (isset($_POST['subscriptions_terms_page']))
            update_option('subscriptions_terms_page', sanitize_text_field(sanitize_title($_POST['subscriptions_terms_page'])));

        if (isset($_POST['donations']))
            update_option('donations', sanitize_text_field(sanitize_title($_POST['donations'])));
    }
    /**
     * Emails
     */
    public function email_options_init()
    {
        register_setting('subscriptions_emails_options', 'subscriptions_emails_settings_options');

        add_settings_section(
            'subscriptions_emails_section', // id
            __('Email Settings', 'subscriptions'), // title
            [$this, 'subscriptions_emails_options'], // callback
            'subscriptions-options-admin' // page
        );

        add_settings_field(
            'subscriptions_email_from', // id
            __('Default email from', 'subscriptions'), // title
            [$this, 'subscriptions_email_from_input'], // callback
            'subscriptions_emails_section' // section
        );
    }

    public function subscriptions_email_from_input()
    {
        $sender = get_option('subscriptions_email_sender', get_bloginfo('admin_email'));

        return '<input type="email" name="subscriptions_email_sender" class="regular-text" value="' . $sender . '" />';
    }

    public function subscriptions_emails_options()
    {
        echo '<p class="warning">' . __('Add or update email sender.', 'subscriptions') . '</p>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Default email Sender', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_email_from_input() . '</p></td>
                </tr>
            </tbody>
        </table>';
    }

    public function subscriptions_emails_save()
    {
        if (isset($_POST['subscriptions_email_sender']))
            update_option('subscriptions_email_sender', sanitize_email($_POST['subscriptions_email_sender']));
    }
    /**
     * Emails defaults
     */
    public function subscriptions_emails_defaults_options()
    {
        register_setting('subscriptions_emails_defaults_options', 'subscriptions_emails_defaults_settings_options');

        add_settings_section(
            'subscriptions_emails_defaults_options', // id
            __('Emails defaults', 'subscriptions'), // title
            [$this, 'subscriptions_emails_register_options'], // callback
            'subscriptions-options-admin' // page
        );

        add_settings_field(
            'subscriptions_email_register_subject', // id
            __('Email new user subject', 'subscriptions'), // title
            [$this, 'subscriptions_email_register_subject_input'], // callback
            'subscriptions_emails_defaults_options' // section
        );

        add_settings_field(
            'subscriptions_email_register_body', // id
            __('Email new user body', 'subscriptions'), // title
            [$this, 'subscriptions_email_register_body_input'], // callback
            'subscriptions_emails_defaults_options' // section
        );

        add_settings_field(
            'subscriptions_email_admin_subject', // id
            __('Email new user subject', 'subscriptions'), // title
            [$this, 'subscriptions_email_admin_subject_input'], // callback
            'subscriptions_emails_defaults_options' // section
        );

        add_settings_field(
            'subscriptions_email_admin_body', // id
            __('Email new user body', 'subscriptions'), // title
            [$this, 'subscriptions_email_admin_body_input'], // callback
            'subscriptions_emails_defaults_options' // section
        );
    }

    public function subscriptions_email_register_subject_input()
    {
        $email_register = get_option('subscriptions_email_register_subject', 'Hi {{first_name}} welcome to {{site_name}}');
        return '<input type="text" name="subscriptions_email_register_subject" class="regular-text" value="' . $email_register . '" />';
    }

    public function subscriptions_email_register_body_input()
    {
        $email_register_body = get_option('subscriptions_email_register_body', 'Hi {{first_name}} {{last_name}} welcome to <a href="{{home_url}}" target="_blank">{{site_name}}</a>');
        return '<textarea name="subscriptions_email_register_body" class="large-text">' . $email_register_body . '</textarea>';
    }

    public function subscriptions_email_admin_subject_input()
    {
        $email_admin = get_option('admin_order_email_subject', __('Hi administrator, you have a new member in {{site_name}}'));
        return '<input type="text" name="admin_order_email_subject" class="regular-text" value="' . $email_admin  . '" />';
    }

    public function subscriptions_email_admin_body_input()
    {
        $email_admin = get_option('admin_order_email_body', __('Hi administrator, you have a new member in your site {{site_name}}'));
        return '<textarea name="admin_order_email_body" class="large-text">' . $email_admin  . '</textarea>';
    }

    public function subscriptions_membership_subject()
    {
        $subjec_membership = get_option('subscriptions_membership_subject', 'Hola {{first_name}} creamos tu usuario en {{site_name}}');
        return '<input type="text" name="subscriptions_membership_subject" class="regular-text" value="' . $subjec_membership  . '" />';
    }

    public function subscriptions_membership_email()
    {
        $email_membership =  get_option('subscriptions_membership_body', 'Hola {{first_name}} {{last_name}} bienvenid@ a <a href="{{home_url}}" target="_blank">{{site_name}}</a>. Creamos una cuenta para una membresía, en un momento te enviaremos los datos de tu membresía. Mientras tanto: <br /> Tu usuario es: {{email}} <br />Tu contraseña es: {{password}}. <br />Puedes cambiar tu contraseña en tu perfil.<br /> Estamos para ayudarte.');
        return '<textarea name="subscriptions_membership_body" class="large-text">' . $email_membership  . '</textarea>';
    }

    public function subscriptions_emails_register_options()
    {
        echo '<h3>' . __('Welcome Email.', 'subscriptions') . '</h3>';
        echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Email subject', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_email_register_subject_input() . '
                        <p class="tip">
                            ' . __('Tags that you can use', 'subscriptions') . '<br />
                            ' . __('Name', 'subscriptions') . ': {{first_name}} <br />
                            ' . __('Last Name', 'subscriptions') . ': {{last_name}} <br />
                            ' . __('Email', 'subscriptions') . ': {{email}} <br />
                            ' . __('Name of subscription', 'subscriptions') . ': {{subscription_name}}<br />
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label>' . __('Email body', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_email_register_body_input() . '
                        <p class="tip">
                            ' . __('Tags that you can use', 'subscriptions') . '<br />
                            ' . __('Name', 'subscriptions') . ': {{first_name}} <br />
                            ' . __('Last Name', 'subscriptions') . ': {{last_name}} <br />
                            ' . __('Email', 'subscriptions') . ': {{email}} <br />
                            ' . __('Name of subscription', 'subscriptions') . ': {{subscription_name}}<br />
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>';
        //email memberships
        echo '<h3>' . __('Datos de usuario (creado desde Membresías).', 'subscriptions') . '</h3>';
            echo '<table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th><label>' . __('Asunto', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_membership_subject() . '</td>
                </tr>
                <tr>
                    <th><label>' . __('Cuerpo', 'subscriptions') . '</label></th>
                    <td>' . $this->subscriptions_membership_email() . '</td>
                </tr>
            </tbody>
        </table>';
        //email memberships
        echo '<h3>' . __('Admin new order email.', 'subscriptions') . '</h3>';
        echo '<table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th><label>' . __('Admin new order subject', 'subscriptions') . '</label></th>
                <td>' . $this->subscriptions_email_admin_subject_input() . '</td>
            </tr>
            <tr>
                <th><label>' . __('Admin new order body', 'subscriptions') . '</label></th>
                <td>' . $this->subscriptions_email_admin_body_input() . '</td>
            </tr>
        </tbody>
    </table>';
    }

    public function subscriptions_emails_register_save()
    {
        if (isset($_POST['subscriptions_email_register_subject']))
            update_option('subscriptions_email_register_subject', sanitize_text_field($_POST['subscriptions_email_register_subject']));

        if (isset($_POST['subscriptions_email_register_body']))
            update_option('subscriptions_email_register_body', esc_textarea($_POST['subscriptions_email_register_body']));

        if (isset($_POST['admin_order_email_subject']))
            update_option('admin_order_email_subject', sanitize_textarea_field($_POST['admin_order_email_subject']));

        if (isset($_POST['admin_order_email_body']))
            update_option('admin_order_email_body', esc_textarea($_POST['admin_order_email_body']));

        if(isset($_POST['subscriptions_membership_subject']))
            update_option('subscriptions_membership_subject', sanitize_text_field($_POST['subscriptions_membership_subject']));
        
        if(isset($_POST['subscriptions_membership_body']))
            update_option('subscriptions_membership_body', $_POST['subscriptions_membership_body']);
    }
}

$subscriptions_options = new Subscriptions_Options();
