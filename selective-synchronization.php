<?php
/**
 * Plugin Name:       Selective Synchronization
 * Plugin URI:        https://edwiser.org/bridge/extensions/selective-synchronization/
 * Description:       Synchronizes selected moodle courses in WordPress.
 * Version:           2.1.0
 * Author:            WisdmLabs
 * Author URI:        https://wisdmlabs.com
 * Text Domain:       selective-synch-td
 * Domain Path:       /languages
 *
 * @link    https://wisdmlabs.com
 * @since   1.1
 * @package SelectiveSync
 */

use ebSelectSync\includes as eb_includes;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Global variable to provide plugin data for licensing
 *
 *  @var array
 */
$eb_select_plg_data = array(
	'plugin_short_name' => 'Selective Synchronization',
	'plugin_slug'       => 'selective_sync',
	'plugin_version'    => '2.1.0',
	'plugin_name'       => 'Selective Synchronization',
	'store_url'         => 'https://edwiser.org/check-update',
	'author_name'       => 'WisdmLabs',
);

require_once 'includes/class-eb-select-add-plugin-data-in-db.php';
$license = new eb_includes\Eb_Select_Add_Plugin_Data_In_Db( $eb_select_plg_data );
$license->init_license();


/**
 * This code checks if new version is available
*/
if ( ! class_exists( 'Eb_Select_Plugin_Updater' ) ) {
	include 'includes/class-eb-select-plugin-updater.php';
}

$l_key = trim( get_option( 'edd_' . $eb_select_plg_data['plugin_slug'] . '_license_key' ) );

// setup the updater.
new eb_includes\Eb_Select_Plugin_Updater(
	$eb_select_plg_data['store_url'],
	__FILE__,
	array(
		'version'   => $eb_select_plg_data['plugin_version'], // current version number.
		'license'   => $l_key, // license key (used get_option above to retrieve from DB).
		'item_name' => $eb_select_plg_data['plugin_name'], // name of this plugin.
		'author'    => $eb_select_plg_data['author_name'], // author of the plugin.
	)
);

$l_key = null;

/*
 * Check if edwiser - Base plugin active or not
 */
add_action( 'admin_init', 'wdm_selective_sync_activation' );

/**
 * All activation activities.
 */
function wdm_selective_sync_activation() {
	$extensions  = array( 'edwiser_bridge' => array( 'edwiser-bridge/edwiser-bridge.php', '1.1' ) );
	$edwiser_old = true;

	// deactive legacy extensions.
	foreach ( $extensions as $extension ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $extension[0] );
		if ( isset( $plugin_data['Version'] ) ) {
			if ( version_compare( $plugin_data['Version'], $extension[1] ) >= 0 ) {
				$edwiser_old = false;
			}
		}
	}

	if ( ! is_plugin_active( 'edwiser-bridge/edwiser-bridge.php' ) || $edwiser_old ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		unset( $_GET['activate'] );
		add_action( 'admin_notices', 'wdm_selective_sync_activation_notices' );
	}
}




/**
 * Shows Edwiser bridge Activation notice if activated without activating the edwiser bridge.
 */
function wdm_selective_sync_activation_notices() {
	echo '<div class="error"><p>' . esc_html( __( 'You need to activate <a href="http://wordpress.org/extend/plugins/edwiser-bridge/">Edwiser Bridge</a> for activating <strong> Selective Synchronization </strong> Plugin.', 'edw_woo' ) ) . '</p></div>';
}

/**
 * Begins execution of the plugin.

 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-selective-sync.php';

/**
 * Start to include and running of the hooks  and the files.
 */
function run_selective_sync() {
	$plugin = new eb_includes\Selective_Sync();
	$plugin->run();
}
run_selective_sync();
