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
		$d = wp_parse_args( $args, $defaults );
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

		if ( isset( $this->data->where->key ) ) {
			$wherekey = $this->data->where->key;
		} else {
			$wherekey = false;
		}

		if ( isset( $this->data->where->value ) ) {
			$whereval = $this->data->where->value;
		} else {
			$whereval = false;
		}

		if ( isset( $this->data->orderby ) ) {
			$orderby = $this->data->orderby;
		} else {
			$orderby = 'date';
		}

		if ( isset( $this->data->order ) ) {
			$order = $this->data->order;
		} else {
			$order = 'DESC';
		}

		if ( $this->data->limit && intval( $this->data->limit ) !== -1 ) {
			$limit = intval( $this->data->limit );
		} else {
			$limit = '18446744073709551615';
		}

		if ( $this->data->offset ) {
			$offset = intval( $this->data->offset );
		} else {
			$offset = 0;
		}

		$sql = 'SELECT * FROM ' . GOOGLE_SS2DB_TABLE_NAME;
		if ( $wherekey && $whereval ) {
			$sql .= ' WHERE ' . $wherekey . ' = ' . $whereval;
		}
		$sql .= ' ORDER BY ' . $orderby . ' ' . $order;
		$sql .= ' LIMIT ' . $limit;
		if ( $limit ) {
			$sql .= ' OFFSET ' . intval( $offset );
		}

		$myrows = $wpdb->get_results( $sql ); // WPCS: unprepared SQL ok.

		return $myrows;
	}
}
