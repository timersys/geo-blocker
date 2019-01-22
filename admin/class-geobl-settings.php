<?php

/**
 * Class GeoLinks_Settings
 */
class Geobl_Settings {
	/**
	 * GeoLinks_Settings constructor.
	 */
	public function __construct() {
		add_filter( 'geot/settings_tabs', [$this, 'add_tab']);
		add_action( 'geot/settings_geo-blocker_panel', [ $this, 'settings_page'] );
		add_action( 'admin_init', [ $this, 'save_settings' ] );
	}

	/**
	 * Register tab for settings page
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_tab( $tabs ){
		$tabs['geo-blocker'] = ['name' => 'Geo Blocker'];
		return $tabs;
	}


	/**
	 * Settings page for plugin
	 * @since 1.0.3
	 */
	public function settings_page() {
		$defaults = [
			'ajax_mode'                 => '0',
		];
		$opts = wp_parse_args( geobl_settings(),  $defaults );

		include GEOBL_DIR . '/admin/partials/settings-page.php';
	}

	/**
	 * Save Settings page
	 * @since 1.0.3
	 */
	public function save_settings() {
		if (  isset( $_POST['geot_nonce'] ) && wp_verify_nonce( $_POST['geot_nonce'], 'geobl_save_settings' ) ) {
			$settings = isset($_POST['geobl_settings']) ? esc_sql( $_POST['geobl_settings'] ) : '';

			update_option( 'geobl_settings' ,  $settings);
		}
	}
}