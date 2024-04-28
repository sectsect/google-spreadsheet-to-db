<?php
/**
 * Class Test_Google_Spreadsheet_To_DB_Query
 * Tests the Google_Spreadsheet_To_DB_Query class functionality.
 */
class Test_Google_Spreadsheet_To_DB_Query extends WP_UnitTestCase {
	/**
	 * @var Google_Spreadsheet_To_DB_Query Instance of Google_Spreadsheet_To_DB_Query to test.
	 */
	private $google_spreadsheet_to_db_query;

	/**
	 * Sets up the environment for each test.
	 * Mocks the global $wpdb and prepares a mock response for Google Sheets API.
	 */
	public function setUp(): void {
		parent::setUp();

		// Mock response from Google Sheets API.
		$mock_api_response = array(
			(object) array(
				'id'    => 1,
				'date'  => '2021-01-01',
				'title' => 'Sample Data 1',
			),
			(object) array(
				'id'    => 2,
				'date'  => '2021-01-02',
				'title' => 'Sample Data 2',
			),
		);

		// Mocking wpdb.
		global $wpdb;
		$wpdb = $this->getMockBuilder( stdClass::class )
					->setMethods( array( 'prepare', 'get_results' ) )
					->getMock();

		$wpdb->method( 'prepare' )->will(
			$this->returnCallback(
				function ( $query, $limit, $offset ) {
					return $query;
				}
			)
		);
		$wpdb->method( 'get_results' )->willReturn( $mock_api_response );

		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query();
	}

	/**
	 * Tests the getrow method of Google_Spreadsheet_To_DB_Query.
	 * Ensures it returns the correct number of results and data.
	 */
	public function testGetRow() {
		$results = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertCount( 2, $results );
		$this->assertEquals( '2021-01-01', $results[0]->date );
		$this->assertEquals( 'Sample Data 1', $results[0]->title );
	}
}
