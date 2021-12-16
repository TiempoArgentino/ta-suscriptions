<?php


class Subscriptions_Status_Emails
{

    public function __construct()
    {
        add_action( 'admin_init', [$this,'update_status'] );
        add_action( 'admin_init', [$this,'delete_status'] );
        add_action( 'admin_init', [$this,'create_options'] );
    }

    private function insert_data($data = [], $replace = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'subscriptions_status_emails';
        $wpdb->insert($table_name, $data, $replace);
    }

    private function update_data($data, $where, $data_format = [], $where_format = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'subscriptions_status_emails';
        $wpdb->update($table_name, $data, $where, $data_format, $where_format);
    }

    private function delete_data($where, $where_format)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'subscriptions_status_emails';
        $wpdb->delete($table_name, $where, $where_format);
    }

    public function get_all_status()
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subscriptions_status_emails", OBJECT);
        return $results;
    }

    public function get_status($status_slug)
    {
        global $wpdb;
        $results = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}subscriptions_status_emails WHERE status_slug=%s", $status_slug)
        );
        return $results;
    }
    /**
     * Function for other things
     */
    public function get_status_name($status_slug)
    {
        global $wpdb;
        $results = $wpdb->get_row(
            $wpdb->prepare("SELECT status_name FROM {$wpdb->prefix}subscriptions_status_emails WHERE status_slug=%s", $status_slug)
        );
        return $results->status_name;
    }

    public function get_status_color($status_slug)
    {
        global $wpdb;
        $results = $wpdb->get_row(
            $wpdb->prepare("SELECT status_color FROM {$wpdb->prefix}subscriptions_status_emails WHERE status_slug=%s", $status_slug)
        );
        return $results->status_color;
    }

    public function get_status_slug($status_slug_name)
    {
        global $wpdb;
        $results = $wpdb->get_row(
            $wpdb->prepare("SELECT status_slug FROM {$wpdb->prefix}subscriptions_status_emails WHERE status_slug=%s", $status_slug_name)
        );
        return $results;
    }
    /**
     * Crate status
     */
    public function create_options()
    {
        if (isset($_POST['add_status'])) {
            $status_name = sanitize_text_field($_POST['status_name']);
            $status_slug = sanitize_title($status_name);
            $color = sanitize_text_field($_POST['status_color']);
            $subject = sanitize_text_field($_POST['email_subject']);
            $body = sanitize_textarea_field($_POST['email_body']);
            
            if($this->get_status_slug($status_slug) !== null){
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=error_create'));
                exit();
            }

            $status = [
                'status_name' => $status_name,
                'status_slug' => $status_slug,
                'status_color' => $color,
                'email_subject' => $subject,
                'email_body' => $body
            ];

            $create = $this->insert_data($status, ['%s', '%s', '%s', '%s', '%s']);
            if ($create === false) {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=error_create'));
                exit();
            } else {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=ok_create'));
                exit();
            }
        }
    }
    /**
     * Update status
     */
    public function update_status()
    {
        if (isset($_POST['edit_status'])) {
            $ID = $_POST['ID'];
            $status_name = sanitize_text_field($_POST['edit_status_name']);
            $color = sanitize_text_field($_POST['edit_status_color']);
            $subject = sanitize_text_field($_POST['edit_email_subject']);
            $body = sanitize_textarea_field($_POST['edit_email_body']);

            $status = [
                'status_name' => $status_name,
                'status_color' => $color,
                'email_subject' => $subject,
                'email_body' => $body
            ];
            $where = ['ID' => $ID];

            $update = $this->update_data($status, $where, null, null);
            if ($update === false) {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=error_update'));
                exit();
            } else {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=ok_update'));
                exit();
            }
        }
    }
    /**
     * Delete status
     */
    public function delete_status()
    {
        if (isset($_POST['delete_status'])) {

            $ID = $_POST['ID'];

            $status = [
                'ID' => $ID
            ];

            $delete = $this->delete_data($status, ['%d']);
            if ($delete === false) {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=error_delete'));
                exit();
            } else {
                wp_redirect(admin_url('/admin.php?page=tar_emails&msg=ok_delete'));
                exit();
            }
        }
    }
    
}



function SE()
{
    return new Subscriptions_Status_Emails();
}

SE();