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

if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'google_ss2db' ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	wp_die( 'Our Site is protected!!' );
}

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

/**
 * Get the Google Spreadsheet Data.
 *
 * @return array "description".
 */
function get_value_google_spreadsheet( $worksheetname, $sheetname ) {
	$client_secret = ( defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) ) ? GOOGLE_SS2DB_CLIENT_SECRET_PATH : '';
	putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $client_secret );
	$client = new Google_Client;
	$client->useApplicationDefaultCredentials();
	$client->setApplicationName( 'Something to do with my representatives' );
	$client->setScopes( array( 'https://www.googleapis.com/auth/drive', 'https://spreadsheets.google.com/feeds' ) );
	if ( $client->isAccessTokenExpired() ) {
		$client->refreshTokenWithAssertion();
	}
	$accesstoken = $client->fetchAccessTokenWithAssertion()['access_token'];

	ServiceRequestFactory::setInstance(
		new DefaultServiceRequest( $accesstoken )
	);
	$spreadsheet = ( new Google\Spreadsheet\SpreadsheetService )
	->getSpreadsheetFeed()
	->getByTitle( $worksheetname );

	$worksheet = $spreadsheet->getWorksheetFeed()->getByTitle( $sheetname );
	$listfeed  = $worksheet->getListFeed();

	$returnrows = array();
	foreach ( $listfeed->getEntries() as $entry ) {
		$returnrows[] = $entry->getValues();
	}

	$array = apply_filters( 'google_ss2db_before_save', $returnrows );

	return $array;
}

/**
 * Save Google Spreadsheet Data to DB.
 *
 * @return boolean "description".
 */
function save_spreadsheet() {
	global $wpdb;
	$today         = new DateTime();
	$today->setTimeZone( new DateTimeZone( get_option( 'timezone_string' ) ) );
	$date          = $today->format( 'Y-m-d H:i:s' );
	$title         = wp_unslash( $_POST['datatitle'] );
	$worksheetname = wp_unslash( $_POST['worksheetname'] );
	$sheetname     = wp_unslash( $_POST['sheetname'] );
	$value         = get_value_google_spreadsheet( $worksheetname, $sheetname );
	if ( get_option( 'google_ss2db_dataformat' ) === 'json-unescp' ) {
		$value = json_encode( $value, JSON_UNESCAPED_UNICODE );
	} else {
		$value = json_encode( $value );
	}
	$result = $wpdb->insert(
		GOOGLE_SS2DB_TABLE_NAME,
		array(
			'date'  => $date,
			'title' => $title,
			'value' => $value,
		),
		array(
			'%s',
			'%s',
			'%s',
		)
	);
	$rowid = $wpdb->insert_id;

	$return = array(
		'id'     => $rowid,
		'date'   => $date,
		'title'  => $title,
		'value'  => $value,
		'result' => $result,
	);

	return $return;
}

$return  = save_spreadsheet();
$rus     = apply_filters( 'google_ss2db_after_save', $return );
$bool    = ( $return['result'] ) ? true : false;
$referer = wp_unslash( $_POST['_wp_http_referer'] );
$referer = str_replace( '&settings-updated=true', '', $referer );
$referer = $referer . '&ss2dbupdated=' . $bool;
wp_redirect( $referer );
exit;
