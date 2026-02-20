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
	require_once dirname( __DIR__, 4 ) . '/wp-load.php'; // phpcs:ignore PluginCheck.CodeAnalysis.DirectFileAccess.WPLoad -- This file is a direct form handler that self-loads WordPress.
}

// Sanitize and validate POST data using filter_input().
$nonce        = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$http_referer = filter_input( INPUT_POST, '_wp_http_referer', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

if (
	! $nonce ||
	! wp_verify_nonce( $nonce, 'google_ss2db' ) ||
	! isset( $_SERVER['REQUEST_METHOD'] ) ||
	'POST' !== $_SERVER['REQUEST_METHOD']
) {
	wp_die( 'Our Site is protected!!' );
}

// Sanitize all POST data.
$sanitized_post_data = array_map( fn( $value ) => is_string( $value ) ? sanitize_text_field( $value ) : $value, $_POST );

// Process and save spreadsheet data.
$data = google_ss2db_save_spreadsheet( $sanitized_post_data );
$data = apply_filters( 'google_ss2db_after_save', $data );

$bool    = (bool) $data['result'];
$referer = wp_unslash( $http_referer );
$referer = str_replace( '&settings-updated=true', '', $referer );
$referer = $referer . '&ss2dbupdated=' . $bool;

// Check if debug mode is enabled.
if ( defined( 'GOOGLE_SS2DB_DEBUG' ) && true === GOOGLE_SS2DB_DEBUG ) {
	// Return detailed debug information as JSON.
	header( 'Content-Type: application/json' );
	echo wp_json_encode(
		array(
			'result'    => $bool,
			'data'      => $data,
			'post_data' => $sanitized_post_data,
			'referer'   => $referer,
		),
		JSON_PRETTY_PRINT
	);
	exit;
}

// Default redirect for non-debug mode.
wp_redirect( $referer );
exit;
