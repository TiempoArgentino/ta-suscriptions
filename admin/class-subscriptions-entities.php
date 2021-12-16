<?php

/**
 * This is simple: https://developer.wordpress.org/plugins/post-types/
 */


class Subscriptions_Entities
{

    public function __construct()
    {
        add_action('init', [$this, 'suscription_post_type'], 0);
        // add_action('init', [$this, 'subscriptions_type']);
        add_action('add_meta_boxes', [$this, 'add_metabox_suscription']);
        add_action('save_post', [$this, 'save_subscriptions_meta_box_data']);
        add_filter('manage_subscriptions_posts_columns', [$this, 'set_custom_edit_subscriptions_columns']);
        add_action('manage_subscriptions_posts_custom_column', [$this, 'custom_subscriptions_column'], 10, 2);

        add_action('init', [$this, 'memberships_post_type']);
        //add_action('init', [$this, 'donations_post_type']);


        add_filter('manage_memberships_posts_columns', [$this, 'set_custom_edit_memberships_columns']);
        add_action('manage_memberships_posts_custom_column', [$this, 'custom_memberships_column'], 10, 2);
        add_filter('manage_edit-memberships_sortable_columns', [$this, 'set_memberships_sortable_columns']);

        add_action('admin_menu', [$this, 'remove_editform_memberships']);
        add_action('add_meta_boxes', [$this, 'add_memberships_metabox']);
        add_action('add_meta_boxes', [$this, 'add_memberships_actions_metabox']);
        add_action('add_meta_boxes', [$this, 'add_memberships_payment_metabox']);

        add_action('save_post_memberships', [$this, 'resend_email_actions'], 10, 3);


        add_action('add_meta_boxes', [$this, 'donations_metabox']);
        add_action('save_post_donations', [$this, 'save_donations_meta_box_data']);

        add_action('before_delete_post', [$this, 'delete_user_membership'], 99, 2);

        add_action('restrict_manage_posts', [$this, 'filter_by_status'], 10);

        add_action('restrict_manage_posts', [$this, 'filter_by_subscription'], 10);

        add_action('restrict_manage_posts', [$this, 'filter_by_email'], 10);

        add_action('parse_query', [$this, 'search_by_filter'], 10);
    }

    /**
     * Subscription post type and metaboxes
     */
    public function suscription_post_type()
    {
        $labels = [
            'name' => __('Subscriptions', 'subscriptions'),
            'singular_name' => __('Subscription', 'subscriptions'),
            'menu_name' => __('Subscriptions', 'subscriptions'),
            'all_items' => __('All Subscriptions', 'subscriptions'),
            'add_new' => __('New Subscription', 'subscriptions'),
            'add_new_item' => __('New', 'subscriptions'),
            'edit_item' => __('Edit', 'subscriptions'),
            'new_item' => __('New', 'subscriptions'),
            'view_item' => __('View', 'subscriptions'),
            'view_items' => __('View all', 'subscriptions'),
            'search_items' => __('Search Subscriptions', 'subscriptions'),
            'not_found' => __('Subscriptions not found', 'subscriptions'),
            'not_found_in_trash' => __('Trash empty', 'subscriptions'),
            'featured_image' => __('Feature Image', 'subscriptions'),
            'set_featured_image' => __('Fix main image', 'subscriptions'),
            'remove_featured_image' => __('Remove main image', 'subscriptions'),
        ];

        $args = [
            'label' => __('Subscriptions', 'subscriptions'),
            'labels' => $labels,
            'description' => '',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'has_archive' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
            'exclude_from_search' => false,
            'capability_type' => ['subscription', 'subscriptions'],
            'map_meta_cap' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' =>  get_option('suscription_post_type_slug', 'suscription'), 'with_front' => false, 'feeds' => true],
            'query_var' => true,
            'menu_icon' => 'dashicons-feedback',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt']
        ];

        register_post_type('subscriptions', $args);
    }


