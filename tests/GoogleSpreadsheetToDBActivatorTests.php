<?php
require_once __DIR__ . '/../includes/class-google-spreadsheet-to-db-activator.php';

/**
 * Class for testing the Google_Spreadsheet_To_DB_Activator activation functionality.
 *
 * This class extends WP_UnitTestCase to test the activation method of the Google_Spreadsheet_To_DB_Activator class.
 */

class Test_Google_Spreadsheet_To_DB_Activator extends WP_UnitTestCase {

	/**
	 * Test the activate method when no previous version is installed.
	 */
	public function test_activate_no_previous_version() {
		global $wpdb;

		// Simulate the environment where no previous version is installed.
		delete_option( 'google_ss2db_version' );

		// Call the activate method.
		Google_Spreadsheet_To_DB_Activator::activate();

		// Check if the new version is updated in the database.
		$installed_ver = get_option( 'google_ss2db_version' );
		$this->assertEquals( '1.2.0', $installed_ver );

		// Check if the table is created.
		$table_name        = GOOGLE_SS2DB_TABLE_NAME;
		$check_table_query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		$this->assertNotEmpty( $wpdb->get_var( $check_table_query ) ); // phpcs:ignore
	}

	/**
	 * Test the activate method when the installed version is outdated.
	 */
	public function test_activate_outdated_version() {
		global $wpdb;

		// Set an outdated version.
		update_option( 'google_ss2db_version', '1.0.0' );

		// Call the activate method.
		Google_Spreadsheet_To_DB_Activator::activate();

		// Check if the version is updated.
		$installed_ver = get_option( 'google_ss2db_version' );
		$this->assertEquals( '1.2.0', $installed_ver );

		// Check if the table exists and possibly updated.
		$table_name        = GOOGLE_SS2DB_TABLE_NAME;
		$check_table_query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		$this->assertNotEmpty( $wpdb->get_var( $check_table_query ) ); // phpcs:ignore
	}
}
