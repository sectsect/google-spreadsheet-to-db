<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.ilovesect.com/
 * @since      1.0.0
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */

declare(strict_types=1);

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */
class Google_Spreadsheet_To_DB_Activator {
	/**
	 * Activates the plugin by creating or updating the necessary database table.
	 *
	 * This method checks the current installed version of the plugin and updates the database schema if required.
	 * It creates a new table if it doesn't exist or updates the existing table to match the new schema version.
	 * This ensures that the plugin's database table is always up to date with the current version.
	 *
	 * @since    1.0.0
	 */
	public static function activate(): void {
		global $wpdb;
		$google_ss2db_db_version = '1.2.0';
		$installed_ver           = get_option( 'google_ss2db_version' );
		$charset_collate         = $wpdb->get_charset_collate();
		if ( $installed_ver !== $google_ss2db_db_version ) {
			$sql = 'CREATE TABLE ' . GOOGLE_SS2DB_TABLE_NAME . " (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				worksheet_id text NOT NULL,
				worksheet_name text NOT NULL,
				sheet_name text NOT NULL,
				title text NOT NULL,
				value LONGTEXT NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";
			// @phpstan-ignore-next-line
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			update_option( 'google_ss2db_version', $google_ss2db_db_version );
		}
	}
}
