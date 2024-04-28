<?php
/**
 * Handles the deletion of data entries from the database.
 *
 * This script is triggered via a POST request and is responsible for deleting entries
 * from a specified database table. It checks for a valid nonce to secure the request,
 * verifies the request method is POST, and confirms the presence of an 'id' in the POST data.
 * If any of these checks fail, the script will terminate the execution and return an error.
 * On successful deletion, it returns the result and the ID of the deleted entry in JSON format.
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

if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'google_ss2db' ) || 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['id'] ) ) {
	wp_die( 'Our Site is protected!!' );
}

$theid = wp_unslash( $_POST['id'] );
$array = array(
	'id' => $theid,
);

global $wpdb;
$res = $wpdb->delete( GOOGLE_SS2DB_TABLE_NAME, $array );

$return = array(
	'res' => $res,
	'id'  => wp_unslash( $_POST['id'] ),
);

echo json_encode( $return );
exit;
