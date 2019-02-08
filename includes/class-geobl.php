<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://timersys.com
 * @since      1.0.0
 *
 * @package    Geobl
 * @subpackage Geobl/includes
 */
use GeotFunctions\Setting\GeotSettings;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 *
 * @since      1.0.0
 * @package    Geobl
 * @subpackage Geobl/includes
 * @author     Damian Logghe <damian@timersys.com>
 */
class Geobl {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Geobl_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Public Class instance
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Geobl_Public    $public Public class instance
	 */
	public $public;

	/**
	 * Admin Class instance
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      Geobl_Admin    $public Admin class instance
	 */
	public $admin;

	/**
	 * Plugin Instance
	 * @since 1.0.0
	 * @var The Fbl plugin instance
	 */
	protected static $_instance = null;
	public $settings;

	/**
	 * Main plugin_name Instance
	 *
	 * Ensures only one instance of WSI is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Geobl()
	 * @return plugin_name - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wsi' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wsi' ), '2.1' );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param mixed $key
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'payment_gateways', 'shipping', 'mailer', 'checkout' ) ) ) {
			return $this->$key();
		}
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'geobl';
		$this->version = GEOBL_VERSION;

		$this->load_dependencies();
		GeotSettings::init();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_global_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-geobl-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-geobl-rules.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-geobl-helper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-geobl-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-geobl-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-geobl-metaboxes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-geobl-public.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Geobl_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Geobl_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

	}

	/**
	 * Register all of the hooks that run globally
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_global_hooks() {

		add_action( 'init', array( $this, 'register_cpt' ) );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->admin = new Geobl_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->settings = new Geobl_Settings();
		$metaboxes = new Geobl_Metaboxes( $this->get_plugin_name(), $this->get_version() );

		Geobl_Rules::set_rules_fields();

		add_filter( 'plugin_action_links_' . GEOBL_PLUGIN_HOOK, array( $this->admin, 'add_action_links' ) );
		add_action( 'add_meta_boxes_geobl_cpt', array( $metaboxes, 'add_meta_boxes' ) );
		add_action( 'save_post_geobl_cpt', array( $metaboxes, 'save_meta_options' ) );

		add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_scripts' ) );

		//AJAX Actions
		add_action('wp_ajax_geobl/field_group/render_rules', array( 'Geobl_Helper', 'ajax_render_rules' ) );
		add_action('wp_ajax_geobl/field_group/render_operator', array( 'Geobl_Helper', 'ajax_render_operator' ) );

		// License and Updates
		add_action( 'admin_init' , [ $this->admin, 'handle_updates'], 0 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->public = new Geobl_Public();
		$action_hook = defined('WP_CACHE') ? 'init' : 'wp';

		if( ! is_admin() && ! $this->is_backend() && ! $this->is_builder() &&
			! defined('DOING_AJAX') && ! defined('DOING_CRON')
		) add_action( $action_hook, array( $this->public, 'handle_blockers' ) );

		add_action( 'wp_ajax_nopriv_geo_blocks', array( $this->public, 'handle_ajax_blockers' ),1 );
		add_action( 'wp_ajax_geo_blocks', array( $this->public, 'handle_ajax_blockers' ),1 );

		add_action( 'wp_ajax_geo_template', array( $this->public, 'view_template' ),1 );
	}

	/**
	 * Check if we are trying to login
	 * @return bool
	 */
	private function is_backend(){
		$ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
		return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF']== '/wp-login.php');
	}


	/**
	 * Check if is a builder ( Elementor/Divi/Gutemberg )
	 * @return bool
	 */
	private function is_builder() {

		// is Elementor
		if ( isset( $_GET['elementor-preview'] ) && is_numeric( $_GET['elementor-preview'] ) )
			return true;

		// is DIVI
		if( isset( $_GET['et_fb'] ) && is_numeric( $_GET['et_fb'] ) )
			return true;

		// is Gutemberg
		if( isset( $_GET['_locale'] ) && $_GET['_locale'] == 'user' )
			return true;

		return false;
	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register custom post types
	 * @since     1.0.0
	 * @return void
	 */
	public function register_cpt() {

		$labels = array(
			'name'               => 'Geo Blocker v'.GEOBL_VERSION,
			'singular_name'      => _x( 'Geo Blocker', 'post type singular name', 'popups' ),
			'menu_name'          => _x( 'Geo Blocker', 'admin menu', 'popups' ),
			'name_admin_bar'     => _x( 'Geo Blocker', 'add new on admin bar', 'popups' ),
			'add_new'            => _x( 'Add New', 'Geo Block', 'popups' ),
			'add_new_item'       => __( 'Add New Geo Block', 'popups' ),
			'new_item'           => __( 'New Geo Block', 'popups' ),
			'edit_item'          => __( 'Edit Geo Block', 'popups' ),
			'view_item'          => __( 'View Geo Block', 'popups' ),
			'all_items'          => __( 'Geo Blocker', 'popups' ),
			'search_items'       => __( 'Search Geo Block', 'popups' ),
			'parent_item_colon'  => __( 'Parent Geo Block:', 'popups' ),
			'not_found'          => __( 'No Geo Blocks found.', 'popups' ),
			'not_found_in_trash' => __( 'No Geo Blocks found in Trash.', 'popups' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'exclude_from_search'=> true,
			'show_ui'            => true,
			'show_in_menu'       => 'geot-settings',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'geobl_cpt' ),
			'capability_type'    => 'post',
			'capabilities' => array(
		        'publish_posts' 		=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'edit_posts' 			=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'edit_others_posts' 	=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'delete_posts' 			=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'delete_others_posts' 	=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'read_private_posts' 	=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'edit_post' 			=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'delete_post' 			=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		        'read_post' 			=> apply_filters( 'geobl/settings_page/roles', 'manage_options'),
		    ),
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 10,
			'supports'           => array( 'title' )
		);

		register_post_type( 'geobl_cpt', $args );

	}

}
