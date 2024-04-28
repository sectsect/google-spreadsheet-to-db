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

/**
 * Checks if a specific sheet exists within a list of sheets.
 *
 * @param Google_Service_Sheets_Sheet[] $sheets_list An array of sheet objects.
 * @param string                        $sheet_name The name of the sheet to check for.
 * @return bool Returns true if the sheet exists, false otherwise.
 */
function exist_sheet( array $sheets_list, string $sheet_name ): bool {
	foreach ( $sheets_list as $sheet ) {
		if ( $sheet->properties->title === $sheet_name ) {
			return true;
		}
	}
	return false;
}

/**
 * Creates and returns a Google_Client authorized for Google Sheets and Drive APIs.
 *
 * @return Google_Client The authorized client object.
 */
function get_client(): Google_Client {
	$client_secret = ( defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) ) ? GOOGLE_SS2DB_CLIENT_SECRET_PATH : '';
	putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $client_secret );

	$client = new Google_Client();
	$client->setApplicationName( 'Google Sheets API PHP Quickstart' );
	$client->setScopes( array( Google_Service_Sheets::SPREADSHEETS, Google_Service_Sheets::DRIVE ) );
	$client->setAuthConfig( $client_secret );
	$client->setAccessType( 'offline' );

	return $client;
}

/**
 * Retrieves data from a specified Google Spreadsheet.
 *
 * @param string $worksheetid The ID of the Google Spreadsheet.
 * @param string $worksheetname The name of the Google Spreadsheet.
 * @param string $sheetname The name of the individual sheet within the Spreadsheet.
 * @param bool   $hasheaderrow Indicates if the spreadsheet contains a header row.
 * @return array|bool The spreadsheet data as an associative array if successful, or false if the sheet does not exist.
 */
function get_value_google_spreadsheet( string $worksheetid, string $worksheetname, string $sheetname, bool $hasheaderrow ): array|bool {
	// Get the API client and construct the service object.
	$client  = get_client();
	$service = new Google_Service_Sheets( $client );

	// Prints the names and majors of students in a sample spreadsheet.
	// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit .
	$spreadsheet_id = $worksheetid;
	$range          = $sheetname;

	$response = $service->spreadsheets->get( $spreadsheet_id );
	$sheets   = $response->getSheets();
	if ( exist_sheet( $sheets, $sheetname ) ) {
		$response = $service->spreadsheets_values->get( $spreadsheet_id, $range );
		$values   = $response->getValues();

		if ( ! empty( $values ) ) {
			if ( $hasheaderrow ) {
				$header_row = $values[0];
				// Remove the header row.
				unset( $values[0] );

				$i      = 0;
				$object = array();
				foreach ( $values as $row ) {
					$j            = 0;
					$object[ $i ] = array();

					foreach ( $row as $column ) {
						$object[ $i ][ $header_row[ $j ] ] = $column;
						++$j;
					}
					++$i;
				}
			} else {
				$object = $values;
			}
		}
	} else {
		$object = false;
	}

	$array = apply_filters( 'google_ss2db_before_save', $object, $worksheetid, $worksheetname, $sheetname );

	return $array;
}

/**
 * Saves data from a Google Spreadsheet to the database.
 *
 * @return array Contains details of the operation including the database row ID, date, worksheet identifiers, and operation result.
 */
function save_spreadsheet(): array {
	global $wpdb;
	$today = new DateTime();
	$today->setTimeZone( new DateTimeZone( get_option( 'timezone_string' ) ) );
	$date          = $today->format( 'Y-m-d H:i:s' );
	$title         = wp_unslash( $_POST['datatitle'] );
	$worksheetid   = wp_unslash( $_POST['worksheetid'] );
	$worksheetname = wp_unslash( $_POST['worksheetname'] );
	$sheetname     = wp_unslash( $_POST['sheetname'] );
	$hasheaderrow  = wp_unslash( $_POST['hasheaderrow'] );
	$value         = get_value_google_spreadsheet( $worksheetid, $worksheetname, $sheetname, $hasheaderrow );
	if ( get_option( 'google_ss2db_dataformat' ) === 'json-unescp' ) {
		$value = json_encode( $value, JSON_UNESCAPED_UNICODE );
	} else {
		$value = json_encode( $value );
	}
	$result = $wpdb->insert(
		GOOGLE_SS2DB_TABLE_NAME,
		array(
			'date'           => $date,
			'worksheet_id'   => $worksheetid,
			'worksheet_name' => $worksheetname,
			'sheet_name'     => $sheetname,
			'title'          => $title,
			'value'          => $value,
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);
	$rowid  = $wpdb->insert_id;

	$return = array(
		'id'             => $rowid,
		'date'           => $date,
		'worksheet_id'   => $worksheetid,
		'worksheet_name' => $worksheetname,
		'sheet_name'     => $sheetname,
		'title'          => $title,
		'value'          => $value,
		'result'         => $result,
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
