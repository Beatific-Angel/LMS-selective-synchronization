<?php
/**
 * This file used to check the licensing data.
 *
 * @package    Selective_Sync
 * @subpackage Selective_Sync/admin
 */

namespace ebSelectSync\includes;

if ( ! class_exists( 'Eb_Select_Add_Plugin_Data_In_Db' ) ) {

	/**
	 *  Handle Licensing data.
	 */
	class Eb_Select_Add_Plugin_Data_In_Db {

		/**
		 * Plugin short name.
		 *
		 * @var string Short Name for plugin.
		 */
		public $plugin_short_name = '';

		/**
		 * Plugin slug.
		 *
		 * @var string Slug to be used in url and functions name
		 */
		public $plugin_slug = '';

		/**
		 * Plugin version.
		 *
		 * @var string stores the current plugin version
		 */
		public $plugin_version = '';

		/**
		 * Plugin name.
		 *
		 * @var string Handles the plugin name
		 */
		public $plugin_name = '';

		/**
		 * Plugin store url of the edwiser site.
		 *
		 * @var string  Stores the URL of store. Retrieves updates from
		 *              this store
		 */
		public $store_url = '';

		/**
		 * Author name which will.
		 *
		 * @var string  Name of the Author
		 */
		public $author_name = '';

		/**
		 * CONTSRUCTOR OF THE CLASS.
		 *
		 * @param string $plugin_data Plugin information.
		 */
		public function __construct( $plugin_data ) {

			$this->author_name       = $plugin_data['author_name'];
			$this->plugin_name       = $plugin_data['plugin_name'];
			$this->plugin_short_name = $plugin_data['plugin_short_name'];
			$this->plugin_slug       = $plugin_data['plugin_slug'];
			$this->plugin_version    = $plugin_data['plugin_version'];
			$this->store_url         = $plugin_data['store_url'];
		}

		/**
		 * Load all the required data.
		 */
		public function init_license() {
			add_filter( 'eb_setting_messages', array( $this, 'license_messages' ), 15, 1 );
			add_filter( 'eb_licensing_information', array( $this, 'license_information' ), 15, 1 );
			add_action( 'init', array( $this, 'add_data' ), 5 );
		}


		/**
		 * License information.
		 *
		 * @param string $licensing_info license information.
		 */
		public function license_information( $licensing_info ) {

			$renew_link = get_option( 'eb_' . $this->plugin_slug . '_product_site' );

			// Get License Status.
			$status = get_option( 'edd_' . $this->plugin_slug . '_license_status' );
			include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-get-plugin-data.php';

			$active_site = Eb_Select_Get_Plugin_Data::get_site_list( $this->plugin_slug );

			$display = '';
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			}

			$license_key = trim( get_option( 'edd_' . $this->plugin_slug . '_license_key' ) );

			// LICENSE KEY.
			if ( ( 'valid' === $status || 'expired' === $status ) && ( empty( $display ) || '' === $display ) ) {
				$license_key_html = '<input id="edd_' .
					$this->plugin_slug .
					'_license_key" name="edd_' .
					$this->plugin_slug .
					'_license_key" type="text" class="regular-text" value="' .
					esc_attr( $license_key ) .
					'" readonly/>'.
					wp_nonce_field("eb_emailtmpl_sec", "eb_emailtmpl_nonce");
			} else {
				$license_key_html = '<input id="edd_' .
					$this->plugin_slug .
					'_license_key" name="edd_' .
					$this->plugin_slug .
					'_license_key" type="text" class="regular-text" value="' .
					esc_attr( $license_key ) .
					'" />'.
					wp_nonce_field("eb_emailtmpl_sec", "eb_emailtmpl_nonce");
			}

			// LICENSE STATUS.

			/*added by wisdmlabs after psr2*/
			$license_status = $this->display_license_status( $status, $display );

			// Activate License Action Buttons.
			ob_start();
			wp_nonce_field( 'edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce' );
			$nonce = ob_get_contents();
			ob_end_clean();

			if ( false !== $status && 'valid' === $status ) {
				$buttons = '<input type="submit" class="button-primary" name="edd_' .
					$this->plugin_slug .
					'_license_deactivate" value="' .
					__( 'Deactivate License"', 'selective-synch-td' ) .
					'/>';
			} elseif ( 'expired' === $status && ( ! empty( $display ) || '' !== $display ) ) {
				$buttons  = '<input type = "submit" class = "button-primary" name = "edd_' .
					$this->plugin_slug .
					'_license_activate" value = "' .
					__( 'Activate License', 'selective-synch-td' ) .
					'"/>';
				$buttons .= ' <input type = "button" class = "button-primary" name = "edd_' .
					$this->plugin_slug .
					'_license_renew" value = "' .
					__( 'Renew License', 'selective-synch-td' ) .
					'" onclick = "window.open( \'' .
					$renew_link .
					'\')"/>';
			} elseif ( 'expired' === $status ) {
				$buttons  = '<input type="submit" class="button-primary" name="edd_' .
					$this->plugin_slug .
					'_license_deactivate" value="' .
					__( 'Deactivate License', 'selective-synch-td' ) .
					'"/>';
				$buttons .= ' <input type="button" class="button-primary" name="edd_' .
					$this->plugin_slug .
					'_license_renew" value="' .
					__( 'Renew License', 'selective-synch-td' ) .
					'" onclick="window.open( \'' .
					$renew_link .
					'\' )"/>';
			} else {
				$buttons = '<input type="submit" class="button-primary" name="edd_' .
					$this->plugin_slug .
					'_license_activate" value="' .
					__( 'Activate License', 'selective-synch-td' ) .
					'"/>';
			}

			$info = array(
				'plugin_name'      => $this->plugin_name,
				'plugin_slug'      => $this->plugin_slug,
				'license_key'      => $license_key_html,
				'license_status'   => $license_status,
				'activate_license' => $nonce . $buttons,
			);

			$licensing_info[] = $info;
			return $licensing_info;
		}

		/**
		 * Licensing messages.
		 *
		 * @param string $eb_licensing_msg eb licensing message.
		 */
		public function license_messages( $eb_licensing_msg ) {
			// Get License Status.
			$status = get_option( 'edd_' . $this->plugin_slug . '_license_status' );

			include_once plugin_dir_path( __FILE__ ) . 'class-eb-select-get-plugin-data.php';
			$status      = $this->get_licenses_global_status( $status );
			$active_site = Eb_Select_Get_Plugin_Data::get_site_list( $this->plugin_slug );
			$display     = '';
			$display     = $this->check_if_site_active( $active_site );

			if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) &&
				! isset( $_POST['eb_server_nr'] )
				) {
				// Handle Submission of inputs on license page.
				if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) &&
					empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] )
					) {
					// If empty, show error message.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: Enter licesing key */
							__(
								'Please enter license key for %s.',
								'selective-synch-td'
							),
							$this->plugin_name
						),
						'error'
					);
				} elseif ( 'server_did_not_respond' === $status ) {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							__(
								'No response from server. Please try again later.',
								'selective-synch-td'
							),
							$this->plugin_name
						),
						'error'
					);
				} elseif ( 'item_name_mismatch' === $status ) {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							__(
								'License key is not valid. Please check your license key and try again',
								'selective-synch-td'
							),
							$this->plugin_name
						),
						'error'
					);
				} elseif ( false !== $status && 'valid' === $status ) { // Valid license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: Enter licesing key */
							__( 'License key for %s is activated.', 'ebbp-textdomain' ),
							$this->plugin_name
						),
						'updated'
					);
				} elseif ( false !== $status && 'expired' === $status &&
						( ! empty( $display ) || '' !== $display ) ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: Enter licesing key */
							__(
								'License key for %1$s have been Expired. Please, Renew it. 
								<br/>Your License Key is already activated at : %2$s',
								'ebbp-textdomain'
							),
							$this->plugin_name,
							$display
						),
						'error'
					);
				} elseif ( false !== $status && 'expired' === $status ) { // Expired license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: License key expiration */
							__(
								'License key for %s have been Expired. Please, Renew it.',
								'ebbp-textdomain'
							),
							$this->plugin_name
						),
						'error'
					);
				} elseif ( false !== $status &&
						'disabled' === $status ) { // Disabled license key.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: Disabled license key */
							__( 'License key for %s is Disabled.', 'ebbp-textdomain' ),
							$this->plugin_name
						),
						'error'
					);
				} elseif ( 'no_activations_left' === $status ) { // Invalid license key   and site.
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: Already activated license key */
							__(
								'License Key for %1$s is already activated at : %2$s',
								'ebbp-textdomain'
							),
							$this->plugin_name,
							$display
						),
						'error'
					);
				} else {
					$this->invalid_status_messages( $status, $display );
				}
			}

			ob_start();
			settings_errors( 'eb_' . $this->plugin_slug . '_errors' );
			$ss_setting_messages = ob_get_contents();
			ob_end_clean();
			return $eb_licensing_msg . $ss_setting_messages;
		}



		/**
		 * Check if site is active.
		 *
		 * @param string $status  status of the site.
		 * @param string $display  display.
		 */
		public function invalid_status_messages( $status, $display ) {
			if ( 'invalid' === $status && ( ! empty( $display ) || '' !== $display ) ) { // Invalid license key   and site.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf(
							/* translators: already activated license key */
						__(
							'License Key for %1$s is already activated at : %2$s',
							'selective-synch-td'
						),
						$this->plugin_name,
						$display
					),
					'error'
				);
			} elseif ( 'invalid' === $status ) { // Invalid license key.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf(
							/* translators: Valid license key */
						__(
							'Please enter valid license key for %s.',
							'selective-synch-td'
						),
						$this->plugin_name
					),
					'error'
				);
			} elseif ( 'site_inactive' === $status ) { // Invalid license key and site inactive.
				if ( ( ! empty( $display ) || '' !== $display ) ) {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						sprintf(
							/* translators: already activated license key */
							__(
								'License Key for %1$s is already activated at : %2$s',
								'selective-synch-td'
							),
							$this->plugin_name,
							$display
						),
						'error'
					);
				} else {
					add_settings_error(
						'eb_' . $this->plugin_slug . '_errors',
						esc_attr( 'settings_updated' ),
						__(
							'Site inactive(Press Activate license to activate plugin)',
							'selective-synch-td'
						),
						'error'
					);
				}
			} elseif ( 'deactivated' === $status ) { // Site is inactive.
				add_settings_error(
					'eb_' . $this->plugin_slug . '_errors',
					esc_attr( 'settings_updated' ),
					sprintf(
						/* translators: already activated license key */
						__(
							'License Key for %s is deactivated',
							'selective-synch-td'
						),
						$this->plugin_name
					),
					'updated'
				);
			}
		}


		/**
		 * Check if site is active.
		 *
		 * @param string $active_site  status of the site.
		 */
		public function check_if_site_active( $active_site ) {
			if ( ! empty( $active_site ) || '' !== $active_site ) {
				$display = '<ul>' . $active_site . '</ul>';
			} else {
				$display = '';
			}
			return $display;
		}


		/**
		 * Added by wisdmlabs after psr2.
		 *
		 * @param string $status  Status of the license.
		 * @param string $display Display.
		 */
		public function display_license_status( $status, $display ) {
			if ( false !== $status && 'valid' === $status ) {
				$license_status = '<span style="color:green;">' .
				__( 'Active', 'selective-synch-td' ) .
				'</span>';
			} elseif ( 'site_inactive' === get_option(
				'edd_' . $this->plugin_slug . '_license_status'
			) ) {
				$license_status = '<span style="color:red;">' .
				__( 'Not Active', 'selective-synch-td' ) .
				'</span>';
			} elseif ( 'expired' === get_option(
				'edd_' . $this->plugin_slug . '_license_status'
			) ) {
				$license_status = '<span style="color:red;">' .
				__( 'Expired', 'selective-synch-td' ) .
				'</span>';
			} elseif ( 'invalid' === get_option(
				'edd_' . $this->plugin_slug . '_license_status'
			) ) {
				$license_status = '<span style="color:red;">' .
				__( 'Invalid Key', 'selective-synch-td' ) .
				'</span>';
			} else {
				$license_status = '<span style="color:red;">' .
				__( 'Not Active ', 'selective-synch-td' ) .
				'</span>';
			}
			unset( $display );
			return $license_status;
		}



		/**
		 * Updates license status in the database and returns status value.
		 *
		 * @param object $license_data License data returned from server.
		 * @param  string $plugin_slug  Slug of the plugin. Format of the key in options table is 'edd_<$plugin_slug>_license_status'.
		 *
		 * @return string              Returns status of the license
		 */
		public static function update_status( $license_data, $plugin_slug ) {
			$status = '';

			if ( isset( $license_data->success ) ) {
				// Check if request was successful.
				if ( false === $license_data->success ) {
					if ( ! isset( $license_data->error ) || empty( $license_data->error ) ) {
						$license_data->error = 'invalid';
					}
				}
				// Is there any licensing related error?
				$status = self::check_licensing_error( $license_data );

				if ( ! empty( $status ) ) {


					update_option( 'edd_' . $plugin_slug . '_license_status', $status );

					return $status;
				}
				$status = 'invalid';
				// Check license status retrieved from EDD.
				$status = self::check_license_status( $license_data, $plugin_slug );
			}

			$status = ( empty( $status ) ) ? 'invalid' : $status;
			update_option( 'edd_' . $plugin_slug . '_license_status', $status );
			return $status;
		}



		/**
		 * Checks if there is any error in response.
		 *
		 * @param object $license_data License Data obtained from server.
		 *
		 * @return string empty if no error or else error
		 */
		public static function check_licensing_error( $license_data ) {
			$status = '';
			if ( isset( $license_data->error ) && ! empty( $license_data->error ) ) {
				switch ( $license_data->error ) {
					case 'revoked':
						$status = 'disabled';
						break;
					case 'expired':
						$status = 'expired';
						break;
					case 'item_name_mismatch':
						$status = 'item_name_mismatch';
						break;
					case 'no_activations_left':
						$status = 'no_activations_left';
						break;
				}
			}
			return $status;
		}

		/**
		 * Check license status.
		 *
		 * @param string $license_data license data.
		 * @param string $plugin_slug plugin Slug data.
		 */
		public static function check_license_status( $license_data, $plugin_slug ) {
			$status = 'invalid';
			if ( isset( $license_data->license ) && ! empty( $license_data->license ) ) {
				switch ( $license_data->license ) {
					case 'invalid':
						$status = 'invalid';
						if ( isset( $license_data->activations_left ) && '0' === $license_data->activations_left ) {
							include_once plugin_dir_path( __FILE__ ) . 'class-wdm-wusp-get-data.php';
							$active_site = Eb_Select_Get_Plugin_Data::get_site_list( $plugin_slug );
							if ( ! empty( $active_site ) || '' !== $active_site ) {
								$status = 'no_activations_left';
							}
						}
						break;
					case 'failed':
						$status                                   = 'failed';
						$GLOBALS['wdm_license_activation_failed'] = true;
						break;
					default:
						$status = $license_data->license;
				}
			}
			return $status;
		}


		/**
		 * Get license global status.
		 *
		 * @param string $status status.
		 */
		private function get_licenses_global_status( $status ) {
			if ( isset( $GLOBALS['wdm_server_null_response'] ) &&
			true === $GLOBALS['wdm_server_null_response'] ) {
				$status = 'server_did_not_respond';
			} elseif ( isset( $GLOBALS['wdm_license_activation_failed'] ) &&
			true === $GLOBALS['wdm_license_activation_failed'] ) {
				$status = 'license_activation_failed';
			} elseif ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) &&
				empty( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] ) ) {
				$status = 'no_license_key_entered';
			}
			return $status;
		}



		/**
		 * Add data.
		 */
		public function add_data() {
			if ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_activate' ] ) ) {
				if ( ! check_admin_referer(
					'edd_' . $this->plugin_slug . '_nonce',
					'edd_' . $this->plugin_slug . '_nonce'
				) ) {
					return;
				}

				$this->activate_license();
			} elseif ( isset( $_POST[ 'edd_' . $this->plugin_slug . '_license_deactivate' ] ) ) {
				if ( ! check_admin_referer(
					'edd_' . $this->plugin_slug . '_nonce',
					'edd_' . $this->plugin_slug . '_nonce'
				) ) {
					return;
				}

				$this->deactivate_license();
			}
		}


		/**
		 * Activate license.
		 */
		public function activate_license() {
			$license_key = trim( $_POST[ 'edd_' . $this->plugin_slug . '_license_key' ] );

			if ( $license_key ) {
				update_option( 'edd_' . $this->plugin_slug . '_license_key', $license_key );
				$api_params = array(
					'edd_action'      => 'activate_license',
					'license'         => $license_key,
					'item_name'       => urlencode( $this->plugin_name ),
					'current_version' => $this->plugin_version,
				);

				$response = wp_remote_get(
					add_query_arg( $api_params, $this->store_url ),
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'blocking'  => true,
					)
				);

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				$valid_response_code   = array( '200', '301' );
				$current_response_code = wp_remote_retrieve_response_code( $response );

				$is_data_available = $this->check_if_no_data( $license_data, $current_response_code, $valid_response_code );

				if ( false === $is_data_available ) {
					return;
				}

				$expiration_time = $this->get_expiration_time( $license_data );
				$current_time    = time();

				if ( isset( $license_data->expires ) &&
					( false !== $license_data->expires ) &&
					( 'lifetime' !== $license_data->expires ) &&
					$expiration_time <= $current_time && 0 !== $expiration_time &&
					! isset( $license_data->error ) ) {
					$license_data->error = 'expired';
				}

				if ( isset( $license_data->renew_link ) && ( ! empty( $license_data->renew_link ) || '' !== $license_data->renew_link ) ) {
					update_option( 'eb_' . $this->plugin_slug . '_product_site', $license_data->renew_link );
				}

				$this->update_number_of_sites_using_license( $license_data );

				$license_status = self::update_status( $license_data, $this->plugin_slug );
				$this->set_transient_on_activation( $license_status );
			}
		}


		/**
		 * Deactivate license.
		 */
		public function deactivate_license() {
			$wpep_license_key = trim( get_option( 'edd_' . $this->plugin_slug . '_license_key' ) );

			if ( $wpep_license_key ) {
				$api_params = array(
					'edd_action'      => 'deactivate_license',
					'license'         => $wpep_license_key,
					'item_name'       => urlencode( $this->plugin_name ),
					'current_version' => $this->plugin_version,
				);

				$response = wp_remote_get(
					add_query_arg( $api_params, $this->store_url ),
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'blocking'  => true,
					)
				);

				if ( is_wp_error( $response ) ) {
					return false;
				}

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				$valid_response_code   = array( '200', '301' );
				$current_response_code = wp_remote_retrieve_response_code( $response );
				$is_data_available     = $this->check_if_no_data( $license_data, $current_response_code, $valid_response_code );

				if ( false === $is_data_available ) {
					return;
				}

				if ( 'deactivated' === $license_data->license || 'failed' === $license_data->license ) {
					update_option( 'edd_' . $this->plugin_slug . '_license_status', 'deactivated' );
				}
				delete_transient( 'eb_' . $this->plugin_slug . '_license_trans' );
				set_transient( 'eb_' . $this->plugin_slug . '_license_trans', $license_data->license, 0 );
			}
		}


		/**
		 * Set transient.
		 *
		 * @param string $license_status License status.
		 */
		public function set_transient_on_activation( $license_status ) {
			$trans_var = get_transient( 'eb_' . $this->plugin_slug . '_license_trans' );
			if ( isset( $trans_var ) ) {
				delete_transient( 'eb_' . $this->plugin_slug . '_license_trans' );
				if ( ! empty( $license_status ) ) {
					if ( 'valid' === $license_status ) {
						$time = 60 * 60 * 24 * 7;
					} else {
						$time = 60 * 60 * 24;
					}
					set_transient( 'eb_' . $this->plugin_slug . '_license_trans', $license_status, $time );
				}
			}
		}


		/**
		 * Update number of sites using license.
		 *
		 * @param string $license_data license data.
		 */
		public function update_number_of_sites_using_license( $license_data ) {
			if ( isset( $license_data->sites ) && ( ! empty( $license_data->sites ) || '' !== $license_data->sites ) ) {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_sites', $license_data->sites );
				update_option( 'eb_' . $this->plugin_slug . '_license_max_site', $license_data->license_limit );
			} else {
				update_option( 'eb_' . $this->plugin_slug . '_license_key_sites', '' );
				update_option( 'eb_' . $this->plugin_slug . '_license_max_site', '' );
			}
		}



		/**
		 * Checks if any response received from server or not after making an API call.
		 * If no response obtained, then sets next api request after 24 hours.
		 *
		 * @param object $license_data         License Data obtained from server.
		 * @param  string $current_response_code    Response code of the API request.
		 * @param  array  $valid_response_code      Array of acceptable response codes.
		 *
		 * @return bool returns false if no data obtained. Else returns true.
		 */
		public function check_if_no_data( $license_data, $current_response_code, $valid_response_code ) {

			if ( null === $license_data || ! in_array( $current_response_code, $valid_response_code ) ) {
				$GLOBALS['wdm_server_null_response'] = true;
				set_transient( 'eb_' . $this->plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24 );
				return false;
			}

			return true;
		}

		/**
		 * Get expiration time.
		 *
		 * @param object $license_data         License Data obtained from server.
		 * * @return bool returns false if no data obtained. Else returns true.
		 */
		public function get_expiration_time( $license_data ) {
			$expiration_time = 0;
			if ( isset( $license_data->expires ) ) {
				$expiration_time = strtotime( $license_data->expires );
			}

			return $expiration_time;
		}
	}
}