    /**
     * Admin Columns
     */
    public function set_custom_edit_subscriptions_columns($columns)
    {
        $columns['_donation'] = __('Type:', 'subscriptions');
        $columns['_time'] = __('Period:', 'subscriptions');
        $columns['_s_price'] = __('Prices range:', 'subscriptions');
        return $columns;
    }
    public function custom_subscriptions_column($column, $post_id)
    {
        $time = get_post_meta($post_id, '_period_time', true);

        $s_price = get_post_meta($post_id, '_s_price', true);

        $other_prices = get_post_meta($post_id, '_prices_extra', true);

        $extra_price = !$other_prices ? '' : ', ' . implode(', ', $other_prices);

        $price_custom = get_post_meta($post_id, '_price_custom', true);

        $price_customs = !$price_custom ? '' : __(' and custom price', 'subscriptions');

        $donation = get_post_meta($post_id, '_is_donation', true) === '1' ? __('Donation', 'subscriptions') : __('Subscription', 'subscriptions');

        switch ($column) {
            case '_time';
                echo $time === 'period_months' ? __('Monthly', 'subscriptions') : __('Daily', 'subscriptions');
                break;
            case '_s_price';
                echo $s_price . $extra_price . $price_customs;
                break;
            case '_donation';
                echo $donation;
                break;
        }
    }
    /**
     * Subscriptions Metaboxes
     */
    public function add_metabox_suscription()
    {
        add_meta_box(
            'suscripciones-box-s',
            __('Options & Details', 'subscriptions'),
            [$this, 'subscriptions_meta_box_callback_s'],
            ['subscriptions'],
            'side',
            'high'
        );
    }

    public function subscriptions_meta_box_callback_s($post)
    {
        /**
         * Nonce
         */
        wp_nonce_field('subscriptions_nonce_s', 'subscriptions_nonce_s');
        /**
         * Fields
         */
        $s_price = get_post_meta($post->ID, '_s_price', true);
        $prices_extra = get_post_meta($post->ID, '_prices_extra', true);
        $price_custom = get_post_meta($post->ID, '_price_custom', true);
        $period_number = get_post_meta($post->ID, '_period_number', true);
        $period_time = get_post_meta($post->ID, '_period_time', true);
        $donation = get_post_meta($post->ID, '_is_donation', true);

        $type = get_post_meta($post->ID,'_is_type',true);

        $special = get_post_meta($post->ID, '_is_special', true);

        $physical = get_post_meta($post->ID, '_physical', true);

        $discount = get_post_meta($post->ID, '_discount', true);
        $show_discount = metadata_exists($post->ID, '_discount', true) ? 'display:block !important;"' : '';
        /**
         * Price
         */
        $field = '<label class="components-base-control__label">
       ' . __('Prices: ', 'subscriptions') . ' <span class="dashicons-plus dashicons add-price" title="' . __('add other price', 'subscriptions') . '"></span>
       </label>
       <div id="prices-container"><div><input type="text" name="s_price" style="max-width:80px" value="' . $s_price . '" /></div></div>
       ';
        /**
         * Extra prices
         */
        if ($prices_extra && count($prices_extra) > 0) {
            foreach ($prices_extra as $key => $value) {
                $field .= '<div id="price-extra-' . $key . '" style="margin-top:5px"><input type="text" name="prices_extra[]" style="max-width:80px" value="' . $value . '" /><span class="dashicons-trash dashicons remove-price" data-id="#price-extra-' . $key . '" style="cursor:pointer"></span></div>';
            }
        }
        /**
         * Custom price?
         */
        $field .= '<br /><p><label class="components-base-control__label">
        ' . __('Accept custom price?: ', 'subscriptions') . '
        <input type="checkbox" value="1" name="_price_custom" ' . checked(1, $price_custom, false) . ' />
        </label></p>';
        /**
         * Subscription period
         */
        $field .=  '<label class="components-base-control__label">
        ' . __('Subscription Period: ', 'subscriptions') . '
        </label><br /><input type="text" name="period_number" style="max-width:50px" value="' . $period_number . '" />';

        $field .= '<select name="period_time">
            <option value="">' . __('--select--', 'subscriptions') . '</option>
            <option value="period_days" ' . selected($period_time, 'period_days', false) . '>' . __('Days', 'subscriptions') . '</option>
            <option value="period_months" ' . selected($period_time, 'period_months', false) . '>' . __('Months', 'subscriptions') . '</option>
        </select><br />';

        $field .=  '<p><label class="components-base-control__label">
        ' . __('Tipo de suscripción: ', 'subscriptions') . '
        </label><br />';
        $field .= '<select name="type">
            <option value="digital" ' . selected($type, 'digital', false) . '>' . __('Digital', 'subscriptions') . '</option>
            <option value="not_digital" ' . selected($type, 'not_digital', false) . '>' . __('Suscriptor', 'subscriptions') . '</option>
        </select></p>';
        /**
         * Suscriptión data
         */
        $field .=  '<label class="components-base-control__label" style="margin-top:10px;display: block;">
        ' . __('Have a physical product with shipping? ', 'subscriptions') . '
            <input type="checkbox" name="_physical" value="1" ' . checked(1, $physical, false) . ' />
        </label>';
        /**
         * Special (for other design for example)
         */
        $field .= '<label class="components-base-control__label" style="margin-top:10px;display: block;">
        ' . __('Special subscription ', 'subscriptions') . '
            <input type="checkbox" name="_is_special" value="1" ' . checked(1, $special, false) . ' />
        </label>';
        /**
         * Subscription month donation
         */
        $field .=  '<label class="components-base-control__label" style="margin-top:10px;display: block;padding-bottom:10px">
        ' . __('Is it a monthly donation? ', 'subscriptions') . '
            <input type="checkbox" name="_donation" id="donation_check" value="1" ' . checked(1, $donation, false) . ' />
        </label>';
        $field .= '<label class="components-base-control__label show-discount" style="' . $show_discount . 'padding-top:10px">
        ' . __('Discount (only percent number): ', 'subscriptions') . '</span>
        </label>
        <div style="padding-bottom:10px"><div><input type="number" class="show-discount" id="_discount" name="_discount" style="max-width:80px;' . $show_discount . '" value="' . $discount . '" /></div>
        <p class="show-discount" style="' . $show_discount . '">' . __('Only donations', 'subscriptions') . '</p>
        </div>
        ';
        echo $field;
    }

