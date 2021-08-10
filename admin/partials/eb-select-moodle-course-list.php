<?php
/**
 * COurses list template.
 *
 * @package     SelectiveSync
 */

if ( empty( $moodle_courses_data ) ) {
	echo '<p class="eb-dtable-error">';
	esc_html_e( 'There is a problem while connecting to moodle server. Please, check your moodle connection or try', 'selective-synch-td' ) . '<a href="javascript:history.go(0)" style="cursor:pointer;">' . esc_html_e( ' reloading', 'selective-synch-td' ) . '</a>' . esc_html_e(' the page.', 'selective-synch-td' );
	echo '</p>';
} else {
	?>
	<table id='moodle_courses_table' >
	<thead>
		<tr>
			<td></td>
			<td></td>
			<td class="filter eb-filter"><?php esc_html_e( 'All Categories', 'selective-synch-td' ); ?></td>
		</tr>
		<tr>
			<th class="dt-center"><input class="select_all_course_cb" type='checkbox' name='select_all_course' /></th>
			<th class="dt-left"><?php esc_html_e( 'Course Name', 'selective-synch-td' ); ?></th>
			<th class="dt-left eb-last-td"><?php esc_html_e( 'Category', 'selective-synch-td' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php

	foreach ( $moodle_courses_data as $course_data ) {
		/**
		 * Moodle always returns moodle frontpage as first course,
		 * below step is to avoid the frontpage to be added as a course.
		 *
		 * @var [type]
		 */
		if ( 1 === $course_data->id ) {
			continue;
		}


		echo '<tr>';
		echo '<td class="dt-center"><input type="checkbox" name="chksel_course" value="' . esc_html( $course_data->id ) . '" /></td>';
		echo '<td>' . esc_html( $course_data->fullname ) . '</td>';

		foreach ( $moodle_category_data as $category ) {
			if ( $category->id === $course_data->categoryid ) {
				if ( $category->depth > 1 ) {
					$ids = explode( '/', $category->path );
					echo '<td class="eb-last-td">';

					foreach ( $ids as $category_id ) {
						foreach ( $moodle_category_data as $category ) {
							if ( $category->id === $category_id ) {
								echo esc_html( $category->name );
								if ( end( $ids ) !== $category_id ) {
									echo ' / ';
								}

								if ( ! in_array( $category->name, $category_list, true ) ) {
									array_push( $category_list, $category->name );
								}
								break;
							}
						}
					}
					echo '</td>';
				} else {
					echo '<td class="eb-last-td">' . esc_html( $category->name ) . '</td>';
					if ( ! in_array( $category->name, $category_list, true ) ) {
						array_push( $category_list, $category->name );
					}
				}

				break;
			}
		}
			echo '</tr>';
	}

	?>
	<tfoot>

	<tr>
		<th class="dt-center"><input class='select_all_course_cb' type='checkbox' name='select_all_course' /></th>
		<th class="dt-left"><?php esc_html_e( 'Course Name', 'selective-synch-td' ); ?></th>
		<th class="dt-left eb-last-td"><?php esc_html_e( 'Category', 'selective-synch-td' ); ?></th>
	</tr>
	</tfoot>
	</tbody>
</table>

	<?php
}
