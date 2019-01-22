<?php
//TODO add custom columsn to post type to show redirect settings
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://timersys.com
 * @since      1.0.0
 *
 * @package    Geobl
 * @subpackage Geobl/admin
 */
use GeotFunctions\GeotUpdates;

/**
 * @subpackage Geobl/admin
 * @author     Damian Logghe <damian@timersys.com>
 */
class Geobl_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $pagenow, $post;

		if ( get_post_type() !== 'geobl_cpt' || !in_array( $pagenow, array( 'post-new.php', 'edit.php', 'post.php' ) ) )
			return;

		$post_id = isset( $post->ID ) ? $post->ID : '';

		wp_enqueue_script( 'geobl-admin-js', plugin_dir_url( __FILE__ ) . 'js/geobl-admin.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_style( 'geobl-admin-css', plugin_dir_url( __FILE__ ) . 'css/geobl-admin.css', array(), $this->version, 'all' );

		wp_localize_script( 'geobl-admin-js', 'geobl_js',
				array(
					'admin_url' => admin_url( ),
					'nonce' 	=> wp_create_nonce( 'geobl_nonce' ),
					'l10n'		=> array (
							'or'	=> '<span>'.__('OR', 'geobl' ).'</span>'
						),
					'opts'      => Geobl_Helper::get_options($post_id)
				)
		);
	}


	/**
	 * Register direct access link
	 *
	 * @since    1.0.0
	 * @return 	Array
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=geobl_cpt' ) . '">' . __( 'Create geo blocker rule', 'geobl' ) . '</a>'
			),
			$links
		);

	}


	/**
	 * Handle Licences and updates
	 * @since 1.0.0
	 */
	public function handle_updates(){
		$opts = geot_settings();
		// Setup the updater
		return new GeotUpdates( GEOBL_PLUGIN_FILE, [
				'version'   => $this->version,
				'license'   => isset($opts['license']) ?$opts['license'] : ''
			]
		);
	}


}
