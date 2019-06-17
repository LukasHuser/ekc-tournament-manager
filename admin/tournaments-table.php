<?php

class Ekc_Tournaments_Table extends WP_List_Table {

	protected $tournaments_data;

	public function __construct($tournaments_data) {
		parent::__construct();
		$this->tournaments_data = $tournaments_data;
	}

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
			'swiss_system_rounds'	=> 'Swiss System rounds',
		);
		return $columns;
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->tournaments_data;
	}

	function column_code_name($item) {
		$actions = array(
			'teams'     => sprintf('<a href="?page=%s&amp;tournamentid=%s">Teams</a>', 'ekc-teams', $item['tournament_id']),
		);

		$actions['edit'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['tournament_id']);
		$actions['delete'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['tournament_id']);
		$actions['backup'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Backup</a>', $_REQUEST['page'], 'backup', $item['tournament_id']);

		return sprintf('%s %s', $item['code_name'], $this->row_actions($actions) );
	}

	function column_name($item) {
		$actions = array();

		if ( $item['elimination_rounds'] ) {
			$actions['elimination-bracket'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Elimination bracket</a>', 'ekc-bracket', 'elimination-bracket', $item['tournament_id']);
		}
		if ( $item['swiss_system_rounds'] > 0 ) {
			$actions['swiss-system'] = sprintf('<a href="?page=%s&amp;action=%s&amp;tournamentid=%s">Swiss System</a>', 'ekc-swiss', 'swiss-system', $item['tournament_id']);
		}
		return sprintf('%s %s', $item['name'], $this->row_actions($actions) );
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
			case 'swiss_system_rounds':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}
}

