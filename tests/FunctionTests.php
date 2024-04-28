<?php
/**
 * Class FunctionsTest
 * Tests the functionality of functions in functions.php.
 */
class FunctionsTest extends WP_UnitTestCase {
	/**
	 * @dataProvider providerTruncateMiddle
	 */
	public function test_google_ss2db_truncate_middle( $input, $max_chars, $expected ) {
		require_once 'functions/functions.php'; // Include the file where the function is defined.
		$result = google_ss2db_truncate_middle( $input, $max_chars );
		$this->assertEquals( $expected, $result );
	}

	public function providerTruncateMiddle() {
		return array(
			array( 'HelloWorld', 5, 'He...d' ), // Basic case.
			array( 'HelloWorld', 10, 'HelloWorld' ), // Max chars equal to string length.
			array( 'HelloWorld', 0, '...' ), // Max chars is zero.
			array( '', 5, '' ), // Empty string.
			array( 'HelloWorld', 1, '...' ), // Max chars is one.
		);
	}

	/**
	 * @dataProvider providerOptionsPagination
	 */
	public function test_google_ss2db_options_pagination( $options, $current_page, $expected ) {
		require_once 'functions/functions.php'; // Include the file where the function is defined.
		$result = google_ss2db_options_pagination( $options, $current_page );
		$this->assertEquals( $expected, $result );
	}

	public function providerOptionsPagination() {
		return array(
			array(
				array(
					'items'        => 100,
					'itemsPerPage' => 10,
				),
				1,
				array(
					'start' => 0,
					'end'   => 10,
				),
			), // First page.
			array(
				array(
					'items'        => 100,
					'itemsPerPage' => 10,
				),
				5,
				array(
					'start' => 40,
					'end'   => 50,
				),
			), // Middle page.
			array(
				array(
					'items'        => 100,
					'itemsPerPage' => 10,
				),
				10,
				array(
					'start' => 90,
					'end'   => 100,
				),
			), // Last page.
			array(
				array(
					'items'        => 100,
					'itemsPerPage' => 10,
				),
				0,
				array(
					'start' => 0,
					'end'   => 10,
				),
			), // Page number too low.
			array(
				array(
					'items'        => 100,
					'itemsPerPage' => 10,
				),
				11,
				array(
					'start' => 100,
					'end'   => 100,
				),
			), // Page number too high.
			array(
				array(
					'items'        => 50,
					'itemsPerPage' => 25,
				),
				2,
				array(
					'start' => 25,
					'end'   => 50,
				),
			), // Exact fit last page.
			array(
				array(
					'items'        => 0,
					'itemsPerPage' => 10,
				),
				1,
				array(
					'start' => 0,
					'end'   => 0,
				),
			), // No items.
		);
	}
}
