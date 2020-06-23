<?php
/**
 * Plugin Name:     Google Spreadsheet to DB
 * Plugin URI:      https://github.com/sectsect/google-spreadsheet-to-db
 * Description:     Pulls Google Spreadsheet data via Google’s API and saves it in your WordPress database
 * Author:          SECT INTERACTIVE AGENCY
 * Author URI:      https://www.ilovesect.com/
 * Text Domain:     google-spreadsheet-to-db
 * Domain Path:     /languages
 * Version:         1.2.3
 *
 * @package         Google_Spreadsheet_to_DB
 */

$google_ss2db_minimalrequiredphpversion = '5.5';

global $wpdb;
define( 'GOOGLE_SS2DB_TABLE_NAME', $wpdb->prefix . 'google_ss2db' );

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version.
 *
 * @return void "description".
 */
function google_ss2db_noticephpversionwrong() {
	global $google_ss2db_minimalrequiredphpversion;
	echo '<div class="updated fade">' .
	__( 'Error: plugin "Google Spreadsheet to DB" requires a newer version of PHP to be running.', 'google_ss2db' ) .
			'<br/>' . __( 'Minimal version of PHP required: ', 'google_ss2db' ) . '<strong>' . $google_ss2db_minimalrequiredphpversion . '</strong>' .
			'<br/>' . __( 'Your server\'s PHP version: ', 'google_ss2db' ) . '<strong>' . phpversion() . '</strong>' .
		'</div>';
}

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version.
 *
 * @return boolean "description".
 */
function google_ss2db_phpversioncheck() {
	global $google_ss2db_minimalrequiredphpversion;
	if ( version_compare( phpversion(), $google_ss2db_minimalrequiredphpversion ) < 0 ) {
		add_action( 'admin_notices', 'google_ss2db_noticephpversionwrong' );
		return false;
	}
	return true;
}

/**
 * The code that runs during plugin activation.
 *
 * @return void "description".
 */
function activate_google_ss2db() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-spreadsheet-to-db-activator.php';
	Google_Spreadsheet_To_DB_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_google_ss2db' );

/**
 * Load the textdomain.
 *
 * @return void "description".
 */
function google_ss2db_load_textdomain() {
	load_plugin_textdomain( 'google_ss2db', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'google_ss2db_load_textdomain' );

/**
 * Add my meta data to row.
 *
 * @param  array  $plugin_meta "description".
 * @param  string $plugin_file "description".
 * @param  string $plugin_data "description".
 * @param  string $status      "description".
 * @return statement           "description".
 */
function google_ss2db_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		$plugin_meta[] = '<a href="https://github.com/sectsect/google-spreadsheet-to-db" target="_blank"><span class="dashicons dashicons-randomize"></span> GitHub</a>';
		return $plugin_meta;
	}
}
add_filter( 'plugin_row_meta', 'google_ss2db_row_meta', 10, 4 );


if ( google_ss2db_phpversioncheck() ) {
	require_once plugin_dir_path( __FILE__ ) . 'functions/functions.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/index.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-spreadsheet-to-db-query.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
}
