<?php
/**
 * Wp list table used to display users list.
 *
 * @link       https://edwiser.org
 * @since      1.2.0
 *
 * @package    Selective Synchronization
 * @subpackage Selective Synchronization/admin
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace ebSelectSync\includes;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( '\ebSelectSync\includes\Eb_Select_Users_List_Table' ) ) {
	/**
	 * Wp list table class used to show the users data.
	 */
	class Eb_Select_Users_List_Table extends \WP_List_Table {

		/**
		 * The ID of this plugin.
		 *
		 * @since    2.0.0
		 * @access   private
		 * @var      string    $bp_columns  bp_columns .
		 */
		protected $bp_columns;

		/**
		 * Consrtuctor of the class.
		 */
		public function __construct() {
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'User creation',
					'plural'   => 'Users creation',
					'ajax'     => true,
				)
			);

			// Columns.
			$this->bp_columns = apply_filters(
				'selective_synch_add_column_to_users_tbl',
				array(
					'cb'           => '<input type="checkbox" />',
					'mdl_id'       => __( 'Moodle User Id', 'eb-textdomain' ),
					'username'     => __( 'Username', 'eb-textdomain' ),
					'firstname'    => __( 'First Name', 'eb-textdomain' ),
					'lastname'     => __( 'Last Name', 'eb-textdomain' ),
					'email'        => __( 'Email', 'eb-textdomain' ),
					'synch_status' => __( 'Synch Status', 'eb-textdomain' ),
				)
			);
		}


		/**
		 * This function add custom action above table in this case we have added the "create user" and "create and link user" actions.
		 *
		 * @param  string $which which.
		 */
		public function extra_tablenav( $which ) {
			$actions = apply_filters(
				'eb-ss-user-bulk-actions',
				array(
					'create_user'          => __(
						'Create selected users',
						'selective-synch-td'
					),
					'create_and_link_user' => __(
						'Create and link selected users',
						'selective-synch-td'
					),
				)
			);

			?>
			<div class="alignleft actions">
				<label class="screen-reader-text" for="">
					<?php esc_html_e( 'Bulk Actions', 'selective-synch-td' ); ?>
				</label>
				<select name="eb-ss-user-bulk-action-dropdown" class="eb-ss-user-bulk-action-dropdown">
					<option value=""><?php esc_html_e( 'Bulk Actions', 'selective-synch-td' ); ?></option>
					<?php
					foreach ( $actions as $value => $action_name ) {
						?>
						<option value="<?php echo esc_html( $value ); ?>"><?php echo esc_html( $action_name ); ?> </option>
						<?php
					}
					?>
				</select>
				<input type="submit" class="eb_ss_user_bulk_actions" class="button action" value="<?php esc_html_e( 'Apply', 'selective-synch-td' ); ?>">
			</div>
			<?php
		}


		/**
		 * Returns the user profile link.
		 *
		 * @param string $user_id user id.
		 * @return string
		 */
		private function get_user_profile_url( $user_id ) {
			$user_name = '';
			$user_info = get_userdata( $user_id );
			if ( $user_info ) {
				$edit_link = get_edit_user_link( $user_id );
				$user_name = '<a href="' . esc_url( $edit_link ) . '">' . $user_info->user_login . '</a>';
			}
			return $user_name;
		}


		/**
		 * Returns the custom column.
		 *
		 * @return string
		 */
		public function get_columns() {
			return $this->bp_columns;
		}

		/**
		 * Sortable columns.
		 */
		protected function get_sortable_columns() {
			$sortable_columns = array(
				'rId'           => array( 'rId', false ),
				'course'        => array( 'course', false ),
				'user'          => array( 'user', false ),
				'enrolled_date' => array( 'enrolled_date', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Get default column value.
		 *
		 * Recommended. This method is called when the parent class can't find a method
		 * specifically build for a given column. Generally, it's recommended to include
		 * one method for each column you want to render, keeping your package class
		 * neat and organized. For example, if the class needs to process a column
		 * named 'title', it would first see if a method named $this->column_title()
		 * exists - if it does, that method will be used. If it doesn't, this one will
		 * be used. Generally, you should try to use custom column methods as much as
		 * possible.
		 *
		 * Since we have defined a column_title() method later on, this method doesn't
		 * need to concern itself with any column with a name of 'title'. Instead, it
		 * needs to handle everything else.
		 *
		 * For more detailed insight into how columns are handled, take a look at
		 * WP_List_Table::single_row_columns()
		 *
		 * @param object $item        A singular item (one full row's worth of data).
		 * @param string $column_name The name/slug of the column to be processed.
		 * @return string Text or HTML to be placed inside the column <td>.
		 */
		protected function column_default( $item, $column_name ) {
			// returns the value for column.
			return $item[ $column_name ];
		}


		/**
		 * Column synch status.
		 *
		 * @param string $item column.
		 */
		protected function column_synch_status( $item ) {
			return eb_ss_get_users_status( $item['email'] );
		}




		/**
		 * Get value for checkbox column.
		 *
		 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
		 * is given special treatment when columns are processed. It ALWAYS needs to
		 * have it's own method.
		 *
		 * @param object $item A singular item (one full row's worth of data).
		 * @return string Text to be placed inside the column <td>.
		 */
		protected function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" class="eb-ss-user-tbl-cb" name="ss_users_mdl_id[]" value="%s" data-fname="%s" data-lname="%s" data-email="%s" data-username="%s"/>',
				$item['mdl_id'],
				$item['firstname'],
				$item['lastname'],
				$item['email'],
				$item['username']
			);
		}


		/**
		 * Get an associative array ( option_name => option_title ) with the list
		 * of bulk actions available on this table.
		 *
		 * Optional. If you need to include bulk actions in your list table, this is
		 * the place to define them. Bulk actions are an associative array in the format
		 * 'slug'=>'Visible Title'
		 *
		 * If this method returns an empty value, no bulk action will be rendered. If
		 * you specify any bulk actions, the bulk actions box will be rendered with
		 * the table automatically on display().
		 *
		 * Also note that list tables are not automatically wrapped in <form> elements,
		 * so you will need to create those manually in order for bulk actions to function.
		 */
		protected function get_bulk_actions() {
			// commented this function because currently there is no need of this function.
		}



		/**
		 * Prepares the list of items for displaying.
		 *
		 * REQUIRED! This is where you prepare your data for display. This method will
		 * usually be used to query the database, sort and filter the data, and generally
		 * get it ready to be displayed. At a minimum, we should set $this->items and
		 * $this->set_pagination_args(), although the following properties and methods
		 * are frequently interacted with here.
		 *
		 * @global wpdb $wpdb
		 * @uses $this->_column_headers
		 * @uses $this->items
		 * @uses $this->get_columns()
		 * @uses $this->get_sortable_columns()
		 * @uses $this->get_pagenum()
		 * @uses $this->set_pagination_args()
		 */
		public function prepare_items() {
			/*
			 * Handle the form submissions.
			 * This handles the action submitted in the table.
			 */

			/*
			 * First, lets decide how many records per page to show
			 */
			$per_page = 20;

			/*
			 * REQUIRED. Now we need to define our column headers. This includes a complete
			 * array of columns to be displayed (slugs & titles), a list of columns
			 * to keep hidden, and a list of columns that are sortable. Each of these
			 * can be defined in another method (as we've done here) before being
			 * used to build the value for our _column_headers property.
			 */
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			/*
			 * REQUIRED. Finally, we build an array to be used by the class for column
			 * headers. The $this->_column_headers property takes an array which contains
			 * three other arrays. One for all columns, one for hidden columns, and one
			 * for sortable columns.
			 */
			$this->_column_headers = array( $columns, $hidden, $sortable );

			/**
			 * Optional. You can handle your bulk actions however you see fit. In this
			 * case, we'll handle them within our package just to keep things clean.
			 */

			/**
			 * This checks for sorting input and sorts the data in our array of dummy
			 * data accordingly (using a custom usort_reorder() function). It's for
			 * example purposes only.
			 *
			 * In a real-world situation involving a database, you would probably want
			 * to handle sorting by passing the 'orderby' and 'order' values directly
			 * to a custom query. The returned data will be pre-sorted, and this array
			 * sorting technique would be unnecessary. In other words: remove this when
			 * you implement your own query.
			 */

			// check and modify.

			/**
			 * REQUIRED for pagination. Let's figure out what page the user is currently
			 * looking at. We'll need this later, so you should always include it in
			 * your own package classes.
			 */
			$current_page = $this->get_pagenum();

			/**
			 * Setting offset as 1 for the first page if the search string is null because of the guest user.
			 * This offset is send directly to the moodle web service to get the users after particular number.
			 */
			$offset = ( ( $current_page - 1 ) * $per_page ) + 1;

			/**
			 * Get search string on the submission of the form on synchronuization users page.
			 */
			$search_text = '';
			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
				$search_text = $_REQUEST['s'];
				/**
				 * Setting offset as 0 for the first page if the search string is not null because of the guest user.
				 */
				$offset = ( ( $current_page - 1 ) * $per_page );
			}

			$table_details = eb_ss_get_moodle_users_in_chunk( $offset, $per_page, $search_text );

			$data = $table_details['data'];
			/**
			 * REQUIRED for pagination. Let's check how many items are in our data array.
			 * In real-world use, this would be the total number of items in your database,
			 * without filtering. We'll need this later, so you should always include it
			 * in your own package classes.
			 */
			$total_items = $table_details['total_users'];

			/*
			 * The WP_List_Table class does not handle pagination for us, so we need
			 * to ensure that the data is trimmed to only the current page. We can use
			 * array_slice() to do that.
			 */
			// $data = array_slice($data, ( ( $current_page - 1 ) * $per_page), $per_page);

			/*
			 * REQUIRED. Now we can add our *sorted* data to the items property, where
			 * it can be used by the rest of the class.
			 */
			$this->items = $data;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
					'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
				)
			);
		}

		/**
		 * Callback to allow sorting of example data.
		 *
		 * @param string $first First value.
		 * @param string $second Second value.
		 */
		protected function usort_reorder( $first, $second ) {
			$first  = $first;
			$second = $second;
		}
	}
}
