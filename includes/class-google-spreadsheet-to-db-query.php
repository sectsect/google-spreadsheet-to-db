<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.ilovesect.com/
 * @since      1.0.2
 *
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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since  1.0.2
	 * @param  array $args "description".
	 * @return void "description".
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
	 * Push the object.
	 *
	 * @since  1.0.2
	 * @param  array $d "description".
	 * @return void "description".
	 */
	public function setobject( $d ) {
		$this->data = json_decode( json_encode( $d ) );
	}

	/**
	 * Get the rows.
	 *
	 * @return array "description".
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
			$whstr = implode( $wh, ' ' . $order . ' ' );
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
