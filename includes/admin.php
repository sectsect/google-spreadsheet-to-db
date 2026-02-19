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

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add a constant for the register_setting arguments at the top of the file.
const GOOGLE_SS2DB_SETTING_ARGS = array(
	'type'              => 'string',
	'sanitize_callback' => 'google_ss2db_sanitize_dataformat',
	'default'           => 'json',
);

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

add_action( 'admin_menu', 'google_ss2db_menu' );

/**
 * Registers the plugin menu in the WordPress admin dashboard.
 * Adds a new options page under the Settings menu specifically for the 'Google Spreadsheet to DB' plugin.
 *
 * @return void
 */
function google_ss2db_menu(): void {
	$page_hook_suffix = add_options_page(
		'Google Spreadsheet to DB',
		'Google Spreadsheet to DB',
		'manage_options',
		'google_ss2db_menu',
		'google_ss2db_options_page'
	);
	add_action( 'admin_print_styles-' . $page_hook_suffix, 'google_ss2db_admin_styles' );
	add_action( 'admin_print_scripts-' . $page_hook_suffix, 'google_ss2db_admin_scripts' );
	add_action( 'admin_init', 'register_google_ss2db_settings' );
}

/**
 * Enqueues custom styles for the admin options page of the plugin.
 * This function is hooked to the WordPress admin styles queue.
 *
 * @return void
 */
function google_ss2db_admin_styles(): void {
	$plugin_data    = google_ss2db_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	$version        = is_string( $plugin_version ) ? $plugin_version : '1.0.0';
	wp_enqueue_style( 'admin-options', plugin_dir_url( __DIR__ ) . 'assets/css/admin-options.css', array(), $version );
}

/**
 * Enqueues custom scripts for the admin options page of the plugin.
 * It also localizes the script to include nonce and plugin directory URL for secure AJAX calls.
 *
 * @return void
 */
function google_ss2db_admin_scripts(): void {
	$plugin_data    = google_ss2db_get_plugin_data();
	$plugin_version = $plugin_data['Version'];
	$version        = is_string( $plugin_version ) ? $plugin_version : '1.0.0';
	wp_enqueue_script( 'google-ss2db-script', plugin_dir_url( __DIR__ ) . 'assets/js/admin-options.js', array( 'jquery' ), $version, true );
	wp_localize_script(
		'google-ss2db-script',
		'google_ss2db_data',
		array(
			'nonce'          => wp_create_nonce( 'google_ss2db' ),
			'plugin_dir_url' => plugin_dir_url( __DIR__ ),
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
		)
	);
}

/**
 * Sanitizes the data format option for the Google Spreadsheet to DB plugin.
 *
 * This callback ensures that only allowed values ('json' or 'json-unescp') are saved.
 * If the input is empty or does not match one of the allowed values, an empty string is returned.
 *
 * @param mixed $value The value to sanitize.
 * @return string Sanitized value; returns an empty string if the value is not allowed.
 */
function google_ss2db_sanitize_dataformat( mixed $value ): string {
	$value   = sanitize_text_field( google_ss2db_cast_mixed_to_string( $value ) );
	$allowed = array( 'json', 'json-unescp' );
	if ( in_array( $value, $allowed, true ) ) {
		return $value;
	}
	return '';
}

/**
 * Registers settings for the Google Spreadsheet to DB plugin within the WordPress settings API.
 *
 * @return void
 */
function register_google_ss2db_settings(): void {
	register_setting(
		'google_ss2db-settings-group',
		'google_ss2db_dataformat',
		GOOGLE_SS2DB_SETTING_ARGS
	);
}

/**
 * Loads and displays the options page for the Google Spreadsheet to DB plugin.
 * This page allows users to configure settings specific to the plugin.
 *
 * @return void
 */
function google_ss2db_options_page(): void {
	require_once plugin_dir_path( __DIR__ ) . 'admin/class-recursivetable.php';
}

// These AJAX handlers should be added to a separate file (e.g., includes/admin-ajax.php).
add_action( 'wp_ajax_get_spreadsheet_entry_details', 'google_ss2db_get_entry_details' );
add_action( 'wp_ajax_delete_spreadsheet_entry', 'google_ss2db_delete_entry' );

/**
 * Retrieves details of a specific spreadsheet entry by its ID.
 *
 * @return void
 * @throws \JsonException If JSON decoding fails.
 */
function google_ss2db_get_entry_details(): void {
	check_ajax_referer( 'google_ss2db', 'nonce' );

	$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	if ( false === $id || null === $id ) {
		wp_send_json_error( 'Invalid ID' );
	}

	// Generate cache key.
	$cache_key = 'google_ss2db_entry_' . $id;

	// Retrieve entry from cache.
	$entry = wp_cache_get( $cache_key );

	if ( false === $entry ) {
		global $wpdb;
		$table_name = GOOGLE_SS2DB_TABLE_NAME;
		$sql        = "SELECT * FROM {$table_name} WHERE id = %d";
		$prepared   = $wpdb->prepare(
			$sql, // phpcs:ignore
			$id
		);
		$entry      = $wpdb->get_row( $prepared ); // phpcs:ignore

		// Save to cache for 1 hour.
		if ( $entry ) {
			wp_cache_set( $cache_key, $entry, 'google_ss2db', HOUR_IN_SECONDS );
		}
	}

	if ( $entry ) {
		try {
			$decoded_value = json_decode( $entry->value, true, 512, JSON_THROW_ON_ERROR );
			if ( ! is_array( $decoded_value ) ) {
				$decoded_value = array();
			}
			wp_send_json_success(
				array(
					'array' => array_values( $decoded_value ),
					'raw'   => wp_json_encode( $decoded_value, get_option( 'google_ss2db_dataformat' ) === 'json-unescp' ? JSON_UNESCAPED_UNICODE : 0 ),
				)
			);
		} catch ( \JsonException $e ) {
			wp_send_json_error( 'JSON decoding error: ' . $e->getMessage() );
		}
	} else {
		wp_send_json_error( 'Entry not found' );
	}
}

/**
 * Deletes a specific spreadsheet entry by its ID.
 *
 * @return void
 */
function google_ss2db_delete_entry(): void {
	check_ajax_referer( 'google_ss2db', 'nonce' );

	$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	if ( false === $id || null === $id ) {
		wp_send_json_error( 'Invalid ID' );
	}

	global $wpdb;
	$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		GOOGLE_SS2DB_TABLE_NAME,
		array( 'id' => $id ),
		array( '%d' )
	);

	// Delete corresponding cache entry.
	$cache_key = 'google_ss2db_entry_' . $id;
	wp_cache_delete( $cache_key, 'google_ss2db' );

	if ( $result ) {
		wp_send_json_success();
	} else {
		wp_send_json_error( 'Failed to delete the entry' );
	}
}
