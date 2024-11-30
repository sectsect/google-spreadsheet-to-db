<?php
/**
 * FunctionsTest class
 *
 * Tests the functionality of functions in functions.php.
 *
 * @package YourPackageName
 * @subpackage Tests
 */

/**
 * Class FunctionsTest
 * Tests the functionality of functions in functions.php.
 *
 * @coversDefaultClass Functions
 */
class FunctionsTest extends WP_UnitTestCase {
	/**
	 * Test the google_ss2db_truncate_middle function.
	 *
	 * @dataProvider providerTruncateMiddle
	 * @covers ::google_ss2db_truncate_middle
	 *
	 * @param string $input      The input string to truncate.
	 * @param int    $max_chars  The maximum number of characters to keep.
	 * @param string $expected   The expected truncated string.
	 */
	public function test_google_ss2db_truncate_middle( $input, $max_chars, $expected ) {
		require_once 'functions/functions.php'; // Include the file where the function is defined.
		$result = google_ss2db_truncate_middle( $input, $max_chars );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Data provider for test_google_ss2db_truncate_middle.
	 *
	 * @return array Test data.
	 */
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
	 *
	 * @covers ::google_ss2db_options_pagination
	 */
	public function testPaginationOutput() {
		// Pagination settings.
		$current_page = 1;
		$total_pages  = 5;
		$range        = 2;
		$nonce        = wp_create_nonce( 'google_ss2db_pagination' );

		// Expected output.
		$expected_output  = '<ul class="pagination">';
		$expected_output .= '<li class="current"><span>1</span></li>';
		$expected_output .= '<li><a href="' . esc_url(
			add_query_arg(
				array(
					'paged' => 2,
					'nonce' => $nonce,
				)
			)
		) . '">2</a></li>';
		$expected_output .= '<li><a href="' . esc_url(
			add_query_arg(
				array(
					'paged' => 3,
					'nonce' => $nonce,
				)
			)
		) . '">3</a></li>';
		$expected_output .= '<li><a href="' . esc_url(
			add_query_arg(
				array(
					'paged' => 4,
					'nonce' => $nonce,
				)
			)
		) . '">4</a></li>';
		$expected_output .= '<li><a href="' . esc_url(
			add_query_arg(
				array(
					'paged' => 5,
					'nonce' => $nonce,
				)
			)
		) . '">5</a></li>';
		$expected_output .= '</ul>';

		// Capture the output.
		$this->expectOutputString( $expected_output );

		// Execute the pagination function.
		google_ss2db_options_pagination( $current_page, $total_pages, $range, $nonce );
	}
}
