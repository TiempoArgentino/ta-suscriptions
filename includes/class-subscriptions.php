<?php

/**

 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Subscriptions {


	protected $loader;


	protected $plugin_name;

	
	protected $version;


	public function __construct() {
		if ( defined( 'SUSCRIPTIONS_VERSION' ) ) {
			$this->version = SUSCRIPTIONS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'subscriptions';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();


	}

	
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-subscriptions-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-subscriptions-i18n.php';

		/**
		 * Status and emails
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'emails/class-subscriptions-status-email.php';
		
		/**
		 * Emails
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'emails/class-subscriptions-emails.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-admin.php';

		/**
		 * Registers subscriptions post type
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-entities.php';

		/**
		 * Metaboxes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-metaboxes.php';

		/**
		 * Menu in administration area
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-menu.php';

		/**
		 * Users
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscriptions-users.php';

		

		/**
		 * Other Options
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) .'admin/class-subscriptions-options.php';

		/**
		 * Front Messages Class
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'utils/subscriptions-utils.php';

		/**
		 * Payment class
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-subscriptions-getways.php';

		/**
		 * Payment class
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-subscriptions-order-handler.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-subscriptions-public.php';

		$this->loader = new Subscriptions_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Subscriptions_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Subscriptions_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Subscriptions_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Subscriptions_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Subscriptions_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

/** Initialize common class */
function TAR()
{
    return new Subscriptions_Proccess();
}
