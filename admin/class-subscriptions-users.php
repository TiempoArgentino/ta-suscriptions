<?php

class Subscriptions_Users_List
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'scripts']);

        add_action('subscriptions_users_data', [$this, 'get_members']);
        add_action('rest_api_init', [$this, 'search_member_endpoint']);

        add_action('admin_init', [$this, 'export_function']);

        /*** users edit */
        add_action('show_user_profile', [$this, 'suscription_user']);
        add_action('edit_user_profile', [$this, 'suscription_user']);
        add_action('personal_options_update', [$this, 'suscription_user_save']);
        add_action('edit_user_profile_update', [$this, 'suscription_user_save']);

        add_action('show_user_profile', [$this, 'user_address']);
        add_action('edit_user_profile', [$this, 'user_address']);

        add_action('personal_options_update', [$this, 'user_address_save']);
        add_action('edit_user_profile_update', [$this, 'user_address_save']);

        add_action('show_user_profile', [$this, 'active_user']);
        add_action('edit_user_profile', [$this, 'active_user']);

        add_action('personal_options_update', [$this, 'active_user_save']);
        add_action('edit_user_profile_update', [$this, 'active_user_save']);

        add_action('manage_users_columns',[$this,'active_user_column']);
        add_filter('manage_users_custom_column',  [$this,'active_user_col_show'], 10, 3);
    }


    public function scripts()
    {

        if(get_current_screen()->base !== 'suscripciones_page_user-panel-subscriptions'){
            return;
        }

        wp_enqueue_script('admin-user-script', plugin_dir_url(__FILE__) . 'js/users.js', '', SUSCRIPTIONS_VERSION, true);
        wp_localize_script('admin-user-script', 'users_vars', [
            'search' => rest_url('/members/search')
        ]);
    }

    public function get_members()
    {
        $total_users = new WP_User_Query(array('role' => 'subscriber'));

        $total_users = (int) $total_users->get_total();

        $paged = isset($_GET['paged']) !== false ? $_GET['paged'] : 1;

        $number = 40;

        $args = [
            'role__in' => ['subscriber'],
            'offset' => $paged ? ($paged - 1) * $number : 0,
            'number' => $number,
        ];
        $users = get_users($args);

        $table = '<table class="wp-list-table widefat fixed striped table-view-list">';
        $table .= '<thead>
            <tr>
                <td>' . __('Full Name', 'subscriptions') . '</td>
                <td>' . __('Email', 'subscriptions') . '</td>
                <td>' . __('Registered', 'subscriptions') . '</td>
                <td>' . __('Active', 'subscriptions') . '</td>
                <td>' . __('View Info', 'subscriptions') . '</td>
            </tr>
        </thead><tbody>';

        foreach ($users as $user) {
            $active = get_user_meta($user->ID, '_user_status', true) !== 'active' ? '<span class="data_user_status no">NO</span>' : '<span class="data_user_status yes">SI</span>';

            $table .= '<tr>
                <td>' . $user->display_name . '</td>
                <td>' . $user->user_email . '</td>
                <td>' . date('d/m/Y', strtotime($user->user_registered)) . '</td>
                <td>' . $active . '</td>
                <td><a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '"><span data-id="' . $user->ID . '" class="data_user_status info">' . __('Info', 'subscriptions') . '</span></a></td>
            <tr>';
        }

        $table .= '</tbody></table>';
        echo $table;

        if ($total_users > $number) {

            $pl_args = array(
                'base'     => add_query_arg('paged', '%#%'),
                'format'   => '',
                'total'    => ceil($total_users / $number),
                'current'  => max(1, $paged),
                'prev_text' => __('&laquo;', 'text-domain'),
                'next_text' => __('&raquo;', 'text-domain'),
            );

            echo paginate_links($pl_args);
        }
    }

    public function user_info(WP_REST_Request $request)
    {
        $data = [$request->get_json_params()];

        //id data empty
        if (!$data || sizeof($data) <= 0) {
            echo wp_send_json_error('Fetch empty body.', 502);
            exit();
        }

        //get data
        if($data[0]['email']) $email = $data[0]['email'];

        if($data[0]['after']) $after = date('Y-m-d H:i:s', strtotime($data[0]['after']));

        if($data[0]['before']) $before = date('Y-m-d H:i:s', strtotime($data[0]['before']));


        if(!$email && !$after && !$before) {
            echo wp_send_json_error( 'At least one field is required', 403 );
            exit();
        }
     
        if($email && !filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo wp_send_json_error('Email is invalid', 403);
            exit();
        } 


        if($email && $after && $before){
            $args = [
                'role__in' => ['subscriber'],
                'login' => $email,
                'date_query' => [
                    [
                    'after' => $after,
                    'before' => $before,
                    'inclusive' => true
                    ]
                ]
            ];
        }

        if($email && $after && !$before){
            $args = [
                'role__in' => ['subscriber'],
                'login' => $email,
                'date_query' => [
                    [
                    'after' => $after,
                    'inclusive' => true
                    ]
                ]
            ];
        }

        if($email && !$after && $before){
            $args = [
                'role__in' => ['subscriber'],
                'login' => $email,
                'date_query' => [
                    [
                    'before' => $before,
                    'inclusive' => true
                    ]
                ]
            ];
        }

        if(!$email && $after && $before){
            $args = [
                'role__in' => ['subscriber'],
                'date_query' => [
                    [
                    'after' => $after,
                    'before' => $before,
                    'inclusive' => true
                    ]
                ]
            ];
        }

        if(!$email && !$after && $before){
            $args = [
                'role__in' => ['subscriber'],
                'date_query' => [
                    [
                    'before' => $before,
                    'inclusive' => true
                    ]
                ]
            ];

        }
        

        if(!$email && $after && !$before){
            $args = [
                'role__in' => ['subscriber'],
                'date_query' => [
                    [
                    'after' => $after,
                    'inclusive' => true
                    ]
                ]
            ];            
        }

        if($email && !$after && !$before){
            $args = [
                'role__in' => ['subscriber'],
                'login' => $email
            ];
        }

        $users = get_users($args);
       

        if(!$users) {
            wp_send_json_error('User not found', 404);
            exit();
        }

        $user_info = [];
        foreach($users as $user) {
             //query members parameters
            $args = [
                'post_type' => 'memberships',
                'meta_query' => [
                    [
                        'key' => '_member_user_id',
                        'value' => $user->ID,
                        'compare' => '='
                    ]
                ]
            ];
            $membership = get_posts($args);

            $user_address = get_user_meta($user->ID, '_user_address', true);

            $payment = get_post_meta($membership[0]->ID, '_member_payment_method_title', true) ? get_post_meta($membership[0]->ID, '_member_payment_method_title', true) : '-';

            $payment_data = get_post_meta($membership[0]->ID, 'payment_data');

            $user_info[] = [
                'ID' => $user->ID,
                'name' => $user->first_name,
                'lastname' => $user->last_name,
                'active' => get_user_meta($user->ID, '_user_status', true) === 'active' ? 'ACTIVE' : 'INACTIVE',
                'registered' => date('d/m/Y', strtotime($user->user_registered)),
                'subscription' => get_user_meta($user->ID, 'suscription_name', true),
                'address' => isset($user_address['address']) ? $user_address['address'] : '-',
                'number' => isset($user_address['number']) ? $user_address['number'] : '-',
                'zip' => isset($user_address['zip']) ? $user_address['zip'] : '-',
                'floor' => isset($user_address['floor']) ? $user_address['floor'] : '-',
                'apt' => isset($user_address['apt']) ? $user_address['apt'] : '-',
                'bstreet' => isset($user_address['bstreet']) ? $user_address['bstreet'] : '-',
                'email' => $user->user_email,
                'state' => isset($user_address['state']) ? $user_address['state'] : '-',
                'city' => isset($user_address['city']) ? $user_address['city'] : '-',
                'payment' => $payment,
                'amount' => get_post_meta($membership[0]->ID, '_member_suscription_cost', true) ? get_post_meta($membership[0]->ID, '_member_suscription_cost', true) : '',
                'cbu' => $payment_data[0]['CBU'] != '' || $payment_data[0]['CBU'] !== null ? $payment_data[0]['CBU'] : '',
                'dni' => $payment_data[0]['DNI'] != '' || $payment_data[0]['DNI'] !== null ? $payment_data[0]['DNI'] : '',
                'cuil' => $payment_data[0]['CUIL'] != '' || $payment_data[0]['CUIL'] !== null ? $payment_data[0]['CUIL'] : ''
            ];

        }

        echo wp_send_json_success( $user_info );

        exit();
    }

    public function search_member_endpoint()
    {
        register_rest_route(
            'members/',
            '/search',
            array(
                'methods' => 'POST',
                'callback' => [$this, 'user_info'],
                'permission_callback'	=> '__return_true',
            )
        );
    }
    /**
     * Export functions
     */
    public function filter_data($str) //sanitize 
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }

    public function export_function()
    {

        if (!isset($_POST['export_field'])) return;

        if (!wp_verify_nonce($_POST['export_field'], 'export_action')) {
            die('Security error');
        };


        if(!isset($_POST['user'])) {
            echo '<script>alert("User empty")</script>';
            exit();
        }
        $user_info = [];
        foreach($_POST['user'] as $user) {
            $user_info[] =
            [
                'Apellido' => $user['lastname'],
                'Nombre' => $user['name'],
                'Calle' => $user['address'],
                'Numero' => $user['number'],
                'CPA' => $user['zip'],
                'Piso' => $user['floor'],
                'Depto' => $user['apt'],
                'Entre' => $user['bstreet'],
                'Email' => $user['email'],
                'Provincia' => $user['state'],
                'Localidad' => $user['city'],
                'Estado' => $user['active'],
                'Fecha Registro' => $user['registered'],
                'Suscripcion' => $user['subscription'],
                'Método de pago' => $user['payment'],
                'Total Pagado' => $user['amount'],
                'CBU' => $user['cbu'],
                'DNI' => $user['dni'],
                'CUIL' => $user['cuil']
            ];
            
        }

        $fileName = "members-" . date('YmdHmi') . ".xls";

        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8");

        $flag = false;
        foreach ($user_info as $row) {
            if (!$flag) {
                echo implode("\t", array_keys($row)) . "\n";
                $flag = true;
            }
            array_walk($row, [$this, 'filter_data']);
            echo implode("\t", array_values($row)) . "\n";
        }

        exit();
    }

    /*** Users Edit */
     /**
     * User
     */
    public function suscription_user($user)
    {
        $roles = get_userdata($user->ID)->roles;
        $show_form = array_diff([get_option('default_sucription_role'), get_option('subscription_digital_role')],$roles);

        if(sizeof($show_form) > 1){
           return;
        }

        $field = '<h3>' . __('Subscriptions', 'subscriptions') . '</h3>';
      
        $subscriptions = get_user_meta($user->ID, 'suscription', true);
        $subscriptions_name = get_the_title($subscriptions);

        $field .= '<table class="form-table">
        <tr>
            <th>
                <label>' . __('Current subscription', 'subscriptions') . '</label>
            </th>
            <td>';
        if ($subscriptions) {
            $field .= $subscriptions_name;
        } else {
            $field .= __('User is not a member', 'subscriptions');
        }
        $field .= '</td></tr>';

        $args = [
            'post_type' => 'subscriptions'
        ];
        query_posts($args);
        $field .= '</table>';
        echo $field;
    }
    /**
     * Subscriptions User Save
     */
    public function suscription_user_save($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        if (!empty($_POST['suscription'])) {
            update_user_meta(
                $user_id,
                'suscription',
                $_POST['suscription']
            );
        }
    }
    
    /**
     * User direction
     */

     public function user_address($user)
     {   
        $roles = get_userdata($user->ID)->roles;
        $show_form = array_diff([get_option('default_sucription_role'), get_option('subscription_digital_role')],$roles);

        if(sizeof($show_form) > 1){
           return;
        }

        $user_address = get_user_meta($user->ID,'_user_address',true);

        $address = metadata_exists('user',$user->ID,'_user_address') ? $user_address['address'] : '';
        $number = metadata_exists('user',$user->ID,'_user_address') ? $user_address['number'] : '';
        $floor = metadata_exists('user',$user->ID,'_user_address') ? $user_address['floor'] : '';
        $apt = metadata_exists('user',$user->ID,'_user_address') ? $user_address['apt'] : '';
        $zip = metadata_exists('user',$user->ID,'_user_address') ? $user_address['zip'] : '';
        $bstreet = metadata_exists('user',$user->ID,'_user_address') ? $user_address['bstreet'] : '';
        $city = metadata_exists('user',$user->ID,'_user_address') ? $user_address['city'] : '';
        $state = metadata_exists('user',$user->ID,'_user_address') ? $user_address['state'] : '';
        $observations = metadata_exists('user',$user->ID,'_user_address') ? $user_address['observations'] : '';

        $field = '<h3>'.__('Dirección del Usuario','subscriptions').'</h3>';
       
        $field .= '<table class="form-table" role="presentation">';

        $field .= '<tr>
            <th><label>'.__('Address', 'subscriptions').'</label></th>
            <td><input type="text" name="user_address" class="regular-text" value="'.$address.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('Number', 'subscriptions').'</label></th>
            <td><input type="text" name="user_number" class="regular-text" value="'.$number.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('Floor', 'subscriptions').'</label></th>
            <td><input type="text" name="user_floor" class="regular-text" value="'.$floor.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('Apt', 'subscriptions').'</label></th>
            <td><input type="text" name="user_apt" class="regular-text" value="'.$apt.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('ZIP', 'subscriptions').'</label></th>
            <td><input type="text" name="user_zip" class="regular-text" value="'.$zip.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('Between streets', 'subscriptions').'</label></th>
            <td><input type="text" name="user_bstreet" class="regular-text" value="'.$bstreet.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('City', 'subscriptions').'</label></th>
            <td><input type="text" name="user_city" class="regular-text" value="'.$city.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('State', 'subscriptions').'</label></th>
            <td><input type="text" name="user_state" class="regular-text" value="'.$state.'" /></td>
        </tr>';

        $field .= '<tr>
            <th><label>'.__('Observations', 'subscriptions').'</label></th>
            <td><textarea name="user_observations" class="large-text">'.$observations.'</textarea></td>
        </tr>';
        
        $field .= '</table>';

        echo $field;
     }

     public function user_address_save($user_id)
     {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $address = [
            'state' =>  $_POST['user_state'],
            'city' => $_POST['user_city'],
            'address' => $_POST['user_address'],
            'number' => $_POST['user_number'],
            'floor' => $_POST['user_floor'],
            'apt' => $_POST['user_apt'],
            'zip' => $_POST['user_zip'],
            'bstreet' => $_POST['user_bstreet'],
            'observations' => $_POST['user_observations']
        ];

        if (isset($_POST['user_address'])) {
            update_user_meta(
                $user_id,
                '_user_address',
                $address
            );
        }
     }

     public function active_user($user)
     {
        $user_id = $user->ID;
        $roles = get_userdata($user_id)->roles;
        $status = get_user_meta($user_id,'_user_status',true);

        $show_form = array_diff([get_option('default_sucription_role'), get_option('subscription_digital_role')],$roles);

        if(sizeof($show_form) > 1){
           return;
        }

        if(!$status && in_array(get_option('default_sucription_role'),$roles) || !$status && in_array(get_option('subscription_digital_role'),$roles) ) {
            update_user_meta($user_id,'_user_status', 'on-hold');
        }

        $field = '<h3>'.__('Estado del Usuario','subscriptions').'</h3>';
        $field .= '<table class="form-table" role="presentation"><tbody>';
        $field .= '<tr>';
        $field .= '<th>'.__('Estado', 'subscriptions').'</th>';
        $field .= '<td>
            <select name="change_user_status">
                <option value="on-hold" '.selected('on-hold',$status,false).'>'.__('Desactivado', 'subscriptions').'</option>
                <option value="active" '.selected('active',$status,false).'>'.__('Activado', 'subscriptions').'</option>
            </select>
        </td>';
        $field .= '</tr>';
        $field .= '</tbody></table>';

        echo $field;

     }

     public function active_user_save($user_id) 
     {
        if(isset($_POST['change_user_status'])) {
            update_user_meta($user_id,'_user_status', $_POST['change_user_status']);
        }
     }
     /** column */
     public function active_user_column($columns)
     {
        unset($columns['wfls_2fa_status']);
        unset($columns['posts']);

        $columns['_user_status'] = __('Estado', 'subscriptions');
        return $columns;
     }

     public function active_user_col_show($value, $column_name, $user_id)
     {
        if ($column_name == '_user_status') {
            $value = '-';
            $status = get_user_meta($user_id,'_user_status',true);
            $roles = get_userdata($user_id)->roles;

            if($status && in_array(get_option('default_sucription_role'),$roles) || $status && in_array(get_option('subscription_digital_role'),$roles) ) {
                
                if($status == 'active') {
                    $value = '<span class="user-bag user-active">Activado</span>';
                } else {
                    $value = '<span class="user-bag user-no-active">Desactivado</span>';
                }
            } 
            return $value;
        }
     }
}

function subscriptions_users_list()
{
    return new Subscriptions_Users_List;
}

subscriptions_users_list();