    public function save_subscriptions_meta_box_data($post_id)
    {
        /**
         * Check if nonce is set
         */
        if (!isset($_POST['subscriptions_nonce_s'])) {
            return;
        }
        /**
         * Is nonce valid?
         */
        if (!wp_verify_nonce($_POST['subscriptions_nonce_s'], 'subscriptions_nonce_s')) {
            return;
        }
        /**
         * Current user can edit?
         */
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        /** 
         * Fields
         */
        $s_price = $_POST['s_price'];
        $period_number = $_POST['period_number'];
        $period_time = $_POST['period_time'];
        $type = $_POST['type'];

        if (isset($_POST['_price_custom']))
            $price_custom = $_POST['_price_custom'];

        /**
         * Add or update suscription post meta
         */
        update_post_meta($post_id, '_period_number', $period_number);
        update_post_meta($post_id, '_period_time', $period_time);
        update_post_meta($post_id, '_s_price', $s_price);
        update_post_meta($post_id, '_is_type', $type);

        if (isset($_POST['_price_custom']))
            update_post_meta($post_id, '_price_custom', $price_custom);

        if (isset($_POST['prices_extra']) && count($_POST['prices_extra']) > 0) {
            for ($i = 0; $i < count($_POST['prices_extra']); $i++) {
                update_post_meta($post_id, '_prices_extra', $_POST['prices_extra']);
            }
        }

        if (isset($_POST['_discount'])) {
            update_post_meta($post_id, '_discount', $_POST['_discount']);
        }

        if (isset($_POST['_physical'])) :
            update_post_meta($post_id, '_physical', 1);
        else :
            update_post_meta($post_id, '_physical', 0);
        endif;

        if (isset($_POST['_is_special'])) :
            update_post_meta($post_id, '_is_special', 1);
        else :
            update_post_meta($post_id, '_is_special', 0);
        endif;

        if (isset($_POST['_donation'])) :
            update_post_meta($post_id, '_is_donation', 1);
        else :
            update_post_meta($post_id, '_is_donation', 0);
        endif;
    }
    /**
     * Taxonomy: Subscriptions Types.
     */
    public function subscriptions_type()
    {
        $labels = [
            'name' => __('Subscriptions Types', 'subscriptions'),
            'singular_name' => __('Subscription Type', 'subscriptions'),
            'menu_name' => __('Subscriptions Types', 'subscriptions'),
        ];

        $args = [
            'label' => __('Subscriptions Types', 'subscriptions'),
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'rewrite' => ['slug' => get_option('suscription_taxonomy_slug', 'subscriptions_type'), 'with_front' => false, 'feeds' => true],
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rest_base' => 'subscriptions_type',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'show_in_quick_edit' => false,
        ];
        register_taxonomy('subscriptions_type', ['subscriptions'], $args);
    }

