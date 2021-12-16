<?php

/**
 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Subscriptions_Admin {

	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->requires();

		$this->requires();

	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscriptions-admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/subscriptions-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function requires()
	{
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-memberships-actions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-prices.php';
	}

}