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
}
