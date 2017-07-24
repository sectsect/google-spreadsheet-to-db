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
		extract( $d, EXTR_SKIP );
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
		$this->data = json_decode(json_encode( $d ));
    }

	/**
	 * Get the rows.
	 *
	 * @return array "description".
	 */
	public function getrow() {
		global $wpdb;
		$sql = "SELECT * FROM " . GOOGLE_SS2DB_TABLE_NAME;
		if ( isset( $this->data->where->key ) && isset( $this->data->where->value ) ) {
			$sql .= " where " . $this->data->where->key . " = " . intval($this->data->where->value);
		}
		$sql .= " ORDER BY " . $this->data->orderby . " " . $this->data->order;
		if ( $this->data->limit ) {
			$sql .= " LIMIT " . intval($this->data->limit);
		}
		if ( $this->data->offset ) {
			$sql .= " OFFSET " . intval($this->data->offset);
		}
		$myrows = $wpdb->get_results( $sql );

        return $myrows;
    }
}
