<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://timersys.com
 * @since      1.0.0
 *
 * @package    Geobl
 * @subpackage Geobl/public
 */
use function GeotFunctions\textarea_to_array;
use function GeotWP\getUserIP;
use function GeotWP\is_session_started;

/**
 * @package    Geobl
 * @subpackage Geobl/public
 * @author     Damian Logghe <damian@timersys.com>
 */
class Geobl_Public {
	/**
	 * @var Array of Redirection posts
	 */
	private $blocks;

	public function handle_blockers(){

		Geobl_Rules::init();
		$this->blocks = $this->get_blocks();
		$opts = geobl_settings();
		if( ! empty( $opts['ajax_mode'] ) )
			add_action( 'wp_enqueue_scripts', [ $this,  'enqueue_scripts' ] );
		else
			$this->check_for_rules();
	}


	/**
	 * Check for rules and block if needed
	 * This will be normal behaviour on site where cache is not active
	 */
	private function check_for_rules() {
		if( !empty($this->blocks) ) {
			foreach ( $this->blocks as $r ) {
				if( ! $this->pass_basic_rules($r) )
					continue;
				$rules = !empty($r->geobl_rules) ? unserialize($r->geobl_rules) : array();
				$do_block = Geobl_Rules::do_block( $rules );
				if ( $do_block ) {
					$this->perform_block( $r );
					break;
				}
			}
		}
	}

	/**
	* Handle Ajax call for blocks, Basically
	 * we call normal block logic but cancel it and print results
	*/
	public function handle_ajax_blockers(){
		Geobl_Rules::init();
		$this->blocks = $this->get_blocks();
		$this->check_for_rules();
		die();
	}

	/**
	 * Grab all blocks posts and associated rules
	 * @return mixed
	 */
	private function get_blocks() {
		global $wpdb;

		$sql = "SELECT ID, 
		MAX(CASE WHEN pm1.meta_key = 'geobl_rules' then pm1.meta_value ELSE NULL END) as geobl_rules,
		MAX(CASE WHEN pm1.meta_key = 'geobl_options' then pm1.meta_value ELSE NULL END) as geobl_options
        FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID)  WHERE post_type='geobl_cpt' AND post_status='publish' GROUP BY p.ID";

		$blocks = wp_cache_get(md5($sql), 'geobl_posts');
		if( $blocks === false) {
			$blocks = $wpdb->get_results($sql, OBJECT );
			wp_cache_add (md5($sql), $blocks, 'geobl_posts');
		}
		return $blocks;
	}

	/**
	 * Before Even checking rules, we need some basic validation
	 *
	 * @param $block
	 *
	 * @return bool
	 */
	private function pass_basic_rules( $block ) {
		if( empty( $block->geobl_options ) )
			return false;

		$opts = maybe_unserialize($block->geobl_options);

		// check user IP
		if( !empty($opts['whitelist']) && $this->user_is_whitelisted( $opts['whitelist'] ) )
			return false;

		return true;
	}

	/**
	 * Perform the actual block
	 * @param $block
	 */
	private function perform_block( $block ) {
		$opts = maybe_unserialize($block->geobl_options);

		$opts['block_message'] = do_shortcode($opts['block_message']);
		//last chance to abort
		if( ! apply_filters('geobl/cancel_block', false, $opts, $block) ) {
			self::block_screen($block->ID, $opts['block_message']);
			die();
		}
	}
	/**
	 * Enqueue script file
	 */
	public function enqueue_scripts(){
		wp_enqueue_script( 'geobl-js',  plugins_url( 'js/geobl-public.js', __FILE__ ), array( 'jquery' ), GEOBL_VERSION, true );
		wp_localize_script( 'geobl-js', 'geobl', [
			'ajax_url'						=> admin_url('admin-ajax.php'),
			'pid'						    => get_queried_object_id(),
			'is_front_page'				    => is_front_page(),
			'is_category'				    => is_category(),
			'site_url'				        => site_url(),
			'is_archive'				    => is_archive(),
			'is_search'				        => is_search()
		]);
	}

	/**
	 * Check if current user IP is whitelisted
	 *
	 * @param $ips
	 *
	 * @return bool
	 */
	private function user_is_whitelisted( $ips ) {
		$ips = textarea_to_array( $ips );
		if( in_array( getUserIP(), apply_filters( 'geobl/whitelist_ips', $ips ) ) )
			return true;
		return false;
	}

	/**
	 * Print placeholder in front end
	 *
	 * @param $id
	 * @param $message
	 */
	public static function block_screen($id, $message){

		$args = ['message' => $message, 'id' => $id ];
		Geobl_Helper::include_template($args);
	}

	/**
	 * Print default template
	 *
	 * @param none
	 */
	public function view_template() {

		if(	isset($_GET['wp-nonce']) && wp_verify_nonce(  $_REQUEST['wp-nonce'], 'nonce-template' ) &&
			isset($_GET['id']) && is_numeric($_GET['id'])  ) {

			$opts = Geobl_Helper::get_options($_GET['id']);
			$args = array('message' => do_shortcode($opts['block_message']), 'id' => $_GET['id'] );

			Geobl_Helper::include_template($args);
		}
		die();
	}

}