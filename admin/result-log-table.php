<?php

class Ekc_Result_Log_Table extends WP_List_Table {

	protected $tournament_id;

	public function __construct( $tournament_id ) {
		parent::__construct();
		$this->tournament_id = $tournament_id;
	}

	protected function get_table_classes() {
		$table_classes = parent::get_table_classes();
		// remove 'fixed' css class
		return array_filter( $table_classes, fn( $table_class ) => $table_class !== 'fixed' );
	}

	function get_columns(){
		$columns = array(
			'log_time'		=> esc_html__( 'Timestamp', 'ekc-tournament-manager' ),
			'team1'			=> esc_html__( 'Team 1', 'ekc-tournament-manager' ),
			'team2'			=> esc_html__( 'Team 2', 'ekc-tournament-manager' ),
			'result'		=> esc_html__( 'Result', 'ekc-tournament-manager' ),
			'stage'			=> esc_html__( 'Stage', 'ekc-tournament-manager' ),
			'log_team'		=> esc_html__( 'Changed by', 'ekc-tournament-manager' )
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

		$validation_helper = new Ekc_Validation_Helper();
		$db = new Ekc_Database_Access();
		$order_by_column = $validation_helper->validate_get_text( 'orderby' );
		$order = $validation_helper->validate_get_text( 'order' );
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
			return esc_html( $item[ $column_name ] );
			default:
			return '';
		}
	}


	function no_items() {
		esc_html_e( 'No results available.', 'ekc-tournament-manager' );
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

