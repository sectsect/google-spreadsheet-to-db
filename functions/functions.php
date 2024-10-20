<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://github.com/sectsect/
 * @since      1.0.0
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */

/**
 * Register functions for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . 'composer/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'composer/vendor/autoload.php';
}

/**
 * Displays pagination links based on the provided parameters.
 *
 * This function generates HTML for a simple pagination interface, based on the current page,
 * total number of pages, and the range of pages to display around the current page.
 *
 * @param int $paged Current page number, defaults to 1.
 * @param int $pages Total number of pages, defaults to 1.
 * @param int $range Number of pages to display around the current page, defaults to 2.
 * @return void
 */
function google_ss2db_options_pagination( int $paged = 1, int $pages = 1, int $range = 2 ): void {
	$paged     = intval( $paged );
	$pages     = intval( $pages );
	$range     = intval( $range );
	$showitems = ( $range * 2 ) + 1;

	if ( 1 !== $pages ) {
		echo '<ul class="pagination">';
		if ( 2 < $paged && $paged > $range + 1 && $showitems < $pages ) {
			echo '<li class="first"><a href="' . get_pagenum_link( 1 ) . '">&laquo;</a></li>';
		}
		if ( 1 < $paged && $showitems < $pages ) {
			echo '<li class="prevnext"><a href="' . get_pagenum_link( $paged - 1 ) . '">&lsaquo;</a></li>';
		}
		for ( $i = 1; $i <= $pages; $i++ ) {
			if ( 1 !== $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				echo ( $paged === $i ) ? '<li class="current"><span>' . $i . '</span></li>' : '<li><a href="' . get_pagenum_link( $i ) . '">' . $i . '</a></li>';
			}
		}
		if ( $paged < $pages && $showitems < $pages ) {
			echo '<li class="prevnext"><a href="' . get_pagenum_link( $paged + 1 ) . '">&rsaquo;</a></li>';
		}
		if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
			echo '<li class="last"><a href="' . get_pagenum_link( $pages ) . '">&raquo;</a></li>';
		}
		echo '</ul>';
	}
}

/**
 * Truncates a string by removing characters from the middle and replacing them with an ellipsis.
 *
 * This function is useful for creating previews or shortening strings without losing the beginning
 * and end of the string. It ensures the string does not exceed the maximum specified length.
 *
 * @param string $str The string to truncate.
 * @param int    $max_chars Maximum number of characters to retain, defaults to 16.
 * @return string The truncated string.
 */
function google_ss2db_truncate_middle( string $str, int $max_chars = 16 ): string {
	$str_length = strlen( $str );

	return substr_replace( $str, '...', (int) floor( $max_chars / 2 ), $str_length - $max_chars );
}

/**
 * Retrieves plugin data from the main plugin file.
 * This function fetches data such as version number and plugin name from the plugin's main file.
 *
 * @return array<string, mixed> The plugin data.
 */
function google_ss2db_get_plugin_data(): array {
	$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . 'google-spreadsheet-to-db.php' );
	return $plugin_data;
}

/**
 * Checks if a specific sheet exists within a list of sheets.
 *
 * @param Google_Service_Sheets_Sheet[] $sheets_list An array of sheet objects.
 * @param string                        $sheet_name The name of the sheet to check for.
 * @return bool Returns true if the sheet exists, false otherwise.
 */
function google_ss2db_exist_sheet( array $sheets_list, string $sheet_name ): bool {
	foreach ( $sheets_list as $sheet ) {
		if ( $sheet->getProperties()->getTitle() === $sheet_name ) {
			return true;
		}
	}
	return false;
}

/**
 * Creates and returns a Google_Client authorized for Google Sheets and Drive APIs.
 *
 * @return Google_Client The authorized client object.
 * @throws Exception If an error occurs during Google Client initialization.
 */
function google_ss2db_get_client(): Google_Client {
	$client_secret = ( defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) ) ? GOOGLE_SS2DB_CLIENT_SECRET_PATH : '';

	if ( empty( $client_secret ) ) {
		wp_die( 'Client secret path is not set. Please check GOOGLE_SS2DB_CLIENT_SECRET_PATH.' );
	}

	if ( ! file_exists( $client_secret ) ) {
		wp_die( 'Client secret file not found: ' . $client_secret );
	}

	putenv( 'GOOGLE_APPLICATION_CREDENTIALS=' . $client_secret );

	try {
		$client = new Google_Client();
		$client->setApplicationName( 'Google Sheets API PHP Quickstart' );
		$client->setScopes( array( Google_Service_Sheets::SPREADSHEETS, Google_Service_Sheets::DRIVE ) );
		$client->setAuthConfig( $client_secret );
		$client->setAccessType( 'offline' );
	} catch ( Exception $e ) {
		wp_die( 'Error occurred during Google Client initialization: ' . $e->getMessage() );
	}

	return $client;
}

