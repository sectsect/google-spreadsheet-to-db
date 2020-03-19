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
 * Push the object.
 *
 * @since  1.0.5
 * @param  array $paged "Paged".
 * @param  array $pages "Total page numbers".
 * @param  array $range "Range for Pagination".
 * @return void "description".
 */
function google_ss2db_options_pagination( $paged = 1, $pages = 1, $range = 2 ) {
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