    /**
     * Membership post type and metaboxes
     */
    public function memberships_post_type()
    {

        /**
         * Post Type: Memberships.
         */

        $labels = [
            'name' => __('Memberships', 'subscriptions'),
            'singular_name' => __("Membership", 'subscriptions'),
            'menu_name' => __('Memberships', 'subscriptions'),
        ];

        $args = [
            'label' => __('Memberships', 'subscriptions'),
            'labels' => $labels,
            'description' => '',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'has_archive' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
            'exclude_from_search' => false,
            'capability_type' => ['membership', 'memberships'],
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
            ),
            'map_meta_cap' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'memberships', 'with_front' => false],
            'query_var' => true,
            'supports' => ['title'],
        ];

        register_post_type('memberships', $args);
    }
    /**
     * Columns
     */
    public function set_custom_edit_memberships_columns($columns)
    {
        $columns['cb'] = $columns['cb'];
        $columns['title'] = __('Reference', 'subscriptions');
        $columns['user'] = __('User', 'subscriptions');
        $columns['email'] = __('Email', 'subscriptions');
        $columns['status_order'] = __('Status', 'subscriptions');
        $columns['payment_method'] = __('Payment Method', 'subscriptions');
        $columns['subscription'] = __('Subscription', 'subscriptions');
        $columns['payment_type'] = __('Type', 'subscriptions');
        $columns['renewal'] = __('Renewal Date', 'subscriptions');
        $columns['date']  = __('Date create', 'subscriptions');
        $columns['cost'] = __('Pay', 'subscriptions');

        $ordering = ['cb', 'title', 'user', 'email', 'status_order', 'payment_method', 'subscription', 'payment_type', 'renewal', 'cost', 'date'];

        foreach ($ordering as $colname)
            $new_order[$colname] = $columns[$colname];
        return $new_order;
    }
    /***
     * Order status
     */
    public function panel_order_status($post_id)
    {
        $status = get_post_meta($post_id, '_member_order_status', true);
        $name = SE()->get_status_name($status);
        $color = SE()->get_status_color($status);

        return '<span class="status_order" style="background:' . $color . '">' . $name . '</span>';
    }
    /**
     * Colums content
     */
    public function custom_memberships_column($column, $post_id)
    {
        $user = get_user_by('id', get_post_meta($post_id, '_member_user_id', true));

        switch ($column) {
            case 'user':
                echo $user ? $user->first_name . ' ' . $user->last_name : __('This is a error!!!!', 'subscriptions');
                break;
            case 'email':
                echo $user->{'user_email'};
                break;
            case 'status_order':
                echo $this->panel_order_status($post_id);
                break;
            case 'payment_method';
                echo get_post_meta($post_id, '_member_payment_method_title', true);
                break;
            case 'subscription';
                echo get_post_meta($post_id, '_member_suscription_name', true);
                break;
            case 'payment_type';
                echo get_post_meta($post_id, 'payment_type', true) === 'subscription' ? __('Subscription', 'subscriptions') : __('Donation', 'subscriptions');
                break;
            case 'renewal':
                echo get_post_meta($post_id, '_member_renewal_date', true);
                break;
            case 'cost';
                echo get_option('subscriptions_currency_symbol', 'ARS') . ' ' . get_post_meta($post_id, '_member_suscription_cost', true);
        }
    }
    /**
     * Columns sort
     */
    public function set_memberships_sortable_columns($columns)
    {
        $columns['status_order'] = __('Status', 'subscriptions');
        $columns['subscription'] = __('Subscription', 'subscriptions');
        $columns['payment_method'] = __('Payment Method', 'subscriptions');
        $columns['payment_type'] = __('Type', 'subscriptions');
        $columns['renewal'] = __('Renewal Date', 'subscriptions');
        $columns['cost'] = __('Cost', 'subscriptions');
        return $columns;
    }
    /**
     * Remove edit form
     */
    public function remove_editform_memberships()
    {
        remove_meta_box('submitdiv', 'memberships', 'normal');
    }
    /**
     * Memberships metabox
     */
    public function add_memberships_metabox()
    {
        add_meta_box(
            'membership-data',
            __('Membership data', 'subscriptions'),
            [$this, 'member_data_meta_box_callback'],
            ['memberships']
        );
    }
    /**
     * Panels
     */
    public function panel_data($post)
    {
        $wrap = '<div class="panel-column">';
        $wrap .= '<h4>' . __('General ', 'subscriptions') . '</h4>';
        $wrap .= '<div class="field-orders"><strong>' . __('Reference: ', 'subscriptions') . ': </strong>' . get_post_meta($post->ID, '_member_order_reference', true) . '</div>';
        $wrap .= '<div class="field-orders"><strong>' . __('Date create', 'subscriptions') . ': </strong>' . get_the_date('Y-m-d H:m_s') . '</div>';
        $wrap .= '<div class="field-orders"><strong>' . __('Order Status', 'subscriptions') . ': </strong>' . $this->panel_order_status($post->ID) . '</div>';

        $wrap .= '</div>';
        return $wrap;
    }
    public function panel_user($post)
    {
        $user = get_user_by('id', get_post_meta($post->ID, '_member_user_id', true));
        $wrap = '<div class="panel-column">';
        $wrap .= '<h4>' . __('User data', 'subscriptions') . '</h4>';
        $wrap .= '<div class="field-orders"><strong>' . __('Full name: ', 'subscriptions') . '</strong>' . $user->first_name . ' ' . $user->last_name . '</div>';
        $wrap .= '<div class="field-orders"><strong>' . __('Email: ', 'subscriptions') . '</strong>' . $user->user_email . ' </div>';
        $wrap .= '</div>';
        return $wrap;
    }
    public function panel_suscription($post)
    {
        $period = get_post_meta($post->ID, '_member_suscription_period', true);
        $period_number = get_post_meta($post->ID, '_member_suscription_period_number', true);
        $suscription_name = get_post_meta($post->ID, '_member_suscription_name', true);
        $wrap = '<div class="panel-column">';
        $wrap .= '<h4>' . __('Subscription', 'subscriptions') . '</h4>';
        $wrap .= '<div class="field-orders"><strong>' . __('Name: ', 'subscriptions') . '</strong>' . $suscription_name . '</div>';
        $wrap .= '<div class="field-orders"><strong>' . __('Period: ', 'subscriptions') . '</strong>' . $period_number . ' ' . $period . '</div>';
        $wrap .= '<div class="field-orders"><strong>' . __('Pay: ', 'subscriptions') . '</strong>' . get_option('subscriptions_currency_symbol', 'ARS') . ' ' . get_post_meta($post->ID, '_member_suscription_cost', true) . '</div>';
        $wrap .= '</div>';
        return $wrap;
    }
    /**
     * Memberships data callback
     */
    public function member_data_meta_box_callback($post)
    {
        $wrap = '<div class="panel-wrap subscriptions">';
        $wrap .= $this->panel_data($post);
        $wrap .= $this->panel_user($post);
        $wrap .= $this->panel_suscription($post);
        $wrap .= '</div>';
        echo $wrap;
    }

    /**
     * Memberships actions
     */
    public function add_memberships_actions_metabox()
    {
        add_meta_box(
            'memberships-actions',
            __('Actions for order', 'subscriptions'),
            [$this, 'memberships_actions_callback'],
            ['memberships'],
            'side'
        );
    }

    public function memberships_actions_email_select()
    {
        $select = '<h4>' . __('Re send emails', 'subscriptions') . '</h4>';
        $select .= '<select name="resend-email" id="resend-email">
            <option value=""> ' . __('Choose a option', 'subscriptions') . ' </option>
            <option value="_welcome_user_email_"> ' . __('Re-send welcome email to suscriber.', 'subscriptions') . ' </option>
            <option value="_order_email_"> ' . __('Re-send order email to suscriber.', 'subscriptions') . ' </option>
        </select>';
        $select .= '<button type="submit" class="button-block-subscriptions button" id="button-block-subscriptions">' . __('Send', 'subscriptions') . '</button>';
        return $select;
    }

    public function change_status($post)
    {
        $status = SE()->get_all_status();
        $default_status = get_post_meta($post->ID, '_member_order_status', true);

        $select = '<h4>' . __('Change status', 'subscriptions') . '</h4>';
        $select .= '<select name="change_status" id="change_status">';
        foreach ($status as $status) {
            $select .= '<option value="' . $status->status_slug . '" ' . selected($default_status, $status->status_slug, false) . '>' . $status->status_name . '</option>';
        }
        $select .= '</select>';
        $select .= '<label class="check-action"><input type="checkbox" name="send_email_too" value="1"> ' . __('Send Email Too?', 'subscriptions') . '</label>';
        $select .= '<button type="submit" class="button-status-subscriptions button" id="button-status-subscriptions">' . __('Update Status', 'subscriptions') . '</button>';
        return $select;
    }

    public function memberships_actions_callback($post)
    {
        echo $this->memberships_actions_email_select();
        echo $this->change_status($post);
    }

    private function resend_welcome_email($post_id)
    {
        $user = get_user_by('id', get_post_meta($post_id, '_member_user_id', true));
        $email = $user->user_email;

        Subscriptions_Emails::email_success_register($email);
    }

    private function resend_memberships_order_email($post_id)
    {
        $order_status = get_post_meta($post_id, '_member_order_status', true);
        $user = get_user_by('id', get_post_meta($post_id, '_member_user_id', true));
        $email = $user->user_email;

        Subscriptions_Emails::email_order($order_status, $user->user_email);
    }

    public function membership_action_save($post_id)
    {
        $user = get_user_by('id', get_post_meta($post_id, '_member_user_id', true));

        if (isset($_POST['resend-email'])) {

            if ($_POST['resend-email'] === '_welcome_user_email_') {
                $this->resend_welcome_email($post_id);
            }

            if ($_POST['resend-email'] === '_order_email_') {
                $this->resend_memberships_order_email($post_id);
            }
        }

        if (isset($_POST['change_status'])) {

            update_post_meta($post_id, '_member_order_status', $_POST['change_status']);

            //update user status
            if ($_POST['change_status'] === 'completed') {
                update_user_meta($user->ID, '_user_status', 'active');
            }
            if($_POST['change_status'] != 'completed'){
                update_user_meta($user->ID, '_user_status', 'on-hold');
            }
            //send email
            if (isset($_POST['send_email_too'])) {
                Subscriptions_Emails::email_order($_POST['change_status'], $user->user_email);
            }
        }
    }

    public function resend_email_actions($post_ID, $post_after, $post_before)
    {
        $this->membership_action_save($post_ID);
    }
    /**payment data */
    public function add_memberships_payment_metabox()
    {
        add_meta_box(
            'membership-payment-data',
            __('Payment Data', 'subscriptions'),
            [$this, 'member_payment_meta_box_callback'],
            ['memberships']
        );
    }

    public function payment_panel($post)
    {
        $payment_data = get_post_meta($post->ID, 'payment_data');
        $wrap = '<div class="panel-column">';

        if ($payment_data) {
            $wrap .= '<h4>' . __('Details ', 'subscriptions') . '</h4>';
            $wrap .= '<div class="field-orders"><strong>' . __('Payment Method', 'subscriptions') . ': </strong>' . get_post_meta($post->ID, '_member_payment_method_title', true) . ' (' . get_post_meta($post->ID, '_member_payment_method', true) . ')</div>';
            foreach ($payment_data[0] as $key => $val) {
                $wrap .= '<div class="field-orders"><strong>' . $key . ': </strong>' . $val . '</div>';
            }
        } else {
            $wrap .= '<h4>' . __('Not payment data found', 'subscriptions') . '</h4>';
        }
        $wrap .= '</div>';
        return $wrap;
    }

    public function member_payment_meta_box_callback($post)
    {
        $wrap = '<div class="panel-wrap subscriptions">';
        $wrap .= $this->payment_panel($post);
        $wrap .= '</div>';
        echo $wrap;
    }
    /**
     * Post Type: Donations.
     */
    public function donations_post_type()
    {

        $labels = [
            'name' => __('Donations', 'subscriptions'),
            'singular_name' => __('Donation', 'subscriptions'),
        ];

        $args = [
            'label' => __('Donations', 'subscriptions'),
            'labels' => $labels,
            'description' => '',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'has_archive' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'delete_with_user' => false,
            'exclude_from_search' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'rewrite' => ['slug' => get_option('donations_post_type_slug', 'donations'), 'with_front' => false, 'feeds' => true],
            'query_var' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
        ];

        register_post_type('donations', $args);
    }

    public function donations_metabox()
    {
        add_meta_box(
            'donations-box',
            __('Prices', 'subscriptions'),
            [$this, 'donations_callback'],
            ['donations'],
            'side',
            'high'
        );
    }

    public function donations_callback($post)
    {
        wp_nonce_field('donations_nonce', 'donations_nonce');

        $d_price = get_post_meta($post->ID, '_d_price', true);
        $d_extra_price = get_post_meta($post->ID, '_d_prices_extra', true);
        $d_price_custom = get_post_meta($post->ID, '_d_price_custom', true);
        /**
         * Price
         */
        $field = '<label class="components-base-control__label">
       ' . __('Prices: ', 'subscriptions') . ' <span class="dashicons-plus dashicons add-price" title="' . __('add other price', 'subscriptions') . '"></span>
       </label>
       <div id="prices-container"><div><input type="text" name="d_price" style="max-width:80px" value="' . $d_price . '" /></div></div>
       ';
        /**
         * Extra prices
         */
        if ($d_extra_price && count($d_extra_price) > 0) {
            foreach ($d_extra_price as $key => $value) {
                $field .= '<div id="price-extra-' . $key . '" style="margin-top:5px"><input type="text" name="d_prices_extra[]" style="max-width:80px" value="' . $value . '" /><span class="dashicons-trash dashicons remove-price" data-id="#price-extra-' . $key . '" style="cursor:pointer"></span></div>';
            }
        }
        /**
         * Custom price?
         */
        $field .= '<br /><p><label class="components-base-control__label">
        ' . __('Accept custom price?: ', 'subscriptions') . '
        <input type="checkbox" value="1" name="d_price_custom" ' . checked(1, $d_price_custom, false) . ' />
        </label></p>';

        echo $field;
    }
    public function save_donations_meta_box_data($post_id)
    {
        /**
         * Check if nonce is set
         */
        if (!isset($_POST['donations_nonce'])) {
            return;
        }
        /**
         * Is nonce valid?
         */
        if (!wp_verify_nonce($_POST['donations_nonce'], 'donations_nonce')) {
            return;
        }
        /**
         * Current user can edit?
         */
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        /** 
         * Fields
         */
        $d_price = $_POST['d_price'];
        $d_prices_extras = $_POST['prices_extra'];

        if (isset($_POST['d_price_custom']))
            $d_price_custom = $_POST['d_price_custom'];

        /**
         * Add or update suscription post meta
         */
        update_post_meta($post_id, '_d_price', $d_price);
        if (isset($_POST['d_price_custom']))
            update_post_meta($post_id, '_d_price_custom', $d_price_custom);

        if (isset($d_prices_extras) && count($d_prices_extras) > 0) {
            for ($i = 0; $i < count($d_prices_extras); $i++) {
                update_post_meta($post_id, '_d_prices_extra', $d_prices_extras);
            }
        }
    }

    /**delete membership */
    public function delete_user_membership($postid, $post)
    {

        if ('memberships' !== $post->post_type) {
            return;
        }

        $user = get_post_meta($postid, '_member_user_id', true);
        delete_user_meta($user, 'suscription');
        delete_user_meta($user, 'suscription_name');
    }

    /**
     * Filters: by status
     */
    public function filter_by_status($post_type)
    {
        if ('memberships' !== $post_type) {
            return;
        }

        global $wpdb;
        $results = $wpdb->get_results('SELECT status_name,status_slug FROM ' . $wpdb->prefix . 'subscriptions_status_emails');

        $current_status = isset($_REQUEST['membership_status']) && $_REQUEST['membership_status'] != 0 ? $_REQUEST['membership_status'] : '';

        echo '<select id="membership_status" name="membership_status">';
        echo '<option value="">' . __('Status', 'subscriptions') . ' </option>';
        foreach ($results as $r) {
            echo '<option value="' . $r->{'status_slug'} . '" ' . selected($r->{'status_slug'}, $current_status, false) . ' >' . $r->{'status_name'} . ' </option>';
        }
        echo '</select>';
    }

    /**
     * Filters: by subscritpion
     */
    public function filter_by_subscription($post_type)
    {
        if ('memberships' !== $post_type) {
            return;
        }

        $args = [
            'post_type' => 'subscriptions'

        ];

        $list = get_posts($args);

        $current_subscription = isset($_REQUEST['membership_subscription']) && $_REQUEST['membership_subscription'] != 0 ? $_REQUEST['membership_subscription'] : '';

        echo '<select id="membership_subscription" name="membership_subscription">';
        echo '<option value="">' . __('Subscription', 'subscriptions') . ' </option>';

        foreach ($list as $s) {
            echo '<option value="' . $s->{'ID'} . '" ' . selected($s->{'ID'}, $current_subscription, false) . ' >' . $s->{'post_title'} . ' </option>';
        }
        echo '</select>';
    }


    /**
     * filter by email
     */
    public function filter_by_email($post_type)
    {
        if ('memberships' !== $post_type) {
            return;
        }

        $current_email = isset($_REQUEST['membership_email']) && $_REQUEST['membership_email'] != '' ? $_REQUEST['membership_email'] : '';

        echo '<input type="email" value="' . $current_email . '" name="membership_email" placeholder="User Email" />';
    }


    public function search_by_filter($query)
    {
        if (!(is_admin() and $query->is_main_query())) {
            return $query;
        }

        if ('memberships' !== $query->query['post_type']) {
            return $query;
        }

        $query->set('post_type','memberships');

        $meta_query = [];
        $queryParamsCounter = 0;

        if (isset($_REQUEST['membership_email']) && $_REQUEST['membership_email'] != '') {
            $queryParamsCounter++;
            $user = get_user_by('email', $_REQUEST['membership_email']);

            $key = $user->{'ID'};
            $meta_query[] =
                array(
                    'key'     => '_member_user_id',
                    'value'   => $key,
                    'compare' => '=',
                );
        }

        if (isset($_REQUEST['membership_subscription']) && $_REQUEST['membership_subscription'] != '') {
            $queryParamsCounter++;
            //type filter
            $key =  $_REQUEST['membership_subscription'];

            $meta_query[] =
                array(
                    'key'     => '_member_suscription_id',
                    'value'   => $key,
                    'compare' => '=',
                );
        }

        if (isset($_REQUEST['membership_status']) && $_REQUEST['membership_status'] != '') {
            $queryParamsCounter++;
            $key =  $_REQUEST['membership_status'];
            $meta_query[] =
                array(
                    'key'     => '_member_order_status',    
                    'value'   => $key,
                    'compare' => '=',
                );
        }
        if($queryParamsCounter > 1) {
            $meta_query['relation'] = 'AND';
        }
        $query->set('meta_query',$meta_query);
        return $query;
    }
}


$sucriptions_entities = new Subscriptions_Entities();
