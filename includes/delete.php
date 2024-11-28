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

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

// Secure input handling with filter_input().
$nonce          = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$request_method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$id             = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

if ( ! $nonce || ! wp_verify_nonce( $nonce, 'google_ss2db' ) || 'POST' !== $request_method || ! $id ) {
	wp_die( 'Our Site is protected!!' );
}

$array = array(
	'id' => $id,
);

global $wpdb;
$res = $wpdb->delete( GOOGLE_SS2DB_TABLE_NAME, $array );

$return = array(
	'res' => $res,
	'id'  => $id,
);

echo json_encode( $return );
exit;
