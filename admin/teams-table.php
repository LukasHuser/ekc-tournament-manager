<?php

class Ekc_Teams_Table extends WP_List_Table {

	protected $teams_data;

	public function __construct($teams_data) {
		parent::__construct();
		$this->teams_data = $teams_data;
	}

	function get_columns(){
		$columns = array(
			'name'			=> 'Name',
			'is_active'		=> 'Active',
			'country'		=> 'Country',
			'email'			=> 'E-Mail',
			'phone'			=> 'Phone',
			'registration_date'	=> 'Registration date',
			'is_on_wait_list'	=> 'Waiting list',
			'camping_count' => 'Camping',
			'breakfast_count'	=> 'Breakfast',
			'seeding_score'	=> 'Seeding score',
			'players'		=> 'Players',
		);
		return $columns;
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->teams_data;
	}

	function column_name($item) {
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['team_id'], $_REQUEST['tournamentid']),
		);
		if ( filter_var($item['is_active'], FILTER_VALIDATE_BOOLEAN) ) {
			$actions['inactivate'] = sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Inactivate</a>', $_REQUEST['page'], 'inactivate', $item['team_id'], $_REQUEST['tournamentid']);
		}
		else {
			$actions['activate'] = sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Activate</a>', $_REQUEST['page'], 'activate', $item['team_id'], $_REQUEST['tournamentid']);
		}

		return sprintf('%s %s', $item['name'], $this->row_actions($actions) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'is_active':
			case 'country':
			case 'email':
			case 'phone':
			case 'registration_date':
			case 'is_on_wait_list':
			case 'camping_count':
			case 'breakfast_count':
			case 'seeding_score':
			case 'players':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}
}

