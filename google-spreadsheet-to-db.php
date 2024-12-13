<?php
/**
 * Plugin Name:     Google Spreadsheet to DB
 * Plugin URI:      https://github.com/sectsect/google-spreadsheet-to-db
 * Description:     Read Google Sheets data with Sheets API and insert into WordPress DB - An alternative to CSV Import
 * Author:          sect
 * Author URI:      https://github.com/sectsect/google-spreadsheet-to-db
 * Text Domain:     google-spreadsheet-to-db
 * Domain Path:     /languages
 * Version:         6.11.4
 * License:         GPL-3.0-or-later
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package         Google_Spreadsheet_to_DB
 */

declare(strict_types=1);

$google_ss2db_minimalrequiredphpversion = '8.0';

global $wpdb;
define( 'GOOGLE_SS2DB_TABLE_NAME', $wpdb->prefix . 'google_ss2db' );

/**
 * Displays an admin notice if the server's PHP version is below the plugin's required PHP version.
 */
function google_ss2db_noticephpversionwrong(): void {
	global $google_ss2db_minimalrequiredphpversion;
	$required_version = is_null( $google_ss2db_minimalrequiredphpversion ) ? 'unknown' : $google_ss2db_minimalrequiredphpversion;
	$message          = sprintf(
		'<div class="updated fade">%s<br/>%s<strong>%s</strong><br/>%s<strong>%s</strong></div>',
		esc_html__( 'Error: plugin "Google Spreadsheet to DB" requires a newer version of PHP to be running.', 'google-spreadsheet-to-db' ),
		esc_html__( 'Minimal version of PHP required: ', 'google-spreadsheet-to-db' ),
		wp_kses_post( $required_version ),
		esc_html__( 'Your server\'s PHP version: ', 'google-spreadsheet-to-db' ),
		wp_kses_post( phpversion() )
	);
	echo wp_kses_post( $message );
}

/**
 * Checks the server's PHP version against the plugin's required PHP version.
 *
 * @return bool True if the PHP version is compatible, false otherwise.
 */
function google_ss2db_phpversioncheck(): bool {
	global $google_ss2db_minimalrequiredphpversion;
	if ( null === $google_ss2db_minimalrequiredphpversion || version_compare( phpversion(), $google_ss2db_minimalrequiredphpversion ) < 0 ) {
		add_action( 'admin_notices', 'google_ss2db_noticephpversionwrong' );
		return false;
	}
	return true;
}

/**
 * Runs during plugin activation to set up initial settings or structures.
 *
 * @return void
 */
function activate_google_ss2db(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-spreadsheet-to-db-activator.php';
	Google_Spreadsheet_To_DB_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_google_ss2db' );

/**
 * Loads the plugin's text domain for internationalization.
 *
 * @return void
 */
function google_ss2db_load_textdomain(): void {
	load_plugin_textdomain( 'google-spreadsheet-to-db', false, plugin_basename( __DIR__ ) . '/languages' );
}
add_action( 'plugins_loaded', 'google_ss2db_load_textdomain' );

/**
 * Adds a link to the plugin's GitHub page in the plugin meta row.
 *
 * @param string[] $plugin_meta An array of the plugin's metadata.
 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
 * @param string[] $plugin_data An array of plugin data.
 * @param string   $status Status of the plugin.
 * @return string[] Modified plugin meta data.
 */
function google_ss2db_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		$plugin_meta[] = '<a href="https://github.com/sectsect/google-spreadsheet-to-db" target="_blank"><span class="dashicons dashicons-randomize"></span> GitHub</a>';
		return $plugin_meta;
	}

	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'google_ss2db_row_meta', 10, 4 );


if ( google_ss2db_phpversioncheck() ) {
	require_once plugin_dir_path( __FILE__ ) . 'functions/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/index.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-spreadsheet-to-db-query.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
}
