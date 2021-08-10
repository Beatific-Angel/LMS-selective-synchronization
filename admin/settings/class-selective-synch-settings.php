<?php
/**
 * EDW General Settings
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @package    Selective synchronization
 * @subpackage Selective synchronization/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SelectiveSynchSettings' ) ) :

	/**
	 * WooIntSettings.
	 */
	class SelectiveSynchSettings extends \app\wisdmlabs\edwiserBridge\EBSettingsPage
	{
		/*
		 * variable used to get the users settings object.
		 */
		private $usersSettings   = null;

		/*
		 * variable used to get the courses settings object.
		 */
		private $coursesSettings = null;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->_id   = 'selective_synch_settings';
			$this->label = __( 'Selective Sync', 'selective-synch-td' );

			$usersObject  = new Selective_Synch_Users_Settings();
			$courseObject = new Selective_Synch_Courses_Settings();

			// set user and course settinsg objects.
			$this->setUsersSettings( $usersObject );
			$this->setCoursesSettings( $courseObject );

			add_filter( 'eb_settings_tabs_array', array( $this, 'addSettingsPage' ), 20 );
			add_action( 'eb_settings_' . $this->_id, array( $this, 'output' ) );

			// Commented save function as we are not using it anymore.
			// add_action('eb_settings_save_'.$this->_id, array($this, 'save'));.
			add_action( 'eb_sections_' . $this->_id, array( $this, 'outputSections' ) );

			/**
			 * The table added in the end of the users setting is added by this hook.
			 * Wp-list-table handling Hook .
			 */
			add_action( 'eb_admin_field_selective_synch_list_table', array( $usersObject, 'get_users_table' ) );
		}


		/*
		 * Setter for the users setting.
		 * @since 1.2.0
		 */
		public function setUsersSettings($userSettingsObject)
		{
			$this->usersSettings = $userSettingsObject;
		}

		/*
		 * Setter for the course settings.
		 * @since 1.2.0
		 */
		public function setCoursesSettings( $coursesSettingsObject ) {
			$this->coursesSettings = $coursesSettingsObject;
		}

		/**
		 * Function used to show 2 tabs on the selective synchronization page.
		 *
		 * @since 1.2.0
		 * @return array of the sections.
		 */
		public function getSections() {
			$sections = array(
				''      => __( 'Course', 'selective-synch-td' ),
				'users' => __( 'Users', 'selective-synch-td' ),
			);
			return apply_filters( 'eb_getSections_' . $this->_id, $sections );
		}



		/*
		public function get_settings()
		{
			$settings = apply_filters(
				'selective_synch_settings_fields',
				array(
					array(
						'title' => __('Selective Synch Options', 'selective-synch-td'),
						'type' => 'title',
						'desc' => '',
						'id' => 'selective_synch_options',
					),
					array('type' => 'sectionend', 'id' => 'selective_synch_options'),
				)
			);

			return apply_filters('eb_get_settings_'.$this->_id, $settings);
		}*/


		/**
		 * print settings array.
		 *
		 * @since  1.2.0
		 * @return array
		 */
		public function output() {

			global $current_section;
			$GLOBALS['hide_save_button'] = true;
			$settings = $this->get_settings( $current_section );
			\app\wisdmlabs\edwiserBridge\EbAdminSettings::outputFields( $settings );
		}


		/**
		 * Get settings array.
		 *
		 * @since  1.2.0
		 * @return array
		 */
		public function get_settings($current_section = '')
		{
			/**
			 * Enqueue settings paige js only the setting page.
			 */
			$settings = array();

			$nonce = wp_create_nonce( 'check_select_sync_action' );

			$categoryList = array();
			$settings     = array();

			//check which section is selected and show the settings accordingly.
			if ( 'users' == $current_section ) {
				$settings = $this->usersSettings->get_settings();
			} else {
				$courseData = $this->coursesSettings->get_settings();
				$settings       = $courseData['settings'];
				$categoryList   = $courseData['category_list'];
			}

			$array_data = array(
				'admin_ajax_path'        => admin_url( 'admin-ajax.php' ),
				'nonce'                  => $nonce,
				'category_list'          => $categoryList,
				'chk_error'              => __( 'Select atleast one course to Synchronize.', 'selective-synch-td' ),
				'select_success'         => __( 'Courses synchronized successfully.', 'selective-synch-td' ),
				'connect_error'          => __( 'There is a problem while connecting to moodle server.', 'selective-synch-td' ),
				'ajax_error'             => __( 'Unable to proceed request.', 'selective-synch-td' ),
				'user_migration_success' => __( 'Users creation and linking commpleted successfully.', 'selective-synch-td' ),
				'all_user_synch_warning' => __( 'It will take some time please be patient and do not refresh or change the page.', 'selective-synch-td' )
			);

			wp_enqueue_script( 'select-admin-js' );
			wp_localize_script( 'select-admin-js', 'admin_js_select_data', $array_data );

			// Enqueuing scripts for datatables.
			wp_enqueue_script( 'eb-ss-button-datatable-js' );
			wp_enqueue_script( 'eb-ss-buttons-html5-datatable-js' );
			wp_enqueue_script( 'eb-ss-button-print-datatable-js' );

			return apply_filters( 'eb_get_settings_' . $this->_id, $settings );
		}
	}

endif;

return new SelectiveSynchSettings();
