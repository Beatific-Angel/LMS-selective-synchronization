<?php
/**
 * Selective Synchronization course settings
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @package    Selective Synchronization
 * @subpackage Selective Synchronization/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Selective_Synch_Users_Settings' ) ) {
	/**
	 * Selective synch user settings.
	 */
	class Selective_Synch_Users_Settings {
		/**
		 * Function used to set the settings array which will be displayed on the users settings page.
		 *
		 * @return array of the settings.
		 */
		public function get_settings() {
			$settings = array();

			if ( eb_ss_test_connection() ) {
				echo '<p class="eb-dtable-error">';
				esc_html_e( 'There is a problem while connecting to moodle server. Please, check your moodle connection or try', 'selective-synch-td' ) . '<a href="javascript:history.go(0)" style="cursor:pointer;">' . esc_html_e( ' reloading', 'selective-synch-td' ) . '</a>' . esc_html_e(' the page.', 'selective-synch-td' );
				echo '</p>';
			} else {
				$settings = apply_filters(
					'selective_synch_users_settings',
					array(
						array(
							'title' => __( 'Synchronize Moodle Users', 'selective-synch-td' ),
							'type'  => 'title',
							'class' => 'eb-ss-user-heading',
							'id'    => 'selective_synch_all_users_options',
						),
						array(
							'title'    => __( 'Create Existing Moodle Users', 'selective-synch-td' ),
							'desc'     => __( 'Check to create user accounts for all Moodle users in your WordPress site ', 'selective-synch-td' ),
							'id'       => 'selective_synch_create_all_users',
							'default'  => 'no',
							'type'     => 'checkbox',
							'autoload' => false,
						),
						array(
							'title'    => __( 'Link all Moodle users', 'selective-synch-td' ),
							'desc'     => __( 'This will link all processed Moodle users enrollment data.', 'selective-synch-td' ),
							'id'       => 'selective_synch_link_all_users',
							'default'  => 'no',
							'type'     => 'checkbox',
							'autoload' => false,
						),
						array(
							'title'    => '',
							'desc'     => '',
							'id'       => 'eb-ss-all-users-submit',
							'default'  => __( 'Submit', 'selective-synch-td' ),
							'type'     => 'button',
							'desc_tip' => false,
							'class'    => 'button secondary',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'selective_synch_all_users_options',
						),
						array(
							'title' => __( 'Synchronize Selective Users', 'selective-synch-td' ),
							'type'  => 'title',
							'class' => 'eb-ss-user-heading eb-ss-all-users-heading',
							'id'    => 'selective_synch_selective_users_options',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'selective_synch_selective_users_options',
						),
						array(
							// custom type to render the table on the users synchronization settings.
							'type' => 'selective_synch_list_table',
						),
					)
				);
			}
			return $settings;
		}

		/**
		 * This displays the Wp-list-table for the users.
		 */
		public function get_users_table() {
			$list_table = new \ebSelectSync\includes\Eb_Select_Users_List_Table();
			$list_table->prepare_items();

			?>

			<div class='eb-ss-user-settings-wrap'>
			<!-- Dialog background color -->
				<div class="eb-ss-dialog-overlay"></div>

				<!-- pop-up html started  -->
				<div class="eb-ss-user-pop-up-cont">
					<div class="eb-ss-users-migration-error-tbl-wrap" title="<?php esc_html_e( 'Users List' ); ?>">
						<table class="eb-ss-users-migration-error-tbl hover">
							<thead>
								<th>
									<?php esc_html_e( 'username', 'selective-synch-td' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Email', 'selective-synch-td' ); ?>
								</th>
							</thead>

							<tbody class="">

							</tbody>

						</table>
					</div>
				</div>

				<!-- Error div for the Selectively synched users -->
				<div class='eb-ss-users-error-wrap'></div>

				<!-- Wp-list table html for users started -->
				<div class="eb-ss-selective-users-cont">
					<form method="get">
						<?php
						$list_table->search_box( __( 'Search Users', 'selective-synch-td' ), 'eb_selective_synch_users_search' );

						// Function used to display the lsit table.
						$list_table->display();
						?>
					</form>
				</div>

			</div>
			<?php
		}
	}
}
