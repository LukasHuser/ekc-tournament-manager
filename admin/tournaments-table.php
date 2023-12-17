<?php

class Ekc_Tournaments_Table extends WP_List_Table {

	function get_columns(){
		$columns = array(
			'code_name'			=> 'Code Name',
			'name'				=> 'Name',
			'team_size'			=> 'Team size',
			'tournament_date'		=> 'Date',
			'max_teams'			=> 'Max teams',
			'is_wait_list_enabled'		=> 'Wait list',
			'is_player_names_required'	=> 'Player names required',
			'tournament_system'	=> 'Tournament system',
			'elimination_rounds'	=> 'Elimination rounds',
			'swiss_system_rounds'	=> 'Swiss rounds',
		);
		return $columns;
	}

	function get_sortable_columns(){
		$columns = array(
			'code_name'			=> array( 'code_name', false ),
			'name'				=> array( 'name', false ),
			'team_size'			=> array( 'team_size', false ),
			'tournament_date'	=> array( 'tournament_date', false ),
			'max_teams'			=> array( 'max_teams', false ),
			'tournament_system'	=> array( 'tournament_system', false ),
			'elimination_rounds'	=> array( 'elimination_rounds', false ),
			'swiss_system_rounds'	=> array( 'swiss_system_rounds', false )
		);
		return $columns;
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$db = new Ekc_Database_Access();
		$order_by_column = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : null;  
		$order = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : null;
		// $order_by_column and $order are validated in get_all_tournaments_as_table
		$tournaments_data = $db->get_all_tournaments_as_table( $order_by_column, $order );

		$this->items = $tournaments_data;
	}

	function column_code_name( $item ) {
		$actions = array();
		$actions['teams'] = sprintf('<a href="?page=%s&amp;tournamentid=%s">Teams</a>', 'ekc-teams', esc_html( $item['tournament_id'] ) );
		$actions['edit'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Edit</a>', esc_html( $_REQUEST['page'] ), 'edit', esc_html( $item['tournament_id'] ) );

		return sprintf('%s %s', $item['code_name'], $this->row_actions($actions) );
	}

	function column_name( $item ) {
		$actions = array();

		if ( $item['elimination_rounds'] ) {
			$actions['elimination-bracket'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Elimination bracket</a>', 'ekc-bracket', 'elimination-bracket', esc_html( $item['tournament_id'] ) );
		}
		if ( $item['swiss_system_rounds'] > 0 ) {
			$actions['swiss-system'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Swiss System</a>', 'ekc-swiss', 'swiss-system', esc_html( $item['tournament_id'] ) );
		}
		return sprintf('%s %s', $item['name'], $this->row_actions($actions) );
	}

	function column_swiss_system_rounds( $item ) {
		$actions = array();
		$actions['delete'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Delete</a>', esc_html( $_REQUEST['page'] ), 'delete', esc_html( $item['tournament_id'] ) );
		$actions['backup'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Backup</a>', esc_html( $_REQUEST['page'] ), 'backup', esc_html( $item['tournament_id'] ) );
		$actions['result-log'] = sprintf('<a href="?page=%s&amp;tournamentid=%s">Result Log</a>', 'ekc-result-log', esc_html( $item['tournament_id'] ) );

		return sprintf('%s %s', $item['swiss_system_rounds'], $this->row_actions($actions) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'team_size':
			case 'tournament_date':
			case 'max_teams':
			case 'is_wait_list_enabled':
			case 'is_player_names_required':
			case 'tournament_system':
			case 'elimination_rounds':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}

	function no_items() {
		esc_html_e("No tournaments available yet.");
	}

	/**
	 * Complete override of pagination method of super class.
	 * We dont use pagination, but simply display the total number of items.
	 */
	protected function pagination( $which ) {
		$total_items = count( $this->items );
?>
		<div class='tablenav-pages one-page'><span class='displaying-num'>
		<?php esc_html_e( sprintf(
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items ))) ?>
		</span></div>
<?php
	}
}

