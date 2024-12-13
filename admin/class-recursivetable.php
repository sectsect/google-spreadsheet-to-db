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

declare(strict_types=1);

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
							<label for="google_ss2db_json_path"><?php echo esc_html__( 'The absolute path to client_secret.json', 'google-spreadsheet-to-db' ); ?></label>
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
							<?php
							// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
							echo '<img src="' . esc_url( plugin_dir_url( __DIR__ ) . 'assets/images/github.svg' ) . '" width="22" height="auto" alt="GitHub" loading="lazy">';
							?>
						</dt>
						<dd>Document on GitHub</dd>
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
	global $wpdb;
	$table = GOOGLE_SS2DB_TABLE_NAME;

	// Get sort parameters.
	$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	// Default sort settings.
	$default_orderby = 'date';
	$default_order   = 'DESC';

	// Allowed sort columns.
	$allowed_orderby = array(
		'id'             => 'id',
		'worksheet_id'   => 'worksheet_id',
		'worksheet_name' => 'worksheet_name',
		'sheet_name'     => 'sheet_name',
		'title'          => 'title',
		'date'           => 'date',
	);

	// Sort column validation.
	$orderby = isset( $allowed_orderby[ $orderby ] ) ? $orderby : $default_orderby;
	$order   = in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : $default_order;

	$paged = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
	$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	// Verify pagination nonce.
	if ( $paged && ! wp_verify_nonce( $nonce, 'google_ss2db_pagination' ) ) {
		$paged = 1;
	}

	$paged  = $paged ? $paged : 1;
	$limit  = 24;
	$offset = ( $paged - 1 ) * $limit;

	// SQL with sorting.
	$countsql      = "SELECT * FROM {$table} ORDER BY {$orderby} {$order}";
	$allrows       = count( $wpdb->get_results( $countsql ) ); // phpcs:ignore
	$max_num_pages = ceil( $allrows / $limit );

	$sql      = "SELECT * FROM {$table} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
	$prepared = $wpdb->prepare(
		$sql, // phpcs:ignore
		$limit,
		$offset
	);

	$myrows = $wpdb->get_results( $prepared ); // phpcs:ignore
	$count  = count( $myrows );

	/**
	 * Generate sort URLs for table columns.
	 *
	 * @param string $column The column to generate sort URL for.
	 * @return string The generated sort URL.
	 */
	function get_sort_url( string $column ): string {
		$current_page    = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$current_orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$current_orderby = $current_orderby ? $current_orderby : 'date';
		$current_order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$current_order   = $current_order ? $current_order : 'DESC';

		// Toggle sort order.
		$new_order = 'DESC' === $current_order && $current_orderby === $column ? 'ASC' : 'DESC';

		$url_params = array(
			'page'    => $current_page,
			'orderby' => $column,
			'order'   => $new_order,
		);

		return esc_url( add_query_arg( $url_params ) );
	}

	if ( 0 < $count ) :
		?>
	<section id="list">
		<hr />
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'id' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'id' ) ); ?>">
							<span>ID</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'worksheet_id' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'worksheet_id' ) ); ?>">
							<span>Worksheet ID</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'worksheet_name' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'worksheet_name' ) ); ?>">
							<span>Worksheet Name</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'sheet_name' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'sheet_name' ) ); ?>">
							<span>Sheet Name</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'title' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'title' ) ); ?>">
							<span>Title</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col" class="manage-column sortable <?php echo esc_attr( 'date' === $orderby ? 'sorted ' . strtolower( $order ) : '' ); ?>">
						<a href="<?php echo esc_url( get_sort_url( 'date' ) ); ?>">
							<span>Date</span>
							<span class="sorting-indicator"></span>
						</a>
					</th>
					<th scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $myrows as $row ) : ?>
				<tr data-id="<?php echo esc_attr( $row->id ); ?>">
					<td><?php echo esc_html( $row->id ); ?></td>
					<td><?php echo esc_html( google_ss2db_truncate_middle( $row->worksheet_id ?? '(no ID)' ) ); ?></td>
					<td><?php echo esc_html( $row->worksheet_name ); ?></td>
					<td><?php echo esc_html( $row->sheet_name ); ?></td>
					<td style="color: <?php echo $row->title ? 'inherit' : '#aaa'; ?>">
						<?php echo esc_html( $row->title ? $row->title : '(no title)' ); ?>
					</td>
					<td>
						<?php
						$date        = new DateTime( $row->date );
						$date_format = is_string( get_option( 'date_format' ) ) ? get_option( 'date_format' ) : 'Y-m-d';
						$time_format = is_string( get_option( 'time_format' ) ) ? get_option( 'time_format' ) : 'H:i:s';
						echo esc_html( date_i18n( $date_format . ' ' . $time_format, $date->getTimestamp() ) );
						?>
					</td>
					<td>
						<button class="button view-details" data-id="<?php echo esc_attr( $row->id ); ?>">
							<?php echo esc_html__( 'Details', 'google-spreadsheet-to-db' ); ?>
						</button>
						<button class="button view-raw-data" data-id="<?php echo esc_attr( $row->id ); ?>">
							<?php echo esc_html__( 'Raw Data', 'google-spreadsheet-to-db' ); ?>
						</button>
						<button class="button delete-entry" data-id="<?php echo esc_attr( $row->id ); ?>">
							<?php echo esc_html__( 'Delete', 'google-spreadsheet-to-db' ); ?>
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		$pagination_nonce = esc_attr( wp_create_nonce( 'google_ss2db_pagination' ) );
		if ( function_exists( 'google_ss2db_options_pagination' ) ) {
			google_ss2db_options_pagination(
				$paged,
				(int) $max_num_pages,
				2,
				$pagination_nonce
			);
		}
		?>
	</section>
	<?php endif; ?>
</div>

<script>
jQuery(function($) {
	$('.view-details').on('click', function() {
		const id = $(this).data('id');
		const row = $(this).closest('tr');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'get_spreadsheet_entry_details',
				id: id,
				nonce: '<?php echo esc_js( wp_create_nonce( 'get_spreadsheet_entry_details' ) ); ?>'
			},
			success: function(response) {
				if (response.success) {
					Swal.fire({
						title: `Entry Details (ID: ${id})`,
						html: `<pre>${JSON.stringify(JSON.parse(response.data), null, 2)}</pre>`,
						width: '80%',
						padding: '1rem'
					});
				}
			}
		});
	});

	$('.delete-entry').on('click', function() {
		const id = $(this).data('id');
		const row = $(this).closest('tr');

		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Delete'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'delete_spreadsheet_entry',
						id: id,
						nonce: '<?php echo esc_js( wp_create_nonce( 'delete_spreadsheet_entry' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							row.remove();
							Swal.fire('Deleted', 'The entry has been deleted', 'success');
						} else {
							Swal.fire('Error', response.data, 'error');
						}
					}
				});
			}
		});
	});
});
</script>

<?php if ( ! defined( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH' ) || ! get_option( 'google_ss2db_dataformat' ) ) : ?>
<script>
jQuery(function() {
	jQuery('#save-spreadsheet').prop('disabled', true);
});
</script>
<?php endif; ?>
