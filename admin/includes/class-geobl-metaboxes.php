<?php

/**
 * The cpt metaboxes functionality of the plugin.
 *
 * @link       https://timersys.com
 * @since      1.0.0
 *
 * @package    Geobl
 * @subpackage Geobl/admin/includes
 */

/**
 * @subpackage Geobl/admin/includes
 * @author     Damian Logghe <damian@timersys.com>
 */
class Geobl_Metaboxes{

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
	 * Register the metaboxes for our cpt
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'geobl-rules',
			 __( 'Block Rules', 'geobl' ),
			[ $this, 'geobl_rules' ],
			'geobl_cpt',
			'normal',
			'core'
		);
		add_meta_box(
			'geobl-opts',
			 __( 'Block Options', 'geobl' ),
			[ $this, 'geobl_opts' ],
			'geobl_cpt',
			'normal',
			'core'
		);
	}

	/**
	 * Saves the post meta of redirections
	 * @since 1.0.0
	 */
	function  save_meta_options( $post_id ){

		// Verify that the nonce is set and valid.
		if ( !isset( $_POST['geobl_options_nonce'] ) || ! wp_verify_nonce( $_POST['geobl_options_nonce'], 'geobl_options' ) ) {
			return $post_id;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// same for ajax
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $post_id;
		}
		// same for cron
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return $post_id;
		}
		// same for posts revisions
		if ( is_int( wp_is_post_autosave( $post_id ) ) ) {
			return $post_id;
		}

		// can user edit this post?
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		$opts = $_POST['geobl'];
		unset( $_POST['geobl'] );

		$post = get_post($post_id);

		// sanitize settings
		$opts['whitelist']	 	    = $opts['whitelist']; // if we sanitize break lines are broken, we sanitize later
		$opts['exclude_se']         = absint( sanitize_text_field( $opts['exclude_se'] ) );
		$opts['block_message'] 	    =  $opts['block_message'] ;

		// save box settings
		update_post_meta( $post_id, 'geobl_options', apply_filters( 'geobl/metaboxes/sanitized_options', $opts ) );


		$keys_geot = apply_filters('geobl/metaboxes/keys_geot', ['country', 'country_region', 'city', 'city_region', 'state', 'zip']);

		// Start with rules
		if( isset($_POST['geobl_rules']) && is_array($_POST['geobl_rules']) ) {

			// clean array keys
			$groups = array_values( $_POST['geobl_rules'] );
			unset( $_POST['geobl_rules'] );

			$output_groups = [];

			foreach($groups as $group_id => $group ) {
				if( is_array($group) ) {

					$output_geot = [];
					$group_wkey = array_values( $group );

					foreach( $group_wkey as $item_key => $items ) {
						if( in_array($items['param'], $keys_geot) )
							$output_geot[] = $items;
						else
							$output_groups[$group_id][] = $items;
					}

					if( count($output_geot) > 0 ) {
						foreach($output_geot as $item_geot)
							$output_groups[$group_id][] = $item_geot;
					}
				}
			}

			update_post_meta( $post_id, 'geobl_rules', apply_filters( 'geobl/metaboxes/sanitized_rules', $output_groups ) );
		}
	}

    /**
     * Include the metabox view for rules
     * @param  object $post    spucpt post object
     * @param  array $metabox full metabox items array
     * @since 1.0.0
     */
    public function geobl_rules( $post, $metabox ) {

	    $groups = apply_filters('geobl/metaboxes/get_rules', Geobl_Helper::get_rules( $post->ID ), $post->ID);

        include GEOBL_DIR . '/admin/partials/metaboxes/rules.php';
    }

    /**
     * Include the metabox view for opts
     * @param  object $post    geoblcpt post object
     * @param  array $metabox full metabox items array
     * @since 1.0.0
     */
    public function geobl_opts( $post, $metabox ) {

        $opts = apply_filters('geobl/metaboxes/get_options', Geobl_Helper::get_options( $post->ID ), $post->ID);

        include GEOBL_DIR . '/admin/partials/metaboxes/opts.php';
    }

}
