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
		if ( isset( $this->data->where->key )) {
			$wherekey = $this->data->where->key;
		}
		if ( isset( $this->data->where->value )) {
			$whereval = intval( $this->data->where->value );
		}
		if ( isset( $this->data->orderby )) {
			$orderby = $this->data->orderby;
		} else {
			$orderby = 'date';
		}
		if ( isset( $this->data->order )) {
			$order = $this->data->order;
		} else {
			$order = 'DESC';
		}
		if ( isset( $this->data->limit )) {
			$limit = intval( $this->data->limit );
		}
		if ( isset( $this->data->offset )) {
			$offset = intval( $this->data->offset );
		}

		if ( isset( $wherekey ) && isset( $this->data->where->value ) ) {
			if ( $this->data->limit && $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						where %s = %d
						ORDER BY %s %s
						LIMIT %d
						OFFSET %d
					",
					$wherekey,
					$whereval,
					$orderby,
					$order,
					$limit,
					$offset
				) );
			} elseif ( $this->data->limit && ! $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						where %s = %d
						ORDER BY %s %s
						LIMIT %d
					",
					$wherekey,
					$whereval,
					$orderby,
					$order,
					$limit
				) );
			} elseif ( ! $this->data->limit && $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						where %s = %d
						ORDER BY %s %s
						OFFSET %d
					",
					$wherekey,
					$whereval,
					$orderby,
					$order,
					$offset
				) );
			} else {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						where %s = %d
						ORDER BY %s %s
					",
					$wherekey,
					$whereval,
					$orderby,
					$order
				) );
			}
		} else {
			if ( $this->data->limit && $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						ORDER BY %s %s
						LIMIT %d
						OFFSET %d
					",
					$orderby,
					$order,
					$limit,
					$offset
				) );
			} elseif ( $this->data->limit && ! $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						ORDER BY %s %s
						LIMIT %d
					",
					$orderby,
					$order,
					$limit
				) );
			} elseif ( ! $this->data->limit && $this->data->offset ) {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						ORDER BY %s %s
						OFFSET %d
					",
					$orderby,
					$order,
					$offset
				) );
			} else {
				$myrows = $wpdb->get_results( $wpdb->prepare(
					"
						SELECT * FROM {$table}
						ORDER BY %s %s
					",
					$orderby,
					$order
				) );
			}
		}

		return $myrows;
	}
}
