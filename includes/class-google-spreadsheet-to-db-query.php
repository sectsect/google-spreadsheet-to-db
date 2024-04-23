<?php
/**
 * Handles database queries for the Google Spreadsheet to DB plugin.
 *
 * This class is responsible for constructing and executing SQL queries based on specified parameters.
 * It supports filtering, sorting, and pagination of the results. The class is typically initialized
 * during the plugin's activation to set up necessary configurations.
 *
 * @since      1.0.2
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.2
 * @package    Google_Spreadsheet_to_DB
 * @subpackage Google_Spreadsheet_to_DB/includes
 */
class Google_Spreadsheet_To_DB_Query {

	/**
	 * Constructor for the Google_Spreadsheet_To_DB_Query class.
	 * Initializes the query object with specified or default parameters.
	 *
	 * @param array $args {
	 *     Optional. Array of query parameters to override defaults.
	 *
	 *     @type array  $where   Conditions for filtering the query.
	 *     @type string $orderby Column by which to order the results.
	 *     @type string $order   Direction to order the results (ASC or DESC).
	 *     @type int    $limit   Maximum number of results to retrieve.
	 *     @type int    $offset  Number of results to skip.
	 * }
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'where'   => array(),
			'orderby' => 'date',
			'order'   => 'DESC',
			'limit'   => false,
			'offset'  => false,
		);
		$d        = wp_parse_args( $args, $defaults );
		$this->setobject( $d );
	}

	/**
	 * Sets the internal data object based on provided query parameters.
	 * Converts the array to a JSON object for internal processing.
	 *
	 * @param array $d The array of query parameters.
	 */
	public function setobject( $d ) {
		$this->data = json_decode( json_encode( $d ) );
	}

	/**
	 * Retrieves rows from the database based on the query parameters set in the data object.
	 * Constructs a SQL query dynamically based on conditions such as where, order, and limit.
	 *
	 * @return array An array of stdClass objects representing each row returned by the query.
	 */
	public function getrow() {
		global $wpdb;
		$table = GOOGLE_SS2DB_TABLE_NAME;

		$allow_whererelation = array( 'AND', 'OR' );
		if ( isset( $this->data->where->relation ) && in_array( $this->data->where->relation, $allow_whererelation, true ) ) {
			$order = esc_sql( $this->data->where->relation );
		} else {
			$order = 'AND';
		}

		// Remove property 'relation' in object '$this->data->where'.
		unset( $this->data->where->relation );
		$wheres = $this->data->where;

		$wh = array();
		foreach ( $wheres as $where ) {
			$allow_wherekeys = array( 'id', 'date', 'worksheet_id', 'worksheet_name', 'sheet_name', 'title' );
			if ( isset( $where->key ) && in_array( $where->key, $allow_wherekeys, true ) ) {
				$wherekey = esc_sql( $where->key );
			} else {
				$wherekey = false;
			}

			if ( isset( $where->value ) ) {
				$whereval = esc_sql( (string) $where->value );
			} else {
				$whereval = false;
			}

			$operators = array( '=', '>', '<', '>=', '<=', '<>', '!=' );
			if ( isset( $where->compare ) && in_array( $where->compare, $operators, true ) ) {
				$wherecompare = esc_sql( (string) $where->compare );
			} else {
				$wherecompare = '=';
			}

			if ( $wherekey && $whereval && $wherecompare ) {
				$wh[] = $wpdb->prepare( $wherekey . ' ' . $wherecompare . ' %s', $whereval ); // phpcs:ignore
			}
		}
		if ( ! empty( $wh ) ) {
			$whstr = implode( ' ' . $order . ' ', $wh );
		}

		$allow_orderbys = array( 'id', 'date', 'worksheet_id', 'worksheet_name', 'sheet_name', 'title' );
		if ( isset( $this->data->orderby ) && in_array( $this->data->orderby, $allow_orderbys, true ) ) {
			$orderby = esc_sql( $this->data->orderby );
		} else {
			$orderby = 'date';
		}

		$allow_orders = array( 'DESC', 'ASC' );
		if ( isset( $this->data->order ) && in_array( $this->data->order, $allow_orders, true ) ) {
			$order = esc_sql( $this->data->order );
		} else {
			$order = 'DESC';
		}

		if ( $this->data->limit && intval( $this->data->limit ) !== -1 ) {
			$limit = intval( $this->data->limit );
		} else {
			$limit = 2147483647;
		}

		if ( $this->data->offset ) {
			$offset = intval( $this->data->offset );
		} else {
			$offset = 0;
		}

		if ( isset( $whstr ) ) {
			$sql      = 'SELECT * FROM ' . $table . ' WHERE ' . $whstr . '  ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';
			$prepared = $wpdb->prepare(
				$sql, // phpcs:ignore
				$limit,
				$offset
			);
		} else {
			$sql      = 'SELECT * FROM ' . $table . ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d OFFSET %d';
			$prepared = $wpdb->prepare(
				$sql, // phpcs:ignore
				$limit,
				$offset
			);
		}

		$myrows = $wpdb->get_results( $prepared ); // phpcs:ignore

		return $myrows;
	}
}
