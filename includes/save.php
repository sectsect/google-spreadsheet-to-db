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

$data = google_ss2db_save_spreadsheet( $_POST );
$data = apply_filters( 'google_ss2db_after_save', $data );

$bool    = (bool) $data['result'];
$referer = wp_unslash( $_POST['_wp_http_referer'] );
$referer = str_replace( '&settings-updated=true', '', $referer );
$referer = $referer . '&ss2dbupdated=' . $bool;
wp_redirect( $referer );
exit;
