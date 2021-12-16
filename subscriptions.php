<?php

/**
 * Plugin Name:       Subscriptions
 * Plugin URI:        https://genosha.com.ar
 * Description:       This is a basic and extendabled plugin for subscriptions.
 * Version:           1.10.5
 * Author:            Juan Iriart
 * Author URI:        https://genosha.com.ar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt (this is important)
 * Text Domain:       subscriptions
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
	die;
}

/**
 * ok guys, this is super serius,
 * Subscriptions, is my first plugin really big, professional etc... bla bla 
 * So... i (or we) need, from you... please... test this plugin, let met see the errors or flaws
 * I insist ... I love you, thanks anyway
 */
define('SUSCRIPTIONS_VERSION', '1.10.5');
/**
 * if panel exist
 */
function panel_not_install(){
	if( !is_plugin_active( 'user-panel/user-panel.php' ) ) {
		printf('<div class="notice notice-error is-dismissible"> 
		<p><strong>'.__('Hi, you need install and activate User Panel Plugin','subscriptions').'.</strong></p>
		</div>');
	}
}
add_action( 'admin_notices', 'panel_not_install' );
/**
 * The code that runs during plugin activation.
 * This action is NOT documented
 */
function activate_subscriptions()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-subscriptions-activator.php';
	Subscriptions_Activator::activate();
}
/**
 * The code that runs during plugin deactivation.
 * This action is NOT documented
 */
function deactivate_subscriptions()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-subscriptions-deactivator.php';
	Subscriptions_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_subscriptions');
register_deactivation_hook(__FILE__, 'deactivate_subscriptions');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-subscriptions.php';

/**
 * Bla bla
 */
function run_subscriptions()
{

	$plugin = new Subscriptions();
	$plugin->run();
}
run_subscriptions();
