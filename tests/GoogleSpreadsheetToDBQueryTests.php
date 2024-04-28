<?php
require_once __DIR__ . '/../includes/class-google-spreadsheet-to-db-query.php';

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
				'id'             => 1,
				'date'           => '2021-01-01',
				'title'          => 'Sample Data 1',
				'worksheet_name' => 'Sheet1',
			),
			(object) array(
				'id'             => 2,
				'date'           => '2021-01-02',
				'title'          => 'Sample Data 2',
				'worksheet_name' => 'Sheet1',
			),
			(object) array(
				'id'             => 3,
				'date'           => '2023-12-31',
				'title'          => 'Sample Data 3',
				'worksheet_name' => 'My Spreadsheet',
			),
			(object) array(
				'id'             => 4,
				'date'           => '2024-01-01',
				'title'          => 'Sample Data 4',
				'worksheet_name' => 'My Spreadsheet',
			),
			(object) array(
				'id'             => 5,
				'date'           => '2024-01-02',
				'title'          => 'Sample Data 5',
				'worksheet_name' => 'My Spreadsheet',
			),
			(object) array(
				'id'             => 6,
				'date'           => '2024-01-03',
				'title'          => 'Sample Data 6',
				'worksheet_name' => 'Sheet2',
			),
		);

		// Mocking wpdb.
		global $wpdb;
		$wpdb = $this->getMockBuilder( stdClass::class )
					->setMethods( array( 'prepare', 'get_results', 'query' ) )
					->getMock();

		// Adding properties to mock object to avoid undefined property warnings
		$wpdb->posts              = 'wp_posts';
		$wpdb->postmeta           = 'wp_postmeta';
		$wpdb->comments           = 'wp_comments';
		$wpdb->commentmeta        = 'wp_commentmeta';
		$wpdb->terms              = 'wp_terms';
		$wpdb->term_taxonomy      = 'wp_term_taxonomy';
		$wpdb->term_relationships = 'wp_term_relationships';
		$wpdb->termmeta           = 'wp_termmeta';
		$wpdb->users              = 'wp_users';
		$wpdb->usermeta           = 'wp_usermeta';

		$wpdb->method( 'prepare' )->will(
			$this->returnCallback(
				function ( $query, ...$params ) {
					return $query;
				}
			)
		);
		$wpdb->method('get_results')->will(
			$this->returnCallback(
				function ($query) use ($mock_api_response) {
					// オフセットと制限に基づくフィルタリング
					if (strpos($query, 'LIMIT') !== false) {
						$limit = intval(explode(',', explode('LIMIT', $query)[1])[1]);
						$offset = intval(explode(',', explode('LIMIT', $query)[1])[0]);
						return array_slice($mock_api_response, $offset, $limit);
					}
					// ワークシート名に基づくフィルタリング
					if (strpos($query, 'WHERE worksheet_name =') !== false) {
						$worksheet_name = str_replace(["SELECT * FROM data WHERE worksheet_name = '", "'"], "", $query);
						return array_filter($mock_api_response, function ($item) use ($worksheet_name) {
							return $item->worksheet_name === $worksheet_name;
						});
					}
					// 日付に基づくフィルタリング
					if (strpos($query, 'WHERE date >=') !== false) {
						$date = str_replace(["SELECT * FROM data WHERE date >= '", "'"], "", $query);
						return array_filter($mock_api_response, function ($item) use ($date) {
							return $item->date >= $date;
						});
					}
					// 複数の条件に基づくフィルタリング
					if (strpos($query, 'WHERE worksheet_name =') !== false && strpos($query, 'AND date >=') !== false) {
						$worksheet_name = str_replace(["SELECT * FROM data WHERE worksheet_name = '", "' AND date >= '"], "", explode("' AND date >= '", $query)[0]);
						$date = str_replace("'", "", explode("' AND date >= '", $query)[1]);
						return array_filter($mock_api_response, function ($item) use ($worksheet_name, $date) {
							return $item->worksheet_name === $worksheet_name && $item->date >= $date;
						});
					}

					// 他の条件に基づくフィルタリングも同様に実装
					return $mock_api_response;
				}
			)
		);

		// Adjusting the instantiation to include parameters if needed.
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query();
	}

	/**
	 * Tests retrieving rows starting from the 4th row in ascending order by ID.
	 */
	public function testGetRowsFromFourth() {
		$args                                 = array(
			'orderby' => 'id',
			'order'   => 'ASC',
			'limit'   => 3,
			'offset'  => 3,
		);
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query( $args );
		$results                              = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertCount( 3, $results );
	}

	/**
	 * Tests retrieving a row with a specific ID.
	 */
	public function testGetRowById() {
		$args                                 = array(
			'where' => array(
				array(
					'key'   => 'id',
					'value' => 2,
				),
			),
		);
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query( $args );
		$result                               = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertEquals( '2021-01-02', $result->date );
		$this->assertEquals( 'Sample Data 2', $result->title );
	}

	/**
	 * Tests retrieving rows from a specific worksheet sorted by ID.
	 */
	public function testGetRowsFromSpecificWorksheet() {
		$args                                 = array(
			'orderby' => 'id',
			'order'   => 'ASC',
			'limit'   => 3,
			'where'   => array(
				array(
					'key'     => 'worksheet_name',
					'value'   => 'My Spreadsheet',
					'compare' => '=',
				),
			),
		);
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query( $args );
		$results                              = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertCount( 3, $results );
	}

	/**
	 * Tests retrieving rows with a date greater than a specified datetime.
	 */
	public function testGetRowsAfterDate() {
		$args                                 = array(
			'where' => array(
				array(
					'key'     => 'date',
					'value'   => '2024-01-01 00:00:00',
					'compare' => '>=',
				),
			),
		);
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query( $args );
		$results                              = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertNotEmpty( $results );
		$this->assertCount( 3, $results );
	}

	/**
	 * Tests retrieving rows based on multiple conditions.
	 */
	public function testGetRowsWithMultipleConditions() {
		$args                                 = array(
			'where' => array(
				'relation' => 'AND',
				array(
					'key'     => 'date',
					'value'   => '2024-01-01 00:00:00',
					'compare' => '>=',
				),
				array(
					'key'     => 'worksheet_name',
					'value'   => 'My Spreadsheet',
					'compare' => '=',
				),
			),
		);
		$this->google_spreadsheet_to_db_query = new Google_Spreadsheet_To_DB_Query( $args );
		$results                              = $this->google_spreadsheet_to_db_query->getrow();
		$this->assertNotEmpty( $results );
		$this->assertCount( 2, $results );
	}
}
