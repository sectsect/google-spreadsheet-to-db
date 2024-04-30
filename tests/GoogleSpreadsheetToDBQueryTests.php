<?php
/**
 * Tests for the Google_Spreadsheet_To_DB_Query class.
 *
 * @package Google_Spreadsheet_To_DB_Query
 */

// Require the class file.
require_once __DIR__ . '/../includes/class-google-spreadsheet-to-db-query.php';

/**
 * Class Test_Google_Spreadsheet_To_DB_Query
 * Tests the Google_Spreadsheet_To_DB_Query class functionality.
 *
 * @covers Google_Spreadsheet_To_DB_Query
 */
class Google_Spreadsheet_To_DB_Query_Test extends WP_UnitTestCase {
	/**
	 * Mock data for testing.
	 *
	 * @var array
	 */
	protected $mock_data;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		// Mock response from Google Sheets API.
		$this->mock_data = array(
			array(
				'id'             => 4,
				'date'           => '2023-06-04 13:00:00',
				'worksheet_id'   => 'sheet2',
				'worksheet_name' => 'Sheet 2',
				'sheet_name'     => 'Data 4',
				'title'          => 'Title 4',
				'value'          => '{"key4":"value4"}',
			),
			array(
				'id'             => 3,
				'date'           => '2023-06-03 12:00:00',
				'worksheet_id'   => 'sheet2',
				'worksheet_name' => 'Sheet 2',
				'sheet_name'     => 'Data 3',
				'title'          => 'Title 3',
				'value'          => '{"key3":"value3"}',
			),
			array(
				'id'             => 2,
				'date'           => '2023-06-02 11:00:00',
				'worksheet_id'   => 'sheet1',
				'worksheet_name' => 'Sheet 1',
				'sheet_name'     => 'Data 2',
				'title'          => 'Title 2',
				'value'          => '{"key2":"value2"}',
			),
			array(
				'id'             => 1,
				'date'           => '2023-06-01 10:00:00',
				'worksheet_id'   => 'sheet1',
				'worksheet_name' => 'Sheet 1',
				'sheet_name'     => 'Data 1',
				'title'          => 'Title 1',
				'value'          => '{"key1":"value1"}',
			),
		);
	}

	/**
	 * Test getting all rows.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_all_rows() {
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
			->method( 'getrow' )
			->willReturn( $this->mock_data );

		$rows = $sheet->getrow();
		$this->assertCount( 4, $rows );
	}

	/**
	 * Test getting 3 rows starting from the 4th row, ordered by ID in ascending order.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_3_rows_from_4th_ascending_by_id() {
		$args  = array(
			'orderby' => 'id',
			'order'   => 'ASC',
			'limit'   => 3,
			'offset'  => 3,
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
				->method( 'getrow' )
				->willReturn( array_slice( $this->mock_data, 3, 3 ) );

		$rows = $sheet->getrow();
		$this->assertCount( 1, $rows );
		$first = reset( $rows );
		$this->assertEquals( 1, $first['id'] );
	}

	/**
	 * Test getting a row with a specific ID.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_row_with_specific_id() {
		$args  = array(
			'where' => array(
				array(
					'key'   => 'id',
					'value' => 3,
				),
			),
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
				->method( 'getrow' )
				->willReturn(
					array_filter(
						$this->mock_data,
						function ( $row ) {
							return 3 === $row['id'];
						}
					)
				);

		$rows = $sheet->getrow();
		$this->assertCount( 1, $rows );
		$first = reset( $rows );
		$this->assertEquals( 3, $first['id'] );
	}

	/**
	 * Test getting 3 rows with a specific worksheet name, ordered by ID.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_3_rows_with_specific_worksheet_ordered_by_id() {
		$args  = array(
			'orderby' => 'id',
			'order'   => 'ASC',
			'limit'   => 3,
			'where'   => array(
				array(
					'key'   => 'worksheet_name',
					'value' => 'Sheet 1',
				),
			),
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
			->method( 'getrow' )
			->willReturnCallback(
				function () {
					$filtered = array_filter(
						$this->mock_data,
						function ( $row ) {
							return 'Sheet 1' === $row['worksheet_name'];
						}
					);
					usort(
						$filtered,
						function ( $a, $b ) {
							return $a['id'] <=> $b['id'];
						}
					);
					return $filtered;
				}
			);

		$rows = $sheet->getrow();
		$this->assertCount( 2, $rows );

		// Convert an associative array into an indexed array forcibly.
		$rows = array_values( $rows );

		$this->assertEquals( 1, $rows[0]['id'] );
		$this->assertEquals( 2, $rows[1]['id'] );
	}

	/**
	 * Test getting rows with a specific sheet name.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_rows_with_specific_sheet_name() {
		$args  = array(
			'where' => array(
				array(
					'key'   => 'sheet_name',
					'value' => 'Data 3',
				),
			),
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
				->method( 'getrow' )
				->willReturn(
					array_filter(
						$this->mock_data,
						function ( $row ) {
							return 'Data 3' === $row['sheet_name'];
						}
					)
				);

		$rows = $sheet->getrow();
		$this->assertCount( 1, $rows );
		$first = reset( $rows );
		$this->assertEquals( 'Data 3', $first['sheet_name'] );
	}

	/**
	 * Test getting rows with a specific title.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_rows_with_specific_title() {
		$args  = array(
			'where' => array(
				array(
					'key'   => 'title',
					'value' => 'Title 4',
				),
			),
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
				->method( 'getrow' )
				->willReturn(
					array_filter(
						$this->mock_data,
						function ( $row ) {
							return 'Title 4' === $row['title'];
						}
					)
				);

		$rows = $sheet->getrow();
		$this->assertCount( 1, $rows );
		$first = reset( $rows );
		$this->assertEquals( 'Title 4', $first['title'] );
	}

	/**
	 * Test getting the 2nd row with a date greater than or equal to '2023-06-03 12:00:00' and a specific worksheet name, ordered by ID in descending order.
	 *
	 * @covers Google_Spreadsheet_To_DB_Query::getrow
	 */
	public function test_get_2nd_row_with_date_gte_and_specific_worksheet_ordered_by_id_desc() {
		$args  = array(
			'orderby' => 'id',
			'order'   => 'DESC',
			'limit'   => 1,
			'offset'  => 1,
			'where'   => array(
				array(
					'key'     => 'date',
					'value'   => '2023-06-03 12:00:00',
					'compare' => '>=',
				),
				array(
					'key'   => 'worksheet_name',
					'value' => 'Sheet 2',
				),
			),
		);
		$sheet = $this->getMockBuilder( Google_Spreadsheet_To_DB_Query::class )
					->setConstructorArgs( array( $args ) )
					->setMethods( array( 'getrow' ) )
					->getMock();

		$sheet->expects( $this->once() )
			->method( 'getrow' )
			->willReturn(
				array_slice(
					array_filter(
						$this->mock_data,
						function ( $row ) {
							return $row['date'] >= '2023-06-03 12:00:00' && 'Sheet 2' === $row['worksheet_name'];
						}
					),
					1,
					1
				)
			);

		$rows = $sheet->getrow();
		$this->assertCount( 1, $rows );
		$first = reset( $rows );
		$this->assertEquals( 3, $first['id'] );
	}
}
