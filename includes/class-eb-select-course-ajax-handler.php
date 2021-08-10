<?php
/**
 * This class contains functionality to handle actions of custom buttons implemented in settings page
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @package    Selective_Sync
 * @subpackage Selective_Sync/includes
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\includes;

use app\wisdmlabs\edwiserBridge as ed_parent;
/**
 * Handles all ajax calls on the settings page.
 */
class Eb_Select_Course_Ajax_Handler {

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
	 * Constructor of the class.
	 *
	 * @param string $plugin_name plugin name.
	 * @param string $version plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Initiate course synchronization process.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function selected_course_synchronization_initiater() {

		if ( isset( $_POST['_wpnonce_field'], $_POST['selected_courses'], $_POST['update_course'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce_field'] ), 'check_select_sync_action' ) ) {
			$selected_course_ids = wp_unslash( $_POST['selected_courses'] );
			$sync_options        = wp_unslash( $_POST['update_course'] );

			$response = $this->selected_course_synchronization_handler( $selected_course_ids, $sync_options );

			echo wp_json_encode( $response );
			die();
		}

		// verifying generated nonce we created earlier.
		if ( isset( $_POST['_wpnonce_field'] ) && ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce_field'] ), 'check_select_sync_action' ) ) {
			die( 'Busted!' );
		}
	}

	/**
	 * Creates or updates the selected courses in the WordPress
	 *
	 * @param  array $course_ids   selected course ids.
	 * @param  int   $sync_options update previously sync course.
	 * @return array               response for synchronization
	 */
	public function selected_course_synchronization_handler( $course_ids, $sync_options ) {
		ed_parent\edwiserBridgeInstance()->logger()->add( 'user', 'Initiating course & category sync process....' ); // add course log.

		$moodle_course_response   = array(); // contains course response from moodle.
		$moodle_category_response = array(); // contains category response from moodle.
		$response_array           = array(); // contains response message to be displayed to user.
		$courses_updated          = array(); // store updated course ids ( WordPress course ids ).
		$courses_created          = array(); // store newely created course ids ( WordPress course ids ).
		// $category_created   = array(); // array of categories created / synced from moodle.

		// checking if moodle connection is working properly.
		$connected = ed_parent\edwiserBridgeInstance()->connectionHelper()->connectionTestHelper( EB_ACCESS_URL, EB_ACCESS_TOKEN );

		$response_array['connection_response'] = $connected['success']; // add connection response in response array.

		if ( 1 === $connected['success'] ) {
			$moodle_category_response = ed_parent\edwiserBridgeInstance()->courseManager()->getMoodleCourseCategories(); // get categories from moodle.

			// creating categories based on recieved data.
			if ( 1 === $moodle_category_response['success'] ) {
				ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Creating course categories....' );
				ed_parent\edwiserBridgeInstance()->courseManager()->createCourseCategoriesOnWordpress( $moodle_category_response['response_data'] );
				ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Categories created....' );
			}

			// push category response in array.
			$response_array['category_success']          = $moodle_category_response['success'];
			$response_array['category_response_message'] = $moodle_category_response['response_message'];

			$moodle_course_response = $this->get_selected_moodle_courses( $course_ids );

			if ( 1 === $moodle_course_response['success'] ) {
				foreach ( $moodle_course_response['response_data'] as $course_data ) {
					/**
						 * Moodle always returns moodle frontpage as first course,
						 * below step is to avoid the frontpage to be added as a course.
						 *
						 * @var [type]
						 */
					if ( 1 === $course_data->id ) {
						continue;
					}

					// check if course is previously synced.
					$existing_course_id = ed_parent\edwiserBridgeInstance()->courseManager()->isCoursePresynced( $course_data->id );

					// creates new course or updates previously synced course conditionally.
					if ( ! is_numeric( $existing_course_id ) ) {
						ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Creating a new course....' );  // add course log.

						$course_id         = ed_parent\edwiserBridgeInstance()->courseManager()->createCourseOnWordpress( $course_data, $sync_options );
						$courses_created[] = $course_id; // push course id in courses created array.

						ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Course created, ID is: ' . $course_id ); // add course log.
					} elseif ( is_numeric( $existing_course_id ) && isset( $sync_options ) && 1 == $sync_options ) {
						ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Updating existing course: ID is: ' . $existing_course_id );  // add course log.

						$course_id = ed_parent\edwiserBridgeInstance()->courseManager()->updateCourseOnWordpress( $existing_course_id, $course_data, $sync_options );
						ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Updated course....' );  // add course log.

						$courses_updated[] = $course_id; // push course id in courses updated array.
					}
				}
			}

			// push course response in array.
			$response_array['course_success']          = $moodle_course_response['success'];
			$response_array['course_response_message'] = $moodle_course_response['response_message'];
		} else {
			ed_parent\edwiserBridgeInstance()->logger()->add( 'course', 'Connection problem in synchronization, Response:' . $connected ); // add connection log.
		}
		return $response_array;
	}

	/**
	 * Retrieves course data of selected course ids from moodle.
	 *
	 * @param  array $course_ids selected course ids.
	 * @return array             selected course data.
	 */
	public function get_selected_moodle_courses( $course_ids ) {

		ed_parent\edwiserBridgeInstance()->logger()->add( 'course', "\n Fetching courses from moodle.... \n" ); // add course log.

		$request_data = array( 'options' => array( 'ids' => $course_ids ) );

		$webservice_function = 'core_course_get_courses'; // get courses from moodle.
		$response            = ed_parent\edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper( $webservice_function, $request_data );

		return $response;
	}
}
