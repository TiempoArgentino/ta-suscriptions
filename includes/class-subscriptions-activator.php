<?php

/**
 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Subscriptions_Activator
{


	public static function activate()
	{
		/**
		 * This should be the way ... but it's not ...
		 */

		self::create_email_status_table();
		self::create_suscriber_role();
		self::create_default_pages();
		self::create_email_status_defaults();

		add_action('init', [self::class, 'flush']);
		add_action('admin_init',[self::class,'permisions']);
	}
	public static function flush()
	{
		flush_rewrite_rules();
	}
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
            $admin->add_cap($cap);
        }
    }
	/**
	 * This creates a new role for subscribers, is only test, delete this if you want.
	 */
	public static function create_suscriber_role()
	{
		$subscriber = get_role('subscriber');
		if (!$subscriber)
			add_role('subscriber', 'Subscriber', array('read' => true, 'edit_posts' => false, 'delete_posts' => false));
		update_option('default_sucription_role', 'subscriber');
	}
	/**
	 * Status emails create table
	 */
	public static function create_email_status_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'subscriptions_status_emails';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ( `ID` INT NOT NULL AUTO_INCREMENT , `status_name` VARCHAR(100) NOT NULL , `status_slug` VARCHAR(120) NOT NULL , `status_color` VARCHAR(50) NOT NULL , `email_subject` VARCHAR(200) NULL , `email_body` TEXT NULL , PRIMARY KEY (`ID`)) ' . $charset_collate;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	/**
	 * Status emails deafult
	 */
	public static function create_email_status_defaults()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'subscriptions_status_emails';

		return $wpdb->query("INSERT INTO " . $table_name . "
            (`status_name`, `status_slug`, `status_color`, `email_subject`, `email_body`)
            VALUES
            ('Completed', 'completed', '#43aa8b', '{{first_name}} you are suscribe in {{subscription_name}}', '{{first_name}} you are suscribe in {{subscription_name}}'),
            ('On Hold', 'on-hold', '#f8961e', 'Hi {{first_name}}, your subscription {{subscription_name}} is on hold for payment', 'Hi {{first_name}}, your subscription {{subscription_name}} is on hold for payment'),
			('Error', 'error', '#f94144', 'Hi {{first_name}} there was a error with you order for {{subscription_name}}', 'Hi {{first_name}} there was a error with you order for {{subscription_name}}, we sorry.'),
			('Cancel', 'cancel', '#006699', '{{first_name}} your membership {{subscription_name}} is cancel', 'Hi {{first_name}} your membership {{subscription_name}} is cancel, see you soon :).'),
			('Renewal', 'renewal', '#ffb703', '', '')");
	}
	/**
	 * Default pages querys, function base: https://developer.wordpress.org/reference/functions/post_exists/
	 */
	public static function page_exists($page_slug)
	{
		global $wpdb;
		$post_title = wp_unslash(sanitize_post_field('post_name', $page_slug, 0, 'db'));

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args  = array();

		if (!empty($page_slug)) {
			$query .= ' AND post_name = %s';
			$args[] = $post_title;
		}

		if (!empty($args)) {
			return (int) $wpdb->get_var($wpdb->prepare($query, $args));
		}

		return 0;
	}
	/**
	 * Create pages
	 */
	public static function create_default_pages()
	{
		if (self::page_exists(get_option('subscriptions_loop_page', 'subscriptions')) === 0) {
			$page = self::create_subscriptions_loop();
			update_option('subscriptions_loop_page', $page);
		}

		if (self::page_exists(get_option('subscriptions_thankyou', 'thank-you')) === 0) {
			$page = self::create_thanks_page();
			update_option('subscriptions_thankyou', $page);
		}

		if (self::page_exists(get_option('subscriptions_login_register_page', 'sign-in')) === 0) {
			$page = self::create_login_register_page();
			update_option('subscriptions_login_register_page', $page);
		}

		if (self::page_exists(get_option('subscriptions_register_page', 'sign-up')) === 0) {
			$page = self::create_register_page();
			update_option('subscriptions_register_page', $page);
		}

		if (self::page_exists(get_option('subscriptions_lost_password_page', 'lost-password')) === 0) {
			$page = self::create_lost_password_page();
			update_option('subscriptions_lost_password_page', $page);
		}

		if (self::page_exists(get_option('subscriptions_payment_page', 'payment-page')) === 0) {
			$page = self::create_payment_page();
			update_option('subscriptions_payment_page', $page);
		}

		if (self::page_exists(get_option('subscriptions_terms_page', 'terms-and-conditions')) === 0) {
			$page = self::create_terms_page();
			update_option('subscriptions_terms_page', $page);
		}
		if (self::page_exists(get_option('donations', 'donations')) === 0) {
			$page = self::create_donations_page();
			update_option('donations', $page);
		}
	}
	/**
	 * Subscriptions loop
	 */
	public static function create_subscriptions_loop()
	{
		$args = [
			'post_title' => __('Subscriptions', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the subscription template, please modify the content in your-theme/subscriptions-theme/subscriptions-loop.php',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Thanks you
	 */
	public static function create_thanks_page()
	{
		$args = [
			'post_title' => __('Thank you', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the subscription template, please modify the content in your-theme/subscriptions-theme/subscriptions-thankyou.php',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Loginpage
	 */
	public static function create_login_register_page()
	{
		$args = [
			'post_title' => __('Sign In', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the login and register template, please modify the content in your-theme/subscriptions-theme/auth/suscription-login.php',
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Register page
	 */
	public static function create_register_page()
	{
		$args = [
			'post_title' => __('Sign Up', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the login and register template, please modify the content in your-theme/subscriptions-theme/auth/subscriptions-register.php',
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Lost password page
	 */
	public static function create_lost_password_page()
	{
		$args = [
			'post_title' => __('Lost Password', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the lost password template, please modify the content in your-theme/subscriptions-theme/auth/lost-password.php',
		];

		$page = wp_insert_post($args);
		return $page;
	}

	/**
	 * Payment page
	 */
	static public function create_payment_page()
	{
		$args = [
			'post_title' => __('Payment page', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the payement getway options template, please modify the content in your-theme/subscriptions-theme/pages/suscription-payment-page.php',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Terms and conditions page
	 */
	static public function create_terms_page()
	{
		$args = [
			'post_title' => __('Terms and conditions', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'You must put the terms and condition text into this page.',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
	/**
	 * Donations pages
	 */
	static public function create_donations_page()
	{
		$args = [
			'post_title' => __('Donations', 'subscriptions'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'Donations page, please modify the content in your-theme/subscriptions-theme/pages/suscription-payment-page.php',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
}
