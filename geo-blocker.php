<?php

/**
 * @link              https://timersys.com
 * @since             1.0.0
 * @package           Geobl
 *
 * @wordpress-plugin
 * Plugin Name:       Geo Blocker
 * Plugin URI:        https://geotargetingwp/
 * Description:       Geo Blocker let you block access to your site based on geolocation
 * Version:           1.0.6.2
 * Author:            Damian Logghe
 * Author URI:        https://timersys.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       geobl
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GEOBL_VERSION', '1.0.6.2');
define( 'GEOBL_PLUGIN_FILE' , __FILE__);
define( 'GEOBL_DIR', dirname(__FILE__));
define( 'GEOBL_URL', plugin_dir_url(__FILE__));
define( 'GEOBL_PLUGIN_HOOK' , basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
if( !defined('GEOTROOT_PLUGIN_FILE'))
	define( 'GEOTROOT_PLUGIN_FILE', GEOBL_PLUGIN_FILE );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-geobl-activator.php
 */
function activate_geobl() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-geobl-activator.php';
	Geobl_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-geobl-deactivator.php
 */
function deactivate_geobl() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-geobl-deactivator.php';
	Geobl_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_geobl' );
register_deactivation_hook( __FILE__, 'deactivate_geobl' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-geobl.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_geobl() {

	return Geobl::instance();

}
$GLOBALS['geobl'] = run_geobl();
