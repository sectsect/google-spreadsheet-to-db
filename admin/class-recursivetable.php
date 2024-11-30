<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/sectsect/
 * @since      1.0.0
 *
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/admin
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/admin
 */
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Google Spreadsheet to DB', 'google-spreadsheet-to-db' ); ?></h1>
	<section>
		<form method="post" action="options.php">
			<hr />
			<h3><?php echo esc_html__( 'General Settings', 'google-spreadsheet-to-db' ); ?></h3>
			<?php
			settings_fields( 'google_ss2db-settings-group' );
			do_settings_sections( 'google_ss2db-settings-group' );
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="google_ss2db_json_path"><?php echo esc_html__( 'The absolute path to <code>client_secret.json</code>', 'google-spreadsheet-to-db' ); ?></label>
						</th>
						<td>
							<?php if ( defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) ) : ?>
							<code>
								<?php echo esc_html( GOOGLE_SS2DB_CLIENT_SECRET_PATH ); ?>
							</code>
							<?php else : ?>
							<p>
								<?php echo esc_html__( 'Warning: You must define constants for client_secret.json in the <code>wp-config.php</code> file.', 'google-spreadsheet-to-db' ); ?><br>
								e.g. <code>define('GOOGLE_SS2DB_CLIENT_SECRET_PATH', '/path/to/your/client_secret.json');</code>
							</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="google_ss2db_dataformat"><?php echo esc_html__( 'Data format', 'google-spreadsheet-to-db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
						</th>
						<td>
							<?php
							$types = array(
								'json'        => 'json_encode',
								'json-unescp' => 'json_encode (JSON_UNESCAPED_UNICODE)',
							);
							?>
							<select id="google_ss2db_dataformat" name="google_ss2db_dataformat" style="font-size: 11px; width: 330px;">
								<?php foreach ( $types as $key => $type ) : ?>
									<?php $selected = ( get_option( 'google_ss2db_dataformat' ) === $key ) ? 'selected' : ''; ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $type ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="link-doc">
				<a href="https://github.com/sectsect/google-spreadsheet-to-db" target="_blank">
					<dl>
						<dt>
							<img src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'assets/images/github.svg' ); ?>" width="22" height="auto" alt="GitHub" loading="lazy">
						</dt>
						<dd> Document on GitHub</dd>
					</dl>
				</a>
			</div>
			<?php submit_button(); ?>
		</form>
	</section>
	<section>
		<?php if ( isset( $_GET['ss2dbupdated'] ) && '1' === $_GET['ss2dbupdated'] ) : ?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><strong><?php echo esc_html__( 'Spreadsheet saved', 'google-spreadsheet-to-db' ); ?></strong></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>
			</div>
		<?php elseif ( isset( $_GET['ss2dbupdated'] ) && '0' === $_GET['ss2dbupdated'] ) : ?>
			<div id="setting-error-settings_updated" class="error settings-error notice notice-error is-dismissible">
				<p><strong><?php echo esc_html__( 'Saving the spreadsheet failed', 'google-spreadsheet-to-db' ); ?></strong></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'includes/save.php' ); ?>">
			<hr />
			<h3><?php echo esc_html__( 'Import from Google Spreadsheet', 'google-spreadsheet-to-db' ); ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="worksheetid"><?php echo esc_html__( 'Spreadsheet ID', 'google-spreadsheet-to-db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
						</th>
						<td>
							<input type="text" id="worksheetid" class="regular-text" name="worksheetid" style="width: 400px;" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="worksheetname"><?php echo esc_html__( 'Spreadsheet name', 'google-spreadsheet-to-db' ); ?> <span style="color: #999; font-size: 10px; font-weight: normal;">(Optional)</span></label>
						</th>
						<td>
							<input type="text" id="worksheetname" class="regular-text" name="worksheetname" style="width: 180px;">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sheetname"><?php echo esc_html__( 'Single Sheet name', 'google-spreadsheet-to-db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
						</th>
						<td>
							<input type="text" id="sheetname" class="regular-text" name="sheetname" style="width: 180px;" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="hasheaderrow"><?php echo esc_html__( 'Top Header Row', 'google-spreadsheet-to-db' ); ?></label>
						</th>
						<td>
							<input type="checkbox" id="hasheaderrow" name="hasheaderrow" value="1" checked>
							<span style="font-size: 11px; color: #888"><?php echo esc_html__( 'Check this if the sheet has a top header row.', 'google-spreadsheet-to-db' ); ?> </span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="datatitle"><?php echo esc_html__( 'Title', 'google-spreadsheet-to-db' ); ?> <span style="color: #999; font-size: 10px; font-weight: normal;">(Optional)</span></label>
						</th>
						<td>
							<input type="text" id="datatitle" class="regular-text" name="datatitle" style="width: 330px;">
						</td>
					</tr>
				</tbody>
			</table>
			<p><?php echo esc_html__( 'This process may takes a few minutes.', 'google-spreadsheet-to-db' ); ?></p>
			<?php wp_nonce_field( 'google_ss2db', 'nonce' ); ?>
			<?php
			$text = esc_html__( 'Import from Google Spreadsheet', 'google-spreadsheet-to-db' );
			submit_button( $text, 'primary', 'save-spreadsheet', false );
			?>
		</form>
	</section>
	<?php
	/**
	 * Generate Recursive Table.
	 *
	 * @since      1.0.3
	 * @package    Google_Spreadsheet_to_DB
	 * @subpackage Google_Spreadsheet_to_DB/admin
	 */
	class RecursiveTable {
		/**
		 * Convert JSON text to HTML debug output.
		 *
		 * @param string $json_text JSON text to be converted.
		 * @return string HTML representation of the JSON.
		 */
		public static function json_to_debug( string $json_text = '' ): string {
			$arr  = json_decode( $json_text, true );
			$html = '';
			if ( $arr && is_array( $arr ) ) {
				$html .= self::array_to_html_table_recursive( $arr );
			}
			return $html;
		}

		/**
		 * Recursively convert an array to an HTML table.
		 *
		 * @param array<mixed> $arr Array to be converted.
		 * @return string HTML table representation of the array.
		 */
		private static function array_to_html_table_recursive( array $arr ): string {
			$str = '<table><tbody>';
			foreach ( $arr as $key => $val ) {
				$str .= '<tr>';
				$str .= '<th><span>' . htmlspecialchars( $key ) . '</span></th>';
				$str .= '<td>';
				if ( is_array( $val ) ) {
					if ( ! empty( $val ) ) {
						$str .= self::array_to_html_table_recursive( $val );
					}
				} else {
					if ( ! is_string( $val ) ) {
						return $str;
					}
					$value = $val;
					$str  .= '<span>' . nl2br( htmlspecialchars( $value ) ) . '</span>';
				}
				$str .= '</td></tr>';
			}
			$str .= '</tbody></table>';

			return $str;
		}
	}

	global $wpdb;
	$table = GOOGLE_SS2DB_TABLE_NAME;

	$paged = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
	$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	$paged         = $paged && wp_verify_nonce( $nonce, 'google_ss2db_pagination' )
		? $paged
		: 1;
	$limit         = 24;
	$offset        = ( $paged - 1 ) * $limit;
	$countsql      = 'SELECT * FROM ' . GOOGLE_SS2DB_TABLE_NAME . ' ORDER BY date DESC';
	$allrows = count( $wpdb->get_results( $countsql ) ); // phpcs:ignore
	$max_num_pages = ceil( $allrows / $limit );
	// $sql           = 'SELECT * FROM ' . GOOGLE_SS2DB_TABLE_NAME . ' ORDER BY date DESC LIMIT ' . $offset . ', ' . $limit;
	$sql      = 'SELECT * FROM ' . $table . ' ORDER BY date DESC LIMIT %d OFFSET %d';
	$prepared = $wpdb->prepare(
		$sql, // phpcs:ignore
		$limit,
		$offset
	);

	$myrows = $wpdb->get_results( $prepared ); // phpcs:ignore
	$count  = count( $myrows );
	if ( 0 < $count ) :
		?>
	<section id="list">
		<hr />
		<?php foreach ( $myrows as $row ) : ?>
		<dl class="acorddion" data-id="<?php echo esc_attr( $row->id ); ?>">
			<dt>
				<span class="ss2db_logo"></span>
				<span class="ss2db_id">
					<?php echo esc_html( $row->id ); ?>
				</span>
				<span class="ss2db_worksheet_id">
					<?php
					$wordsheet_id = ( isset( $row->worksheet_id ) ) ? $row->worksheet_id : '(no ID)';
					echo esc_html( google_ss2db_truncate_middle( $wordsheet_id ) );
					?>
				</span>
				<span class="ss2db_worksheet_name">
					<?php echo esc_html( $row->worksheet_name ); ?>
				</span>
				<span class="ss2db_sheet_name">
					<?php echo esc_html( $row->sheet_name ); ?>
				</span>
				<span class="ss2db_title
				<?php
				if ( ! $row->title ) {
					echo ' no_value';
				}
				?>
				">
					<?php echo esc_html( $row->title ? $row->title : ' (no title)' ); ?>
				</span>
				<span class="ss2db_date">
					<div class="inner">
						<?php
						$date = new DateTime( $row->date );
						echo esc_html( $date->format( 'Y.m.d H:i:s' ) );
						?>
					</div>
				</span>
				<span class="ss2db_delete"></span>
			</dt>
			<dd>
				<?php
				$json = $row->value;
				echo wp_kses_post( RecursiveTable::json_to_debug( $json ) );
				?>
			</dd>
		</dl>
		<?php endforeach; ?>
		<?php
		if ( function_exists( 'google_ss2db_options_pagination' ) ) {
			google_ss2db_options_pagination( $paged, (int) $max_num_pages, 2 );
		}
		?>
	</section>
	<?php endif; ?>
</div>

<?php if ( ! defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) || ! get_option( 'google_ss2db_dataformat' ) ) : ?>
<script>
jQuery(function() {
	jQuery('#save-spreadsheet').prop('disabled', true);
});
</script>
<?php endif; ?>
