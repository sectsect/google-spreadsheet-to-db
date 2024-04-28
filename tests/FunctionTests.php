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
			array( 'HelloWorld', 5, 'He...rld' ), // Basic case.
			array( 'HelloWorld', 10, 'Hello...World' ), // Max chars equal to string length.
			array( 'HelloWorld', 0, '...' ), // Max chars is zero.
			array( '', 5, '...' ), // Empty string.
			array( 'HelloWorld', 1, '...d' ), // Max chars is one.
		);
	}

	/**
	 * Tests the output of the google_ss2db_options_pagination function.
	 */
	public function testPaginationOutput() {
		// Pagination settings.
		$current_page = 1;
		$total_pages  = 5;
		$range        = 2;

		// Expected output.
		$expected_output  = '<ul class="pagination">';
		$expected_output .= '<li class="prevnext"><a href="' . get_pagenum_link( $current_page + 1 ) . '">&rsaquo;</a></li>';
		$expected_output .= '<li class="last"><a href="' . get_pagenum_link( $total_pages ) . '">&raquo;</a></li>';
		$expected_output .= '</ul>';

		// Capture the output.
		$this->expectOutputString( $expected_output );

		// Execute the pagination function.
		google_ss2db_options_pagination( $current_page, $total_pages, $range );
	}
}
