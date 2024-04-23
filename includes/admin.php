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
 * Registers the plugin menu in the WordPress admin dashboard.
 * This function adds a new options page under the Settings menu.
 */
function google_ss2db_menu() {
	$page_hook_suffix = add_options_page( 'Google Spreadsheet to DB', '<img src="' . plugins_url( 'assets/images/ss_logo.svg', __DIR__ ) . '" width="12" height="16" /> Google Spreadsheet to DB', 'manage_options', 'google_ss2db_menu', 'google_ss2db_options_page' );
	add_action( 'admin_print_styles-' . $page_hook_suffix, 'google_ss2db_admin_styles' );
	add_action( 'admin_print_scripts-' . $page_hook_suffix, 'google_ss2db_admin_scripts' );
	add_action( 'admin_init', 'register_google_ss2db_settings' );
}

/**
 * Enqueues custom styles for the admin options page.
 * This function is hooked to the WordPress admin styles queue.
 */
function google_ss2db_admin_styles() {
	wp_enqueue_style( 'admin-options', plugin_dir_url( __DIR__ ) . 'assets/css/admin-options.css', array() );
}

/**
 * Enqueues custom scripts for the admin options page.
 * This function is hooked to the WordPress admin scripts queue.
 * It also localizes the script to include nonce and plugin directory URL.
 */
function google_ss2db_admin_scripts() {
	wp_enqueue_script( 'google-ss2db-script', plugin_dir_url( __DIR__ ) . 'assets/js/admin-options.js', array( 'jquery' ), null, true );
	wp_localize_script(
		'google-ss2db-script',
		'google_ss2db_data',
		array(
			'nonce'          => wp_create_nonce( 'google_ss2db' ),
			'plugin_dir_url' => plugin_dir_url( __DIR__ ),
		)
	);
}

/**
 * Registers settings for the Google Spreadsheet to DB plugin.
 * This function adds a new setting to the WordPress settings API.
 */
function register_google_ss2db_settings() {
	register_setting( 'google_ss2db-settings-group', 'google_ss2db_dataformat' );
}

/**
 * Includes the options page for the Google Spreadsheet to DB plugin.
 * This function loads an external PHP class file that handles the display of the options page.
 */
function google_ss2db_options_page() {
	require_once plugin_dir_path( __DIR__ ) . 'admin/class-recursivetable.php';
}
