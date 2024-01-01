<?php

class Ekc_Result_Log_Table extends WP_List_Table {

	protected $tournament_id;

	public function __construct( $tournament_id ) {
		parent::__construct();
		$this->tournament_id = $tournament_id;
	}

	function get_columns(){
		$columns = array(
			'log_time'		=> 'Timestamp',
			'team1'			=> 'Team 1',
			'team2'			=> 'Team 2',
			'result'		=> 'Result',
			'stage'			=> 'Stage',
			'log_team'		=> 'Changed by',
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'log_time'	=> array('log_time', false),
			'team1'		=> array('team1', false),
			'team2'		=> array('team2', false),
			'result'	=> array('result', false),
			'stage'		=> array('stage', false),
			'log_team'	=> array('log_team', false)
		);
		return $sortable_columns;
	  }

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$db = new Ekc_Database_Access();
		$order_by_column = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : null;  
		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : null;
		// $order_by_column and $order are validated in get_result_log_as_table
		$result_log = $db->get_result_log_as_table( $this->tournament_id, $order_by_column, $order );
		$this->items = $result_log;
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'log_time':
			case 'team1':
			case 'team2':
			case 'result':
			case 'stage':
			case 'log_team':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}


	function no_items() {
		esc_html_e("No results available.");
	}

	/**
	 * Complete override of pagination method of super class.
	 * We dont use pagination, but simply display the total number of items.
	 */
	protected function pagination( $which ) {
		$total_items = !$this->items ? 0 : count( $this->items );
?>
		<div class='tablenav-pages one-page'><span class='displaying-num'>
		<?php esc_html_e( sprintf(
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items ))) ?>
		</span></div>
<?php
	}
}

