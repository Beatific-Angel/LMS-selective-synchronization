<?php
/**
 * Selective Synchronization courses settings.
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

if ( ! class_exists( 'Selective_Synch_Courses_Settings' ) ) {

	/**
	 * Selctive synch course settings.
	 */
	class Selective_Synch_Courses_Settings {
		/**
		 * This returns array of the elements need to add in the course settings page
		 *
		 * @since 1.2.0
		 */
		public function get_settings() {
			$settings = array();

			// Performed to get the test connection result.
			$connected = \app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->connectionHelper()->connectionTestHelper( EB_ACCESS_URL, EB_ACCESS_TOKEN );

			if ( 1 === $connected['success'] ) {
				// gets courses which we will display on the selective synch courses settings.
				$response          = \app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->courseManager()->getMoodleCourses();
				$category_response = \app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->courseManager()->getMoodleCourseCategories();

				if ( 1 === $response['success'] ) {
					if ( 1 === $category_response['success'] ) {
						$moodle_category_data = $category_response['response_data'];
					}

					$moodle_courses_data = $response['response_data'];

					$settings = apply_filters(
						'eb_select_course_synchronization_settings',
						array(
							array(
								'type' => 'title',
								'id'   => 'select_sync_options',
							),
							array(
								'title'           => __( 'Synchronization Options', 'selective-synch-td' ),
								'desc'            => __( 'Update previously synchronized courses', 'selective-synch-td' ),
								'id'              => 'eb_update_selected_courses',
								'default'         => 'no',
								'type'            => 'checkbox',
								'show_if_checked' => 'option',
								'autoload'        => false,

							),
							array(
								'title'    => '',
								'desc'     => '',
								'id'       => 'eb_sync_selected_course_button',
								'default'  => 'Start Synchronization',
								'type'     => 'button',
								'desc_tip' => false,
								'class'    => 'button secondary',
							),

							array(
								'type' => 'sectionend',
								'id'   => 'select_sync_options',
							),
						)
					);
				}
			} else {
				$moodle_category_data = array();
				$moodle_courses_data  = array();
			}

			$category_list = array();

			// Template included to show the Moodle courses in the datatable.
			include_once SELECTIVE_SYNC_PLUGIN_DIR . 'admin/partials/eb-select-moodle-course-list.php';

			return array(
				'settings'      => $settings,
				'category_list' => $category_list,
			);
		}
	}
}
