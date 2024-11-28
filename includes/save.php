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

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

// Sanitize and validate POST data using filter_input().
$nonce        = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$http_referer = filter_input( INPUT_POST, '_wp_http_referer', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

if ( ! $nonce || ! wp_verify_nonce( $nonce, 'google_ss2db' ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	wp_die( 'Our Site is protected!!' );
}

$sanitized_post_data = array_map( fn( $value ) => is_string( $value ) ? sanitize_text_field( $value ) : $value, $_POST );
$data                = google_ss2db_save_spreadsheet( $sanitized_post_data );
$data                = apply_filters( 'google_ss2db_after_save', $data );

$bool    = (bool) $data['result'];
$referer = wp_unslash( $http_referer );
$referer = str_replace( '&settings-updated=true', '', $referer );
$referer = $referer . '&ss2dbupdated=' . $bool;
wp_redirect( $referer );
exit;
