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

add_action( 'admin_menu', 'google_ss2db_menu' );

/**
 * Add options page.
 *
 * @return void "description".
 */
function google_ss2db_menu() {
	$page_hook_suffix = add_options_page( 'Google Spreadsheet to DB', '<img src="' . plugins_url( 'admin/images/ss_logo.svg', dirname( __FILE__ ) ) . '" width="12" height="16" /> Google Spreadsheet to DB', 'manage_options', 'google_ss2db_menu', 'google_ss2db_options_page' );
	add_action( 'admin_print_styles-' . $page_hook_suffix, 'google_ss2db_admin_styles' );
	add_action( 'admin_print_scripts-' . $page_hook_suffix, 'google_ss2db_admin_scripts' );
	add_action( 'admin_init', 'register_google_ss2db_settings' );
}

/**
 * Enqueue style.
 *
 * @return void "description".
 */
function google_ss2db_admin_styles() {
	wp_enqueue_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', array() );
	wp_enqueue_style( 'sweetalert2', '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.6.6/sweetalert2.min.css', array() );
	wp_enqueue_style( 'admin-options', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/admin-options.css', array() );
}

/**
 * Enqueue script.
 *
 * @return void "description".
 */
function google_ss2db_admin_scripts() {
	wp_enqueue_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'sweetalert2', '//cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.6.6/sweetalert2.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'google-ss2db-script', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/script.js', array( 'select2' ) );
	wp_localize_script(
		'google-ss2db-script',
		'google_ss2db_data',
		array(
			'nonce'          => wp_create_nonce( 'google_ss2db' ),
			'plugin_dir_url' => plugin_dir_url( dirname( __FILE__ ) ),
		)
	);
}

/**
 * Register setting.
 *
 * @return void "description".
 */
function register_google_ss2db_settings() {
	register_setting( 'google_ss2db-settings-group', 'google_ss2db_json_path' );
	register_setting( 'google_ss2db-settings-group', 'google_ss2db_worksheetname' );
	register_setting( 'google_ss2db-settings-group', 'google_ss2db_sheetname' );
	register_setting( 'google_ss2db-settings-group', 'google_ss2db_dataformat' );
}

/**
 * Require file.
 *
 * @return void "description".
 */
function google_ss2db_options_page() {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/index.php';
}
