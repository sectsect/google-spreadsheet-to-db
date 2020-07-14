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
	<h1>Google Spreadsheet to DB</h1>
	<section>
		<form method="post" action="options.php">
			<hr />
			<h3><?php _e( 'General Settings', 'google_ss2db' ); ?></h3>
			<?php
			settings_fields( 'google_ss2db-settings-group' );
			do_settings_sections( 'google_ss2db-settings-group' );
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="google_ss2db_json_path"><?php _e( 'The absolute path to <code>client_secret.json</code>', 'google_ss2db' ); ?></label>
						</th>
						<td>
							<?php if ( defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) ) : ?>
							<code>
								<?php echo esc_html( GOOGLE_SS2DB_CLIENT_SECRET_PATH ); ?>
							</code>
							<?php else : ?>
							<p>
								<?php _e( 'Warning: You must define constants for client_secret.json in the <code>wp-config.php</code> file.', 'google_ss2db' ); ?><br>
								e.g. <code>define('GOOGLE_SS2DB_CLIENT_SECRET_PATH', '/path/to/your/client_secret.json');</code>
							</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="google_ss2db_dataformat"><?php _e( 'Data format', 'google_ss2db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
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
									<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $type; ?></option>
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
							<img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/github.svg" width="22" height="auto">
						</dt>
						<dd> Document on GitHub</dd>
					</dl>
				</a>
			</div>
			<?php submit_button(); ?>
		</form>
	</section>
	<section>
		<?php if ( isset( $_GET['ss2dbupdated'] ) && true === $_GET['ss2dbupdated'] ) : ?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><strong><?php _e( 'Spreadsheet saved', 'google_ss2db' ); ?></strong></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>
			</div>
		<?php elseif ( isset( $_GET['ss2dbupdated'] ) && false === $_GET['ss2dbupdated'] ) : ?>
			<div id="setting-error-settings_updated" class="error settings-error notice notice-error is-dismissible">
				<p><strong><?php _e( 'Saving the spreadsheet failed', 'google_ss2db' ); ?></strong></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'includes/save.php'; ?>">
			<hr />
			<h3><?php _e( 'Import from Google Spreadsheet', 'google_ss2db' ); ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="worksheetname"><?php _e( 'Spreadsheet name', 'google_ss2db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
						</th>
						<td>
							<input type="text" id="worksheetname" class="regular-text" name="worksheetname" style="width: 180px;" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sheetname"><?php _e( 'Single Sheet name', 'google_ss2db' ); ?> <span style="color: #c00; font-size: 10px; font-weight: normal;">(Required)</span></label>
						</th>
						<td>
							<input type="text" id="sheetname" class="regular-text" name="sheetname" style="width: 180px;" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="datatitle"><?php _e( 'Title', 'google_ss2db' ); ?> <span style="color: #999; font-size: 10px; font-weight: normal;">(Optional)</span></label>
						</th>
						<td>
							<input type="text" id="datatitle" class="regular-text" name="datatitle" style="width: 330px;">
						</td>
					</tr>
				</tbody>
			</table>
			<p><?php _e( 'This process may takes a few minutes.', 'google_ss2db' ); ?></p>
			<?php wp_nonce_field( 'google_ss2db', 'nonce' ); ?>
			<?php
			$text = __( 'Import from Google Spreadsheet', 'google_ss2db' );
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
		 * Set the description.
		 *
		 * @param  array $json_text "jsonText".
		 * @return string "description".
		 */
		public static function json_to_debug( $json_text = '' ) {
			$arr  = json_decode( $json_text, true );
			$html = '';
			if ( $arr && is_array( $arr ) ) {
				$html .= self::array_to_html_table_recursive( $arr );
			}
			return $html;
		}

		/**
		 * Set the description.
		 *
		 * @param  array $arr "array".
		 * @return string "description".
		 */
		private static function array_to_html_table_recursive( $arr ) {
			$str = '<table><tbody>';
			foreach ( $arr as $key => $val ) {
				$str .= '<tr>';
				$str .= "<th><span>$key</span></th>";
				$str .= '<td>';
				if ( is_array( $val ) ) {
					if ( ! empty( $val ) ) {
						$str .= self::array_to_html_table_recursive( $val );
					}
				} else {
					$str .= "<span>$val</span>";
				}
				$str .= '</td></tr>';
			}
			$str .= '</tbody></table>';

			return $str;
		}
	}

	global $wpdb;
	$table         = GOOGLE_SS2DB_TABLE_NAME;
	$paged         = isset( $_GET['paged'] ) ? ( (int) $_GET['paged'] ) : 1;
	$limit         = 24;
	$offset        = ( $paged - 1 ) * $limit;
	$countsql      = 'SELECT * FROM ' . GOOGLE_SS2DB_TABLE_NAME . ' ORDER BY date DESC';
	$allrows       = count( $wpdb->get_results( $countsql ) ); // phpcs:ignore
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
		<dl class="acorddion" data-id="<?php echo $row->id; ?>">
			<dt>
				<span class="ss2db_logo"></span>
				<span class="ss2db_id">
					<?php echo $row->id; ?>
				</span>
				<span class="ss2db_worksheet_name">
					<?php echo $row->worksheet_name; ?>
				</span>
				<span class="ss2db_sheet_name">
					<?php echo $row->sheet_name; ?>
				</span>
				<span class="ss2db_title">
					<?php
					if ( $row->title ) {
						echo $row->title;
					} else {
						echo '(no title)';
					}
					?>
				</span>
				<span class="ss2db_date">
					<div class="inner">
						<?php
						$date = new DateTime( $row->date );
						echo $date->format( 'Y.m.d H:i:s' );
						?>
					</div>
				</span>
				<span class="ss2db_delete"></span>
			</dt>
			<dd>
				<?php
				$json = $row->value;
				echo RecursiveTable::json_to_debug( $json );
				?>
			</dd>
		</dl>
		<?php endforeach; ?>
		<?php
		if ( function_exists( 'google_ss2db_options_pagination' ) ) {
			google_ss2db_options_pagination( $paged, $max_num_pages, 2 );
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
