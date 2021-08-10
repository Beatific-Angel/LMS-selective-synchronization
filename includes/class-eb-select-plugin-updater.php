<?php
/**
 * Allows plugins to use their own update API.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Selective_Sync
 * @subpackage Selective_Sync/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\includes;

if ( ! class_exists( 'Eb_Select_Plugin_Updater' ) ) {
	/**
	 * Handles plugin update.
	 */
	class Eb_Select_Plugin_Updater {

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $api_url    plugin api url.
		 */
		private $api_url = '';

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $api_data  api data .
		 */
		private $api_data = array();

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $name  name .
		 */
		private $name = '';

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $slug  slug .
		 */
		private $slug = '';

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $response_data  response_data .
		 */
		private static $response_data;

		/**
		 * Class constructor.
		 *
		 * @uses plugin_basename()
		 * @uses hook()
		 *
		 * @param string $_api_url The URL pointing to the custom API endpoint.
		 * @param string $_plugin_file Path to the plugin file.
		 * @param array  $_api_data Optional data to send with API calls.
		 * @return void
		 */
		public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
			$this->api_url  = trailingslashit( $_api_url );
			$this->api_data = urlencode_deep( $_api_data );
			$this->name     = plugin_basename( $_plugin_file );
			$this->slug     = basename( $_plugin_file, '.php' );
			$this->version  = $_api_data['version'];
			// Set up hooks.
			$this->hook();
		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 */
		private function hook() {
			add_filter(
				'pre_set_site_transient_update_plugins',
				array(
					$this,
					'pre_set_site_transient_update_plugins_filter',
				)
			);
			add_filter(
				'pre_set_transient_update_plugins',
				array(
					$this,
					'pre_set_site_transient_update_plugins_filter',
				)
			);
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update api just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 * @return array Modified update array with custom plugin data.
		 */
		public function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
			if ( empty( $_transient_data ) ) {
							return $_transient_data;
			}

			$to_send = array( 'slug' => $this->slug );

			$api_response = $this->api_request( $to_send );

			if ( false !== $api_response && is_object( $api_response ) && isset( $api_response->new_version ) ) {
				if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {
					$_transient_data->response[ $this->name ] = $api_response;
				}
			}
				return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed  $_data data.
		 * @param string $_action action.
		 * @param object $_args args.
		 * @return object $_data data.
		 */
		public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
			if ( ( 'plugin_information' !== $_action ) || ! isset( $_args->slug ) || ( $_args->slug !== $this->slug ) ) {
				return $_data;
			}
			$to_send          = array( 'slug' => $this->slug );
				$api_response = $this->api_request( 'plugin_information', $to_send );
			if ( false !== $api_response ) {
				$_data = $api_response;
			}
			return $_data;
		}

		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 * @param array $_data Parameters for the API action.
		 * @return false||object
		 */
		private function api_request( $_data ) {
			if ( null !== self::$response_data ) {
				return self::$response_data;
			}

			$data = array_merge( $this->api_data, $_data );

			if ( $data['slug'] !== $this->slug ) {
				return;
			}

			if ( empty( $data['license'] ) ) {
				return;
			}

			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => $data['license'],
				'name'       => $data['item_name'],
				'slug'       => $this->slug,
				'author'     => $data['author'],
			);
			$request    = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if ( $request && isset( $request->sections ) ) {
					$request->sections = maybe_unserialize( $request->sections );
				}
				self::$response_data = $request;
				return $request;
			} else {
				self::$response_data = false;
				return false;
			}
		}
	}
}
