<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/sectsect/
 * @since      1.0.0
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/includes
 */

require '../../../../wp-load.php';

if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'google_ss2db' ) || 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['id'] ) ) {
	wp_die( 'Our Site is protected!!' );
}

$theid = wp_unslash( $_POST['id'] );
$res = $wpdb->delete( GOOGLE_SS2DB_TABLE_NAME, array( 'id' => $theid ) );

$return = array(
	"res"    => $res,
	"id"     => wp_unslash( $_POST['id'] ),
);

echo json_encode( $return );
exit;