/**
 * Processes spreadsheet data with a header row.
 *
 * @param array<int, array<int, string>> $values The raw spreadsheet values.
 * @return array<int, array<string, string|null>> Processed data with header keys.
 */
function google_ss2db_process_with_header( array $values ): array {
	if ( empty( $values ) ) {
		return array();
	}

	$header = array_shift( $values );
	return array_map(
		function ( $row ) use ( $header ) {
			return array_combine( $header, array_pad( $row, count( $header ), null ) );
		},
		$values
	);
}

/**
 * Retrieves data from a specified Google Spreadsheet.
 *
 * @param string $worksheet_id The ID of the Google Spreadsheet.
 * @param string $worksheet_name The name of the Google Spreadsheet.
 * @param string $sheet_name The name of the individual sheet within the Spreadsheet.
 * @param bool   $has_header_row Indicates if the spreadsheet contains a header row.
 * @return array<int|string, mixed> The spreadsheet data as an associative array.
 * @throws Exception If the specified sheet does not exist or if there's an error retrieving data.
 */
function google_ss2db_get_value_google_spreadsheet( string $worksheet_id, string $worksheet_name, string $sheet_name, bool $has_header_row ): array {
	try {
		$client  = google_ss2db_get_client();
		$service = new Google_Service_Sheets( $client );

		$spreadsheet = $service->spreadsheets->get( $worksheet_id );
		$sheets      = $spreadsheet->getSheets();

		if ( ! google_ss2db_exist_sheet( $sheets, $sheet_name ) ) {
			wp_die( 'The specified sheet does not exist. Please check the sheet name.' );
		}

		$response = $service->spreadsheets_values->get( $worksheet_id, $sheet_name );
		$values   = $response->getValues();

		$data = $has_header_row ? google_ss2db_process_with_header( $values ) : $values;

		return apply_filters( 'google_ss2db_before_save', $data, $worksheet_id, $worksheet_name, $sheet_name );
	} catch ( Exception $e ) {
		error_log( 'Error in google_ss2db_get_value_google_spreadsheet: ' . $e->getMessage() );
		throw $e;
	}
}

/**
 * Saves data from a Google Spreadsheet to the database.
 *
 * @param array<string> $post_data POST data containing spreadsheet information.
 * @return array<string> Contains details of the operation including the database row ID, date, worksheet identifiers, and operation result.
 */
function google_ss2db_save_spreadsheet( array $post_data ): array {
	global $wpdb;
	$today           = new DateTime();
	$timezone_string = wp_timezone_string();
	if ( empty( $timezone_string ) ) {
		wp_die( __( 'Error: Timezone is not set. Please check your WordPress settings.', 'google_ss2db' ) );
	}
	$today->setTimeZone( new DateTimeZone( $timezone_string ) );
	$date           = $today->format( 'Y-m-d H:i:s' );
	$title          = wp_unslash( $post_data['datatitle'] ?? '' );
	$worksheet_id   = wp_unslash( $post_data['worksheetid'] ?? '' );
	$worksheet_name = wp_unslash( $post_data['worksheetname'] ?? '' );
	$sheet_name     = wp_unslash( $post_data['sheetname'] ?? '' );
	$has_header_row = filter_var( wp_unslash( $post_data['hasheaderrow'] ?? false ), FILTER_VALIDATE_BOOLEAN );
	$value          = google_ss2db_get_value_google_spreadsheet( $worksheet_id, $worksheet_name, $sheet_name, $has_header_row );
	$value          = json_encode( $value, get_option( 'google_ss2db_dataformat' ) === 'json-unescp' ? JSON_UNESCAPED_UNICODE : 0 );

	$result = $wpdb->insert(
		GOOGLE_SS2DB_TABLE_NAME,
		array(
			'date'           => $date,
			'worksheet_id'   => $worksheet_id,
			'worksheet_name' => $worksheet_name,
			'sheet_name'     => $sheet_name,
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
	$row_id = $wpdb->insert_id;

	return array(
		'id'             => $row_id,
		'date'           => $date,
		'worksheet_id'   => $worksheet_id,
		'worksheet_name' => $worksheet_name,
		'sheet_name'     => $sheet_name,
		'title'          => $title,
		'value'          => $value,
		'result'         => $result,
	);
}
