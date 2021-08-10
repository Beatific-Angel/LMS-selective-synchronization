<?php
/**
 * Definations of the normally used functions.
 *
 * @link       http://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    SelectiveSync
 * @subpackage SelectiveSync/includes
 */

if ( ! function_exists( 'eb_ss_get_moodle_users_in_chunk' ) ) {
	/**
	 * Functionality to return user in chunks.
	 * Used while migrating all users.
	 *
	 * @param  string $offset     record no. from which you want users.
	 * @param  string $limit      how many users we are fetching at a time.
	 * @param  string $search_text string matchinh to the users name or email.
	 */
	function eb_ss_get_moodle_users_in_chunk( $offset, $limit, $search_text ) {
		// check if the connection is successfull.
		$total_users    = 0;
		$users          = array();
		$web_service_fn = 'eb_get_users';
		$request_data   = array(
			'offset'        => $offset,
			'limit'         => $limit,
			'search_string' => $search_text,
			'total_users'   => 1,
		);
		$result         = \app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->connectionHelper()->connectMoodleWithArgsHelper( $web_service_fn, $request_data );

		if ( isset( $result['success'] ) && 1 === $result['success'] && ! empty( $result['response_data'] ) ) {
			$total_users = $result['response_data']->total_users;
			foreach ( $result['response_data']->users as $user ) {
				array_push(
					$users,
					array(
						'mdl_id'    => $user->id,
						'username'  => $user->username,
						'firstname' => $user->firstname,
						'lastname'  => $user->lastname,
						'email'     => $user->email,
					)
				);
			}
		}

		return array(
			'total_users' => $total_users,
			'data'        => $users,
		);
	}
}



if ( ! function_exists( 'eb_ss_test_connection' ) ) {

	/**
	 * Testing connection with the moodle site.
	 */
	function eb_ss_test_connection() {
		$connected = \app\wisdmlabs\edwiserBridge\edwiserBridgeInstance()->connectionHelper()->connectionTestHelper( EB_ACCESS_URL, EB_ACCESS_TOKEN );

		if ( 1 === $connected['success'] ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'eb_ss_get_users_status' ) ) {
	/**
	 * Returns the status of the user i.e checks if the user is created inked or not created.
	 *
	 * @param  string $email email of the user.
	 */
	function eb_ss_get_users_status( $email ) {
		// check if the users email id exist in the WP.
		$user_id = email_exists( $email );
		$status  = __( 'Not Created', 'selective-synch-td' );

		if ( $user_id ) {
			$status         = __( 'Created', 'selective-synch-td' );
			$moodle_user_id = get_user_meta( $user_id, 'moodle_user_id', true );

			if ( $moodle_user_id ) {
				$status = __( 'Linked', 'selective-synch-td' );
			}
		}

		return '<span class="eb_ss_users_status">' . $status . '</span>';
	}
}
