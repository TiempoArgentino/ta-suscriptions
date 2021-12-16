<?php

/**
 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Subscriptions_Deactivator
{
	public static function deactivate()
	{
		/**
		 * This is not the way...
		 */
		self::remove_table_email_status();
		self::delete_some_options();
		add_action('admin_init',[self::class,'permisions']);
	}

	/**caps */
	/** caps */
	public static function permisions()
    {
        $admin = get_role( 'administrator' );
        
        $admin_cap = [
            'edit_subscription',
            'edit_subscriptions',
            'delete_subscription',
            'delete_subscritpions',
            'publish_subscriptions',
            'edit_published_subscriptions',
            'edit_membership',
            'edit_memberships',
            'delete_membership',
            'delete_memberships',
            'edit_published_memberships'
        ];

        foreach( $admin_cap as $cap ) {
            $admin->remove_cap($cap);
        }
    }
	public static function remove_table_email_status()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'subscriptions_status_emails';
		$sql = 'DROP TABLE IF EXISTS ' . $table_name;
		$wpdb->query($sql);
	}
	/**
	 * Delete some options
	 */
	public static function delete_some_options()
	{
		/**
		 * Pages
		 */
		if(get_option('subscriptions_loop_page')) {
			wp_delete_post(get_option('subscriptions_loop_page'));
			delete_option('subscriptions_loop_page');
		}
		if(get_option('subscriptions_thankyou')) {
			wp_delete_post(get_option('subscriptions_thankyou'));
			delete_option('subscriptions_thankyou');
		}
		if(get_option('subscriptions_login_register_page')) {
			wp_delete_post(get_option('subscriptions_login_register_page'));
			delete_option('subscriptions_login_register_page');
		}
		if(get_option('subscriptions_register_page')) {
			wp_delete_post(get_option('subscriptions_register_page'));
			delete_option('subscriptions_register_page');
		}
		if(get_option('subscriptions_lost_password_page')) {
			wp_delete_post(get_option('subscriptions_lost_password_page'));
			delete_option('subscriptions_lost_password_page');
		}
		if(get_option('subscriptions_payment_page')) {
			wp_delete_post(get_option('subscriptions_payment_page'));
			delete_option('subscriptions_payment_page');
		}
		if(get_option('subscriptions_terms_page')) {
			wp_delete_post(get_option('subscriptions_terms_page'));
			delete_option('subscriptions_terms_page');
		}
		if(get_option('donations')) {
			wp_delete_post(get_option('donations'));
			delete_option('donations');
		}
		/**
		 * Slugs
		 */
		delete_option('suscription_taxonomy_slug');
		delete_option('suscription_post_type_slug');
	}
}
