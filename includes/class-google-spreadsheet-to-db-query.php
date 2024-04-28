<?php
	/**
	 * This file contains the Google_Spreadsheet_To_DB_Query class which handles SQL queries for the Google Spreadsheet to DB plugin.
	 *
	 * The class is designed to construct and execute SQL queries with support for filtering, sorting, and pagination.
	 * It is initialized with customizable parameters to tailor the query operations.
	 *
	 * @package    Google_Spreadsheet_to_DB
	 * @subpackage Google_Spreadsheet_to_DB/includes
	 * @since      1.0.2
	 */

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
class Google_Spreadsheet_To_DB_Query {
	/**
	 * Holds the parameters and defaults for the query.
	 *
	 * @var stdClass
	 */
	private $data;

	/**
	 * Constructor for the Google_Spreadsheet_To_DB_Query class.
	 * Initializes the class with default or custom parameters.
	 *
	 * @param array<string, mixed> $args Customizable parameters including 'where', 'orderby', 'order', 'limit', and 'offset'.
	 */
	public function __construct( array $args = array() ) {
		$defaults   = array(
			'where'   => array(),
			'orderby' => 'date',
			'order'   => 'DESC',
			'limit'   => PHP_INT_MAX,
			'offset'  => 0,
		);
		$this->data = (object) wp_parse_args( $args, $defaults );
	}

	/**
	 * Builds the WHERE SQL clause based on the conditions specified in the 'where' parameter.
	 * Supports basic comparison operators and handles multiple conditions with AND/OR relations.
	 *
	 * @return string The WHERE clause of the SQL query.
	 */
	private function build_where_clause(): string {
		global $wpdb;
		$wheres   = array();
		$relation = isset( $this->data->where['relation'] ) && in_array( $this->data->where['relation'], array( 'AND', 'OR' ), true ) ? $this->data->where['relation'] : 'AND';
		unset( $this->data->where['relation'] );

		foreach ( $this->data->where as $where ) {
			if ( in_array( $where['key'], array( 'id', 'date', 'worksheet_id', 'worksheet_name', 'sheet_name', 'title' ), true ) ) {
				$compare  = isset( $where['compare'] ) && in_array( $where['compare'], array( '=', '>', '<', '>=', '<=', '<>', '!=' ), true ) ? $where['compare'] : '=';
				$wheres[] = $wpdb->prepare( "{$where['key']} $compare %s", $where['value'] ); // phpcs:ignore
			}
		}

		return $wheres ? implode( " $relation ", $wheres ) : '';
	}

	/**
	 * Builds the ORDER BY SQL clause based on the 'orderby' and 'order' parameters.
	 * Ensures that the ordering is by a valid column and in a valid direction (ASC/DESC).
	 *
	 * @return string The ORDER BY clause of the SQL query.
	 */
	private function build_order_by_clause(): string {
		$orderby = in_array( $this->data->orderby, array( 'id', 'date', 'worksheet_id', 'worksheet_name', 'sheet_name', 'title' ), true ) ? $this->data->orderby : 'date';
		$order   = in_array( $this->data->order, array( 'ASC', 'DESC' ), true ) ? $this->data->order : 'DESC';
		return "ORDER BY $orderby $order";
	}

	/**
	 * Retrieves rows from the database based on the constructed SQL query.
	 * Applies filtering, sorting, and pagination as specified in the class parameters.
	 *
	 * @return array<object>|null Database query results.
	 */
	public function getrow(): ?array {
		global $wpdb;
		$table           = GOOGLE_SS2DB_TABLE_NAME;
		$where_clause    = $this->build_where_clause();
		$order_by_clause = $this->build_order_by_clause();
		$limit           = is_numeric( $this->data->limit ) ? intval( $this->data->limit ) : PHP_INT_MAX;
		$limit           = ( -1 === $limit ) ? PHP_INT_MAX : $limit;
		$offset          = is_numeric( $this->data->offset ) ? intval( $this->data->offset ) : 0;

		$sql  = "SELECT * FROM $table";
		$sql .= $where_clause ? " WHERE $where_clause" : '';
		$sql .= " $order_by_clause LIMIT %d OFFSET %d";

		$prepared = $wpdb->prepare(
			$sql, // phpcs:ignore
			$limit,
			$offset
		);
		return $wpdb->get_results( $prepared ); // phpcs:ignore
	}
}
