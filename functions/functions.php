<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://github.com/sectsect/
 * @since      1.0.0
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */

/**
 * Register functions for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/functions
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . 'composer/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'composer/vendor/autoload.php';
}

/**
 * Displays pagination links based on the provided parameters.
 *
 * This function generates HTML for a simple pagination interface, based on the current page,
 * total number of pages, and the range of pages to display around the current page.
 *
 * @param int $paged Current page number, defaults to 1.
 * @param int $pages Total number of pages, defaults to 1.
 * @param int $range Number of pages to display around the current page, defaults to 2.
 */
function google_ss2db_options_pagination( int $paged = 1, int $pages = 1, int $range = 2 ): void {
	$paged     = intval( $paged );
	$pages     = intval( $pages );
	$range     = intval( $range );
	$showitems = ( $range * 2 ) + 1;

	if ( 1 !== $pages ) {
		echo '<ul class="pagination">';
		if ( 2 < $paged && $paged > $range + 1 && $showitems < $pages ) {
			echo '<li class="first"><a href="' . get_pagenum_link( 1 ) . '">&laquo;</a></li>';
		}
		if ( 1 < $paged && $showitems < $pages ) {
			echo '<li class="prevnext"><a href="' . get_pagenum_link( $paged - 1 ) . '">&lsaquo;</a></li>';
		}
		for ( $i = 1; $i <= $pages; $i++ ) {
			if ( 1 !== $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				echo ( $paged === $i ) ? '<li class="current"><span>' . $i . '</span></li>' : '<li><a href="' . get_pagenum_link( $i ) . '">' . $i . '</a></li>';
			}
		}
		if ( $paged < $pages && $showitems < $pages ) {
			echo '<li class="prevnext"><a href="' . get_pagenum_link( $paged + 1 ) . '">&rsaquo;</a></li>';
		}
		if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
			echo '<li class="last"><a href="' . get_pagenum_link( $pages ) . '">&raquo;</a></li>';
		}
		echo '</ul>';
	}
}

/**
 * Truncates a string by removing characters from the middle and replacing them with an ellipsis.
 *
 * This function is useful for creating previews or shortening strings without losing the beginning
 * and end of the string. It ensures the string does not exceed the maximum specified length.
 *
 * @param string $str The string to truncate.
 * @param int    $max_chars Maximum number of characters to retain, defaults to 16.
 * @return string The truncated string.
 */
function google_ss2db_truncate_middle( string $str, int $max_chars = 16 ): string {
	$str_length = strlen( $str );

	return substr_replace( $str, '...', $max_chars / 2, $str_length - $max_chars );
}
