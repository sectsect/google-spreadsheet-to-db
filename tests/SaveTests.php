<?php
require_once __DIR__ . '/../includes/save.php';

/**
 * Tests the functionality of saving a spreadsheet.
 *
 * @coversDefaultClass SaveSpreadsheet
 */
class SaveSpreadsheetTest extends WP_UnitTestCase {
	private $plugin_slug    = 'google_ss2db';
	private $worksheet_id   = 'testWorksheetId';
	private $worksheet_name = 'testWorksheetName';
	private $sheet_name     = 'testSheetName';
	private $has_header_row = true;
	private $post_data      = array();

	/**
	 * Sets up the environment for each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->post_data           = array(
			'nonce'            => wp_create_nonce( 'google_ss2db' ),
			'datatitle'        => 'Test Data Title',
			'worksheetid'      => $this->worksheet_id,
			'worksheetname'    => $this->worksheet_name,
			'sheetname'        => $this->sheet_name,
			'hasheaderrow'     => $this->has_header_row,
			'_wp_http_referer' => '/test-referer',
		);
		$_POST                     = $this->post_data;
		$_SERVER['REQUEST_METHOD'] = 'POST';
	}

	/**
	 * Resets the environment after each test.
	 */
	public function tearDown() {
		parent::tearDown();
		unset( $_POST );
		unset( $_SERVER['REQUEST_METHOD'] );
	}

	/**
	 * Tests the save_spreadsheet function.
	 *
	 * @covers ::save_spreadsheet
	 */
	public function test_save_spreadsheet() {
		// Mock global wpdb.
		global $wpdb;
		$wpdb            = $this->getMockBuilder( 'wpdb' )
					->setMethods( array( 'insert', 'insert_id' ) )
					->getMock();
		$wpdb->insert_id = 1;

		// Expectations for wpdb->insert.
		$wpdb->expects( $this->once() )
			->method( 'insert' )
			->with(
				$this->equalTo( GOOGLE_SS2DB_TABLE_NAME ),
				$this->anything(),
				$this->equalTo( array( '%s', '%s', '%s', '%s', '%s', '%s' ) )
			)
			->will( $this->returnValue( 1 ) );

		// Call the function.
		$result = save_spreadsheet();

		// Assertions.
		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['id'] );
		$this->assertEquals( $this->worksheet_id, $result['worksheet_id'] );
		$this->assertEquals( $this->worksheet_name, $result['worksheet_name'] );
		$this->assertEquals( $this->sheet_name, $result['sheet_name'] );
		$this->assertEquals( 'Test Data Title', $result['title'] );
		$this->assertTrue( $result['result'] );
	}
}
