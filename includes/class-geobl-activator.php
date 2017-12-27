<?php

/**
 * Fired during plugin activation
 *
 * @link       https://timersys.com
 * @since      1.0.0
 *
 * @package    Geobl
 * @subpackage Geobl/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Geobl
 * @subpackage Geobl/includes
 * @author     Damian Logghe <damian@timersys.com>
 */
class Geobl_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		GeotFunctions\add_countries_to_db();
		do_action('geotWP/activated');
	}

}
