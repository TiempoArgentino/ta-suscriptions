<?php

/**
 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Subscriptions_Public
{
	private $plugin_name;

	private $version;

	/**
	 * i like spaces and line breaks :)
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->subscriptions_load_templates();
		$this->subscriptions_forms();
		$this->subscriptions_process();
		$this->subscriptions_front();
		$this->subscriptions_panel();

		add_action('template_redirect',[$this,'hide_admin_bar']);
	}
	/**
	 * Or not?
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/subscriptions-public.css', array(), $this->version, 'all');
		if (isset(get_option('subscriptions_options_option_name')['load_bootstrap'])) {
			wp_enqueue_style($this->plugin_name . '_boostrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css', array(), $this->version, 'all');
		}
	}
	/**
	 * Js
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/subscriptions-public.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name.'-payment', plugin_dir_url(__FILE__) . 'js/subscriptions-payment.js', array('jquery'), $this->version, true);
		if (isset(get_option('subscriptions_options_option_name')['load_bootstrap_js'])) {
			wp_enqueue_script($this->plugin_name.'jsbootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js', array('jquery'), $this->version, true);
		}

		wp_localize_script($this->plugin_name, 'subscriptions_ajax_object', array(
			'pricesURL' => rest_url('subscriptions/v1/user-prices'),
		));
	}
	/**
	 * Forms functions
	 */
	public function subscriptions_forms()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-subscriptions-forms-auth.php';
	}
	/**
	 * Templates
	 */
	public function subscriptions_load_templates()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-subscriptions-templates.php';
	}
	/**
	 * Form proccess
	 */
	public function subscriptions_process()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-subscriptions-proccess.php';
	}
	/**
	 * Front
	 */
	public function subscriptions_front()
	{
		require_once plugin_dir_path( dirname(__FILE__) ) . 'public/class-subscriptions-front-functions.php';
		SF();
	}
	/**
	 * Donations
	 */
	public function donations_process()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-donations-process.php';
	}
	/**
	 * Users
	 */
	public function subscriptions_panel()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-subscriptions-user.php';
	}

	/**
	 * Admin bar
	 */
	public function hide_admin_bar()
	{
		if(is_user_logged_in()){
			$user = wp_get_current_user();
			$roles = ( array ) $user->roles;
			if(in_array(get_option('default_sucription_role'),$roles) || in_array(get_option('subscription_digital_role'),$roles)){
				show_admin_bar(false);
			}
		}
	}
}
