<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ); // since WP 3.1.
}

/**
 * Lingotek Custom Fields Table class.
 */
class Lingotek_Custom_Fields_Table extends WP_List_Table {
	/**
	 * List of profiles.
	 *
	 * @var array
	 */
	protected $profiles;
	/**
	 * List of content types.
	 *
	 * @var array
	 */
	protected $content_types;

	/**
	 * Constructor
	 *
	 * @since 0.2
	 */
	function __construct() {
		parent::__construct(array(
			'plural'   => 'lingotek-custom-fields', // do not translate (used for css class).
			'ajax'   => false,
		));
	}

	/**
	 * Displays the item's meta_key
	 *
	 * @since 0.2
	 *
	 * @param array $item item.
	 * @return string
	 */
	function column_meta_key( $item ) {
		// var_dump($item);
		printf('<input type="checkbox" onClick="show(this);" class="boxes" name="%s" value="value1" > ',$item['meta_key'] );
		return isset( $item['meta_key'] ) ? esc_html( $item['meta_key'] ) : '';
	}

	/**
	 * Displays the item setting
	 *
	 * @since 0.2
	 *
	 * @param array $item item.
	 */
	function column_setting( $item ) {
		//var_dump($item);
		$settings = array( 'translate', 'copy', 'ignore' );
		$custom_field_choices = get_option( 'lingotek_custom_fields', array() );
    //var_dump($custom_field_choices);
		printf( '<select class="custom-field-setting" name="%1$s" id="%1$s">', 'settings[' . esc_html( $item['meta_key'] ) . ']' );

		// select the option from the lingotek_custom_fields option.
		foreach ( $settings as $setting ) {
			if ( $setting === $custom_field_choices[ $item['meta_key'] ] ) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo "\n\t<option value='" . esc_attr( $setting ) . "' " . esc_html( $selected ) . '>' . esc_attr( ucwords( $setting ) ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Gets the list of columns
	 *
	 * @since 0.2
	 *
	 * @return array the list of column titles
	 */
	function get_columns() {
		return array(

		'meta_key' => __( '<input type="checkbox" id="master" onClick="toggle(this);" value="value2" > Custom Field Key', 'lingotek-translation' ),
		'setting' => __( 'Action', 'lingotek-translation' ),
		);
	}

	/**
	 * Gets the list of sortable columns
	 *
	 * @since 0.2
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		return array(
		'meta_key' => array( 'meta_key', false ),
		);
	}

	/**
	 * Prepares the list of items for displaying
	 *
	 * @since 0.2
	 *
	 * @param array $data data.
	 */
	function prepare_items( $data = array() ) {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		/**
		 * Custom sorting comparator.
		 *
		 * @param  array $a array of strings.
		 * @param  array $b array of strings.
		 * @return int sort direction.
		 */
		function usort_reorder( $a, $b ) {
			$order = filter_input( INPUT_GET, 'order' );
			$orderby = filter_input( INPUT_GET, 'orderby' );
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // determine sort order.
			return (empty( $order ) || 'asc' === $order ) ? $result : -$result; // send final sort direction to usort.
		};

		if ( ! empty( $orderby ) ) { // no sort by default.
			usort( $data, 'usort_reorder' );
		}

		$total_items = count( $data );
		$this->items = $data;

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'  => count( $data ),
		));
	}
}
