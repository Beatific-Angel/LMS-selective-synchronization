<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    SelectiveSync
 * @subpackage SelectiveSync/includes
 */

namespace ebSelectSync\includes;

use ebSelectSync\admin as eb_admin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SelectiveSync
 * @subpackage SelectiveSync/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */
class Selective_Sync {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SelectiveSyncLoader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Instance of the class.
	 *
	 * @var SelectiveSync The single instance of the class
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main SelectiveSync Instance
	 *
	 * Ensures only one instance of SelectiveSync is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see SelectiveSync()
	 * @return SelectiveSync - Main instance
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
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

		$this->plugin_name = 'selective_synchronization';
		$this->version     = '2.1.0';
		$this->define_constants();
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function define_constants() {

		// Plugin version.
		if ( ! defined( 'SELECTIVE_SYNC_VERSION' ) ) {
			define( 'SELECTIVE_SYNC_VERSION', $this->version );
		}

		// Plugin Folder URL.
		if ( ! defined( 'SELECTIVE_SYNC_PLUGIN_URL' ) ) {
			define( 'SELECTIVE_SYNC_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		}

		// Plugin Folder Path.
		if ( ! defined( 'SELECTIVE_SYNC_PLUGIN_DIR' ) ) {
			define( 'SELECTIVE_SYNC_PLUGIN_DIR', plugin_dir_path( dirname( __FILE__ ) ) );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SelectiveSyncLoader. Orchestrates the hooks of the plugin.
	 * - Selective_Sync_i18n. Defines internationalization functionality.
	 * - Selective_Sync_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		if ( ! is_admin() ) {
			$this->frontend_dependencies();
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/class-selective-sync-loader.php';

		$this->loader = new Selective_Sync_Loader();

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		// include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/class-bridge-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'admin/class-selective-sync-admin.php';

		/*
		 *The class responsible for defining all actions that occur for AJAX
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/class-eb-select-course-ajax-handler.php';

		/*
		 * Admin settings section
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'admin/settings/class-selective-synch-courses-settings.php';
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'admin/settings/class-selective-synch-users-settings.php';

		/*
		 * Load wp-list-table.
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/class-eb-select-users-list-table.php';

		/**
		 * Loads the generally used functions.
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/selective-synch-functions.php';

		/**
		 * Loads the generally used functions.
		 */
		include_once SELECTIVE_SYNC_PLUGIN_DIR . 'includes/class-eb-select-users-ajax-handler.php';
	}

	/**
	 * Public facing code.
	 *
	 * Include the following files that make up the plugin:
	 * - Selective_Sync_Shortcodes. Defines set of shortcode.
	 * - Bridge_Woo_Shortcode_Associated_Courses. Defines output for associated courses.
	 *
	 * @return void
	 * @since    1.0.0
	 * @access   private
	 */
	private function frontend_dependencies() {
		/**
		* Tha classes responsible for defining shortcodes & templates
		*/
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $eb_select_plg_data;

		include_once plugin_dir_path( __FILE__ ) . '/class-eb-select-get-plugin-data.php';

		$get_data_from_db = Eb_Select_Get_Plugin_Data::get_data_from_db( $eb_select_plg_data );
		if ( 'available' === $get_data_from_db ) {
			$plugin_admin = new eb_admin\Selective_Sync_Admin( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 15 );

			// Add Selective Course synchronization setting.

			// commented below 2 hooks as they are moved to the new page.

			// user action handler.
			$user_action_handler = new Eb_Select_Users_Ajax_Handler();

			$this->loader->add_action(
				'wp_ajax_selective_users_sync',
				$user_action_handler,
				'selective_users_creation_and_linking_ajax'
			);

			$this->loader->add_action(
				'wp_ajax_all_users_sync',
				$user_action_handler,
				'all_users_creation_and_linking_ajax'
			);

			// Action to sync selected courses.
			$ajax_handle_obj = new Eb_Select_Course_Ajax_Handler(
				$this->get_plugin_name(),
				$this->get_version()
			);

			$this->loader->add_action(
				'wp_ajax_selective_course_sync',
				$ajax_handle_obj,
				'selected_course_synchronization_initiater'
			);

			// Adding the setting related Hooks.
			$this->loader->add_filter(
				'eb_get_settings_pages',
				$plugin_admin,
				'add_selective_synch_tab',
				10,
				1
			);
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
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
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SelectiveSyncLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
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
}

/**
 * Returns the main instance of SelectiveSync to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return SelectiveSync
 */
function selective_sync() {
	return Selective_Sync::instance();
}
