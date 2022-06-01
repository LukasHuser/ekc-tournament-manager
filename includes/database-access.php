<?php

/**
 * General database access 
 */
class Ekc_Database_Access {

	/***************************************************************************************************************************
	 * Tournament
	 ***************************************************************************************************************************/

	public function get_all_tournaments_as_table( $sort_column = 'tournament_date', $sort = 'desc' ) {
		global $wpdb;
		$sql_sort = $this->validate_tournament_table_sort_column( $sort_column ) . ' ' . ($sort === 'asc' ? 'ASC' : 'DESC');
		$results = $wpdb->get_results( 
			"
			SELECT tournament_id, code_name, name, tournament_date, team_size, max_teams, 
                    case when is_wait_list_enabled = 1 then 'yes' else 'no' end as is_wait_list_enabled, 
					case when is_player_names_required = 1 then 'yes' else 'no' end as is_player_names_required,
					tournament_system, elimination_rounds, swiss_system_rounds   
			FROM   {$wpdb->prefix}ekc_tournament
			ORDER BY {$sql_sort}
			",
		ARRAY_A );

		return $results;
	}

	private function validate_tournament_table_sort_column( $column ) {
		$valid_columns = array(
			'code_name', 'name', 'team_size', 'tournament_date', 'max_teams',
			'tournament_system', 'elimination_rounds', 'swiss_system_rounds'
		);
		if ( in_array( $column, $valid_columns, true) ) {
			return $column;
		}
		return 'tournament_date'; // default sort column
	}

	public function insert_tournament($tournament) {
		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix . 'ekc_tournament', 
			array( 
				'code_name'			=> $this->truncate_string( $tournament->get_code_name(), 50 ), 
				'name'				=> $this->truncate_string( $tournament->get_name(), 500 ),
				'tournament_date'		=> $tournament->get_date(),
				'team_size'			=> $this->truncate_string( $tournament->get_team_size(), 20 ),
				'max_teams'			=> $tournament->get_max_teams(),
				'is_wait_list_enabled'		=> intval( $tournament->is_wait_list_enabled() ),
				'is_player_names_required'	=> intval( $tournament->is_player_names_required() ),
				'is_auto_backup_enabled'	=> intval( $tournament->is_auto_backup_enabled() ),
				'tournament_system'			=> $this->truncate_string( $tournament->get_tournament_system(), 20 ),
				'elimination_rounds'		=> $this->truncate_string( $tournament->get_elimination_rounds(), 20 ),
				'elimination_max_points_per_round'		=> $tournament->get_elimination_max_points_per_round(),
				'swiss_system_rounds'		=> $tournament->get_swiss_system_rounds(),
				'swiss_system_max_points_per_round'		=> $tournament->get_swiss_system_max_points_per_round(),
				'swiss_system_additional_rounds'	=> $tournament->get_swiss_system_additional_rounds(),
				'swiss_system_slide_match_rounds'	=> $tournament->get_swiss_system_slide_match_rounds(),
				'swiss_system_round_time'	=> $tournament->get_swiss_system_round_time(),
				'swiss_system_tiebreak_time'	=> $tournament->get_swiss_system_tiebreak_time(), 
				'swiss_system_start_pitch'	=> $tournament->get_swiss_system_start_pitch(),
			), 
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d' ) 
		);

		return $wpdb->insert_id;
	}

	public function update_tournament($tournament) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_tournament', 
			array( 
				'code_name'			=> $this->truncate_string( $tournament->get_code_name(), 50 ),
				'name'				=> $this->truncate_string( $tournament->get_name(), 500 ),
				'tournament_date'		=> $tournament->get_date(),
				'team_size'			=> $this->truncate_string( $tournament->get_team_size(), 20 ),
				'max_teams'			=> $tournament->get_max_teams(),
				'is_wait_list_enabled'		=> intval( $tournament->is_wait_list_enabled() ),
				'is_player_names_required'	=> intval( $tournament->is_player_names_required() ),
				'is_auto_backup_enabled'	=> intval( $tournament->is_auto_backup_enabled() ),
				'tournament_system'			=> $this->truncate_string( $tournament->get_tournament_system(), 20 ),
				'elimination_rounds'		=> $this->truncate_string( $tournament->get_elimination_rounds(), 20 ),
				'elimination_max_points_per_round'		=> $tournament->get_elimination_max_points_per_round(),
				'swiss_system_rounds'		=> $tournament->get_swiss_system_rounds(),
				'swiss_system_max_points_per_round'		=> $tournament->get_swiss_system_max_points_per_round(),
				'swiss_system_additional_rounds'	=> $tournament->get_swiss_system_additional_rounds(),
				'swiss_system_slide_match_rounds'	=> $tournament->get_swiss_system_slide_match_rounds(),
				'swiss_system_round_time'	=> $tournament->get_swiss_system_round_time(),
				'swiss_system_tiebreak_time'	=> $tournament->get_swiss_system_tiebreak_time(), 
				'swiss_system_start_pitch'	=> $tournament->get_swiss_system_start_pitch(),
			), 
			array( 'tournament_id'		=> $tournament->get_tournament_id() ),
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d' ),
			array( '%d' )
		);
	}

	public function delete_tournament($tournament_id) {
		global $wpdb;
		$team_ids = $wpdb->get_col( $wpdb->prepare( 
			"
			SELECT      team_id
			FROM        {$wpdb->prefix}ekc_team
			WHERE       tournament_id = %d
			",
			$tournament_id
		) );
		foreach ( $team_ids as $team_id ) {
			$this->delete_players($team_id);
			$wpdb->delete( $wpdb->prefix . 'ekc_team', array( 'team_id' => $team_id ) );				
		}
		$wpdb->delete( $wpdb->prefix . 'ekc_result', array( 'tournament_id' => $tournament_id ) );
		$wpdb->delete( $wpdb->prefix . 'ekc_tournament', array( 'tournament_id' => $tournament_id ) );
	}

	public function get_tournament_by_id($tournament_id) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 
			"
			SELECT tournament_id, code_name, name, tournament_date,
			       team_size, max_teams, is_wait_list_enabled, 
				   is_player_names_required, is_auto_backup_enabled, 
				   tournament_system, elimination_rounds, elimination_max_points_per_round, 
				   swiss_system_rounds, swiss_system_max_points_per_round, swiss_system_additional_rounds, 
				   swiss_system_slide_match_rounds, swiss_system_round_time, swiss_system_tiebreak_time, swiss_system_start_pitch, 
				   shareable_link_url_prefix, shareable_link_email_text, shareable_link_sender_email
			FROM   {$wpdb->prefix}ekc_tournament
			WHERE  tournament_id = %d
			",
        		$tournament_id
		) );

		if ( ! $row ) {
			return null;
		}

		return $this->create_tournament_from_table_row( $row );
	}

	public function get_tournament_by_code_name($code_name) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 
			"
			SELECT tournament_id, code_name, name, tournament_date, 
			       team_size, max_teams, is_wait_list_enabled, 
				   is_player_names_required, is_auto_backup_enabled, 
				   tournament_system, elimination_rounds, elimination_max_points_per_round, 
				   swiss_system_rounds, swiss_system_max_points_per_round, swiss_system_additional_rounds, 
				   swiss_system_slide_match_rounds, swiss_system_round_time, swiss_system_tiebreak_time, swiss_system_start_pitch, 
				   shareable_link_url_prefix, shareable_link_email_text, shareable_link_sender_email
			FROM   {$wpdb->prefix}ekc_tournament
			WHERE  code_name = %s
			",
        		$code_name
		) ); 

		if ( ! $row ) {
			return null;
		}

		return $this->create_tournament_from_table_row( $row );
	}

	private function create_tournament_from_table_row( $row ) {
		$tournament = new Ekc_Tournament();
		$tournament->set_tournament_id( $row->tournament_id );
		$tournament->set_code_name( strval( $row->code_name ) );
		$tournament->set_name( strval( $row->name ) );
		$tournament->set_date( strval( $row->tournament_date ) );
		$tournament->set_team_size( strval( $row->team_size ) );
		$tournament->set_max_teams( $row->max_teams );
		$tournament->set_wait_list_enabled( boolval( $row->is_wait_list_enabled ) );
		$tournament->set_player_names_required( boolval( $row->is_player_names_required ) );
		$tournament->set_auto_backup_enabled( boolval( $row->is_auto_backup_enabled ) );
		$tournament->set_tournament_system( strval( $row->tournament_system ) );
		$tournament->set_elimination_rounds( strval( $row->elimination_rounds ) );
		$tournament->set_elimination_max_points_per_round( $row->elimination_max_points_per_round );
		$tournament->set_swiss_system_rounds( $row->swiss_system_rounds );
		$tournament->set_swiss_system_max_points_per_round( $row->swiss_system_max_points_per_round );
		$tournament->set_swiss_system_additional_rounds( $row->swiss_system_additional_rounds );
		$tournament->set_swiss_system_slide_match_rounds( $row->swiss_system_slide_match_rounds );
		$tournament->set_swiss_system_round_time( $row->swiss_system_round_time );
		$tournament->set_swiss_system_tiebreak_time( $row->swiss_system_tiebreak_time );
		$tournament->set_swiss_system_start_pitch( $row->swiss_system_start_pitch );
		$tournament->set_shareable_link_url_prefix( $row->shareable_link_url_prefix );
		$tournament->set_shareable_link_email_text( $row->shareable_link_email_text );
		$tournament->set_shareable_link_sender_email( $row->shareable_link_sender_email );
		return $tournament;
	}


	public function is_wait_list_enabled( $tournament_id ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COALESCE(t.is_wait_list_enabled, 0)
			FROM   {$wpdb->prefix}ekc_tournament t
			WHERE  t.tournament_id = %d
                        ",
			$tournament_id
		));		
		return boolval( $result );
	}

	public function get_tournament_round_start( $tournament_id, $round ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT round_start_time
			FROM   {$wpdb->prefix}ekc_tournament_round
			WHERE  tournament_id = %d
			AND    tournament_round = %d
                        ",
			$tournament_id,
			$round
		));		
		return $result;
	}

	public function delete_tournament_round_start( $tournament_id, $round ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ekc_tournament_round', array( 'tournament_id' => $tournament_id, 'tournament_round' => $round ) );
	}

	public function store_tournament_round_start( $tournament_id, $round ) {
		$this->delete_tournament_round_start( $tournament_id, $round );

		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix . 'ekc_tournament_round', 
			array( 'tournament_id'		=> $tournament_id,
				   'tournament_round'	=> $round ,
				   'round_start_time'	=> date('Y-m-d H:i:s')
			),
			array( '%d', '%d', '%s' )
		);
	}

	/***************************************************************************************************************************
	 * Teams
	 ***************************************************************************************************************************/

	public function get_active_team_ids( $tournament_id ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id 
			FROM   {$wpdb->prefix}ekc_team t
			WHERE  COALESCE(t.is_active, 0) = 1
			AND    COALESCE(t.is_on_wait_list, 0) <> 1
			AND    t.tournament_id = %d
			",
			$tournament_id
		));

		$team_ids = array();
		foreach( $results as $row ) {
			$team_ids[] = $row->team_id;
		}
		return $team_ids;
	}

	public function get_active_teams_count_by_code_name( $tournament_code_name ) {
		global $wpdb;
		$max_teams = $this->get_max_teams_by_code_name( $tournament_code_name );
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(t.team_id)
			FROM   {$wpdb->prefix}ekc_team t
			INNER JOIN {$wpdb->prefix}ekc_tournament tx
			ON     t.tournament_id = tx.tournament_id
			WHERE  COALESCE(t.is_active, 0) = 1
			AND    COALESCE(t.is_on_wait_list, 0) <> 1
			AND    tx.code_name = %s
                        ",
			$tournament_code_name
		));		
		if ( $max_teams > 0 && $result > $max_teams ) {
			return $max_teams;
		}		
		return $result;
	}

	public function get_active_teams_count_by_tournament_id( $tournament_id ) {
		global $wpdb;
		$max_teams = $this->get_max_teams_by_tournament_id( $tournament_id );
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(t.team_id)
			FROM   {$wpdb->prefix}ekc_team t
			INNER JOIN {$wpdb->prefix}ekc_tournament tx
			ON     t.tournament_id = tx.tournament_id
			WHERE  COALESCE(t.is_active, 0) = 1
			AND    COALESCE(t.is_on_wait_list, 0) <> 1
			AND    tx.tournament_id = %d
                        ",
			$tournament_id
		));
		if ( $max_teams > 0 && $result > $max_teams ) {
			return $max_teams;
		}		
		return $result;
	}

	public function get_max_teams_by_tournament_id( $tournament_id ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COALESCE(t.max_teams, 0)
			FROM   {$wpdb->prefix}ekc_tournament t
			WHERE  t.tournament_id = %d
                        ",
			$tournament_id
		));		
		return $result;
	}

	public function get_max_teams_by_code_name( $tournament_code_name ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COALESCE(t.max_teams, 0)
			FROM   {$wpdb->prefix}ekc_tournament t
			WHERE  t.code_name = %s
                        ",
			$tournament_code_name
		));		
		return $result;
	}

	public function get_active_teams( $tournament_id, $limit = 0, $sort = 'asc') {
		global $wpdb;
		$max_teams = $this->get_max_teams_by_tournament_id( $tournament_id );
		$limit = filter_var( $limit, FILTER_VALIDATE_INT );
		$sql_limit = $limit > 0 ? ' LIMIT ' . $limit : '';
		$sql_teams_limit = $max_teams > 0 ? ' LIMIT ' . $max_teams : '';
		$sql_sort = $sort === 'desc' ? 'DESC' : 'ASC';

		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, t.tournament_id, t.name, 
			       LOWER(t.country) as country, t.club,
				   t.is_active, t.email, t.phone, t.registration_date, 
				   t.camping_count, t.breakfast_count, t.is_registration_fee_paid,
				   t.is_on_wait_list, t.registration_order, 
				   t.seeding_score, t.initial_score, t.virtual_rank, 
				   t.shareable_link_id
			FROM   {$wpdb->prefix}ekc_team t
			INNER JOIN (
				SELECT x.team_id
				FROM   {$wpdb->prefix}ekc_team x
				WHERE  x.tournament_id = %d
				AND    COALESCE(x.is_active, 0) = 1
				AND    COALESCE(x.is_on_wait_list, 0) <> 1
				ORDER BY COALESCE(x.registration_order, x.team_id) ASC
				{$sql_teams_limit}
			) as t2
			ON t.team_id = t2.team_id
			ORDER BY COALESCE(t.registration_order, t.team_id) {$sql_sort}
			{$sql_limit}",
			$tournament_id
		));

		$player_map = $this->get_players_map( $tournament_id, true );
		$teams = array();
		foreach ( $results as $row ) {
			$team = $this->create_team_from_table_row( $row );
			if ( array_key_exists( $team->get_team_id(), $player_map )) {
				$team->set_players( $player_map[$team->get_team_id()] );
			}
			$teams[] = $team;
		}
		return $teams;
	}

	private function create_team_from_table_row( $row ) {
		$team = new Ekc_Team();
		$team->set_team_id( $row->team_id );
		$team->set_tournament_id( $row->tournament_id );
		$team->set_name( strval( $row->name ));
		$team->set_country( strval( $row->country ));
		$team->set_club( strval( $row->club ));
		$team->set_active( boolval( $row->is_active ));
		$team->set_email( strval( $row->email ));
		$team->set_phone( strval( $row->phone ));
		$team->set_registration_date( strval( $row->registration_date ));
		$team->set_camping_count( $row->camping_count );
		$team->set_breakfast_count( $row->breakfast_count );
		$team->set_registration_fee_paid( boolval( $row->is_registration_fee_paid ));
		$team->set_on_wait_list( boolval( $row->is_on_wait_list ));
		$team->set_registration_order( $row->registration_order );
		$team->set_seeding_score( $row->seeding_score );
		$team->set_initial_score( $row->initial_score );
		$team->set_virtual_rank( $row->virtual_rank );
		$team->set_shareable_link_id( $row->shareable_link_id );
		return $team;
	}

	public function get_active_teams_on_wait_list($tournament_id, $sort = 'asc') {
		global $wpdb;
		$is_wait_list_enabled = $this->is_wait_list_enabled( $tournament_id );
		if ( !$is_wait_list_enabled ) {
			return null;
		}
		$max_teams = $this->get_max_teams_by_tournament_id( $tournament_id );
		if ( $max_teams <= 0 ) {
			return null;
		}
		$sql_teams_limit = $max_teams > 0 ? ' LIMIT ' . $max_teams : '';
		$sql_sort = $sort === 'desc' ? 'DESC' : 'ASC';

		// use a temporary table (as mysql does not support common table expressions and no LIMIT clause in an IN expression)
		// temporary table: all active teams not on waiting list (same as in method get_active_teams)
		$wpdb->query( $wpdb->prepare( 
			"
			CREATE TEMPORARY TABLE {$wpdb->prefix}ekc_temp_active_teams
			SELECT x.team_id
			FROM   {$wpdb->prefix}ekc_team x
			WHERE  x.tournament_id = %d
			AND    COALESCE(x.is_active, 0) = 1
			AND    COALESCE(x.is_on_wait_list, 0) <> 1
			ORDER BY COALESCE(x.registration_order, x.team_id) ASC
			{$sql_teams_limit}
			",
			$tournament_id
		));

		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, t.tournament_id, t.name, 
			       LOWER(t.country) as country, t.club,
				   t.is_active, t.email, t.phone, t.registration_date, 
				   t.camping_count, t.breakfast_count, t.is_registration_fee_paid,
				   t.is_on_wait_list, t.registration_order, 
				   t.seeding_score, t.initial_score, t.virtual_rank, 
				   t.shareable_link_id 
			FROM   {$wpdb->prefix}ekc_team t
			WHERE  COALESCE(t.is_active, 0) = 1
			AND    t.tournament_id = %d
			AND    t.team_id NOT IN (
				SELECT x.team_id 
				FROM   {$wpdb->prefix}ekc_temp_active_teams x)
			ORDER BY COALESCE(t.registration_order, t.team_id) {$sql_sort}",
			$tournament_id
		));

		// drop temporary table (needed if multiple wait lists are shown on the same page)
		$wpdb->query( "DROP TEMPORARY TABLE {$wpdb->prefix}ekc_temp_active_teams" );

		$teams = array();
		foreach ( $results as $row ) {
			$team = $this->create_team_from_table_row( $row );
			$team->set_players( $this->get_active_players($row->team_id));
			$teams[] = $team;
		}
		return $teams;
	}

	public function get_all_teams( $tournament_id ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, t.tournament_id, t.name, 
			       LOWER(t.country) as country, t.club, 
				   t.is_active, t.email, t.phone, t.registration_date, 
				   t.camping_count, t.breakfast_count, t.is_registration_fee_paid,
				   t.is_on_wait_list, t.registration_order, 
				   t.seeding_score, t.initial_score, t.virtual_rank, 
				   t.shareable_link_id
			FROM   {$wpdb->prefix}ekc_team t
			WHERE  t.tournament_id = %d
			ORDER BY t.team_id ASC",
			$tournament_id
		));

		$player_map = $this->get_players_map( $tournament_id, false );
		$teams = array();
		foreach ( $results as $row ) {
			$team = $this->create_team_from_table_row( $row );
			if ( array_key_exists( $team->get_team_id(), $player_map )) {
				$team->set_players( $player_map[$team->get_team_id()] );
			}
			$teams[] = $team;
		}
		return $teams;
	}

	public function get_all_teams_as_table( $tournament_id, $sort_column = 'registration_date', $sort = 'asc', $filter ) {
		global $wpdb;
		$sql_sort = $this->create_team_table_sort_column_sql( $sort_column, 't') . ' ' . ($sort === 'desc' ? 'DESC' : 'ASC');
		$sql_filter = $this->create_team_table_filter( $filter, 't' );
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, t.name, 
			       LOWER(t.country) as country, t.club,
				   case when t.is_active = 1 then 'yes' else 'no' end as is_active, 
				   t.email, t.phone, t.registration_date, t.registration_order, 
				   t.seeding_score, 
				   case when t.is_on_wait_list = 1 then 'yes' else 'no' end as is_on_wait_list,
                   GROUP_CONCAT( CONCAT(p.first_name, ' ', p.last_name, ' (', p.country, ')') SEPARATOR ', ') as players
			FROM   {$wpdb->prefix}ekc_team t
			LEFT OUTER JOIN {$wpdb->prefix}ekc_player p
			ON t.team_id = p.team_id
			WHERE t.tournament_id = %d
			      {$sql_filter} 
			GROUP BY t.team_id, t.name, t.country, t.club, t.is_active, t.email, t.phone, t.registration_date, t.registration_order, t.seeding_score, t.is_on_wait_list
			ORDER BY {$sql_sort}
			",
			$tournament_id),
		ARRAY_A );

		return $results;
	}

	private function create_team_table_sort_column_sql( $column, $table_alias ) {
		$sql_alias = $table_alias ? $table_alias . '.' : '';
		$valid_columns = array(
		  'name', 'is_active', 'country', 'club', 'registration_date', 'registration_order',
		  'is_on_wait_list', 'seeding_score'
		);
		if ( in_array( $column, $valid_columns, true) ) {
			return $sql_alias . $column;
		}
		return $sql_alias . 'registration_date'; // default sort column
	}

	private function create_team_table_filter( $filter, $table_alias ) {
		$sql_alias = $table_alias ? $table_alias . '.' : '';
		$sql_filter = '';
		foreach ( $filter as $key => $value ) {
			if ( $key === 'is_active' || $key === 'is_on_wait_list' ) {
				if ( $value === '1' || $value === '0') {
					$sql_filter .= ' AND ' . $sql_alias . $key . ' = ' . $value;
				}
			}
			elseif ( $key === 'country' ) {
				if ( in_array( $value, array_keys( Ekc_Drop_Down_Helper::COUNTRY_COMMON ) ) ) {
					$sql_filter .= ' AND ' . $sql_alias . $key . " = '" . $value . "'";
				}
			}
		}
		return $sql_filter;
	}

	public function get_all_teams_as_csv( $tournament_id ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT  'team_id', 'name',
			        'country', 'club',
			        'active', 'email', 'phone', 'registration_date', 'order', 
					'camping', 'breakfast', 'registration_fee_paid',
					'wait_list', 'seeding_score', 'initial_score', 'virtual_rank',
					'players'
			UNION ALL
			SELECT	t.team_id, t.name, 
			        LOWER(t.country) as country, t.club, 
					case when t.is_active = 1 then 'yes' else 'no' end as is_active, 
					t.email, t.phone, t.registration_date, t.registration_order, 
					t.camping_count, t.breakfast_count, 
					case when t.is_registration_fee_paid = 1 then 'yes' else 'no' end as registration_fee_paid,
					case when t.is_on_wait_list = 1 then 'yes' else 'no' end as is_on_wait_list, 
					t.seeding_score, t.initial_score, t.virtual_rank,
                    GROUP_CONCAT( CONCAT(p.first_name, ' ', p.last_name, ' (', p.country, ')') SEPARATOR ', ') as players
			FROM   {$wpdb->prefix}ekc_team t
			LEFT OUTER JOIN {$wpdb->prefix}ekc_player p
			ON t.team_id = p.team_id
			WHERE t.tournament_id = %d
			GROUP BY t.team_id, t.registration_order, t.name, t.country, t.club, t.is_active, t.email, t.phone, t.registration_date, t.camping_count, t.breakfast_count, t.is_registration_fee_paid, t.is_on_wait_list, t.seeding_score, t.initial_score, t.virtual_rank
			",
			$tournament_id),
		ARRAY_A );

		return $results;
	}

	public function insert_team($team) {
		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix . 'ekc_team', 
			array( 
				'tournament_id'		=> $team->get_tournament_id(), 
				'name'			=> $this->truncate_string( $team->get_name(), 500 ),
				'country'		=> $this->truncate_string( $team->get_country(), 20 ),
				'club'          => $this->truncate_string( $team->get_club(), 500 ),
				'is_active'		=> intval( $team->is_active()),
				'email'			=> $this->truncate_string( $team->get_email(), 500 ),
				'phone'			=> $this->truncate_string( $team->get_phone(), 50 ),
				'registration_date'	=> $team->get_registration_date(),
				'camping_count'	=> $team->get_camping_count(),
				'breakfast_count'	=> $team->get_breakfast_count(),
				'is_registration_fee_paid'	=> intval( $team->is_registration_fee_paid()),
				'is_on_wait_list'	=> intval( $team->is_on_wait_list()),
				'registration_order' => $team->get_registration_order(),
				'seeding_score'	=> $team->get_seeding_score(),
				'initial_score'	=> $team->get_initial_score(),
				'virtual_rank'	=> $team->get_virtual_rank(),
			), 
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%d' ) 
		);

		$team_id = $wpdb->insert_id;
		// set team_id as initial registration_order, if not manually set
		if ( is_null( Ekc_Type_Helper::opt_floatval( $team->get_registration_order() ) ) ) {
			$wpdb->update( 
				$wpdb->prefix . 'ekc_team', 
				array( 
					'registration_order' => $team_id,
				),
				array( 'team_id'		=> $team_id ),
				array( '%f' ),
				array( '%d' )
			);
		}

		$this->insert_players( $team_id, $team->get_players() );

		return $team_id;
	}

	public function update_team( $team ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_team', 
			array( 
				'tournament_id'		=> $team->get_tournament_id(), 
				'name'			=> $this->truncate_string( $team->get_name(), 500 ),
				'country'		=> $this->truncate_string( $team->get_country(), 20 ),
				'club'          => $this->truncate_string( $team->get_club(), 500 ),
				'is_active'		=> intval( $team->is_active() ),
				'email'			=> $this->truncate_string( $team->get_email(), 500 ),
				'phone'			=> $this->truncate_string( $team->get_phone(), 50 ),
				'camping_count'	=> $team->get_camping_count(),
				'breakfast_count'	=> $team->get_breakfast_count(),
				'is_registration_fee_paid'	=> intval( $team->is_registration_fee_paid() ),
				'is_on_wait_list'	=> intval( $team->is_on_wait_list() ),
				'registration_order' => $team->get_registration_order(),
				'seeding_score'	=> $team->get_seeding_score(),
				'initial_score'	=> $team->get_initial_score(),
				'virtual_rank'	=> $team->get_virtual_rank(),
			),
			array( 'team_id'		=> $team->get_team_id() ),
			array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%d' ),
			array( '%d' )
		);

		$this->delete_players( $team->get_team_id() );
		$this->insert_players( $team->get_team_id(), $team->get_players() );
	}

	public function get_team_by_id($team_id) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 
			"
			SELECT team_id, tournament_id, name, 
			       LOWER(country) as country, club, 
				   is_active, email, phone, registration_date, 
				   camping_count, breakfast_count, is_registration_fee_paid,
				   is_on_wait_list, registration_order, 
				   seeding_score, initial_score, virtual_rank, 
				   shareable_link_id 
			FROM   {$wpdb->prefix}ekc_team
			WHERE  team_id = %d
			",
        	$team_id
		) ); 

		if ( ! $row ) {
			return null;
		} 

		$team = $this->create_team_from_table_row( $row );
		$team->set_players($this->get_active_players($row->team_id));

		return $team;
	}

	public function set_team_active( $team_id, $is_active ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_team', 
			array( 
				'is_active'		=> intval( $is_active ),
			),
			array( 'team_id'		=> $team_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	public function set_team_on_wait_list( $team_id, $is_on_wait_list ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_team', 
			array( 
				'is_on_wait_list'	=> intval( $is_on_wait_list ),
			),
			array( 'team_id'		=> $team_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/***************************************************************************************************************************
	 * Players
	 ***************************************************************************************************************************/

	public function get_active_players($team_id) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT player_id, first_name, last_name, LOWER(country) as country, is_active, is_captain
			FROM   {$wpdb->prefix}ekc_player
			WHERE  team_id = %d
                        AND    is_active = 1
			ORDER BY player_id ASC
			",
			$team_id
		));

		$players = array();
		foreach ( $results as $row ) {
			$player = new Ekc_Player();
			$player->set_player_id( $row->player_id );
			$player->set_first_name( strval( $row->first_name ) );
			$player->set_last_name( strval( $row->last_name ) );
			$player->set_country( strval( $row->country ) );
			$player->set_active( boolval( $row->is_active ) );
			$player->set_captain( boolval( $row->is_captain ) );
			$players[] = $player;
		}
		return $players;
	}

	public function get_players_map( $tournament_id, $active_only ) {
		global $wpdb;

		$active_constraint = "";
		if ( $active_only ) {
			$active_constraint = " AND t.is_active = 1 AND p.is_active = 1";
		}

		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, p.player_id, p.first_name, p.last_name, LOWER(p.country) as country, p.is_active, p.is_captain
			FROM   {$wpdb->prefix}ekc_team t
			JOIN   {$wpdb->prefix}ekc_player p
			ON t.team_id = p.team_id
			WHERE  t.tournament_id = %d
			{$active_constraint}
			",
			$tournament_id
		));

		$player_map = array();
		foreach ( $results as $row ) {
			$player = new Ekc_Player();
			$player->set_player_id( $row->player_id );
			$player->set_first_name( strval( $row->first_name ) );
			$player->set_last_name( strval( $row->last_name ) );
			$player->set_country( strval( $row->country ) );
			$player->set_active( boolval( $row->is_active ) );
			$player->set_captain( boolval( $row->is_captain ) );
			$team_id = intval( $row->team_id );
			if ( ! array_key_exists( $team_id , $player_map )) {
				$player_map[$team_id] = array();
			}
			$player_map[$team_id][] = $player;
		}
		return $player_map;
	}

	public function delete_players($team_id) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ekc_player', array( 'team_id' => $team_id ) );
	}

	public function insert_players($team_id, $players) {
		if (is_array($players)) {
			foreach ( $players as $player ) {
				$this->insert_player($team_id , $player);
			}
		}
	}

	public function insert_player( $team_id, $player ) {
		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix . 'ekc_player', 
			array( 
				'team_id'	=> $team_id,
				'last_name'	=> $this->truncate_string( $player->get_last_name(), 500 ),
				'first_name'	=> $this->truncate_string( $player->get_first_name(), 500 ),
				'country'	=> $this->truncate_string( $player->get_country(), 20 ),
				'is_active'	=> intval( $player->is_active() ),
				'is_captain'	=> intval( $player->is_captain() ),
			), 
			array( '%d', '%s', '%s', '%s', '%d', '%d' ) 
		);

		return $wpdb->insert_id;
	}

	/***************************************************************************************************************************
	 * Shareable links
	 ***************************************************************************************************************************/

	public function update_shareable_link_data( $tournament ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_tournament', 
			array( 
				'shareable_link_url_prefix'	=> $this->truncate_string( $tournament->get_shareable_link_url_prefix(), 500 ),
				'shareable_link_sender_email'	=> $this->truncate_string( $tournament->get_shareable_link_sender_email(), 500 ),
				'shareable_link_email_text' => $this->truncate_string( $tournament->get_shareable_link_email_text(), 5000 ),
			),
			array( 'tournament_id'		=> $tournament->get_tournament_id() ),
			array( '%s' ),
			array( '%s' ),
			array( '%d' )
		);
	}

	public function update_shareable_link_id( $team_id, $shareable_link_id ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_team', 
			array( 
				'shareable_link_id'	=> $shareable_link_id,
			),
			array( 'team_id'		=> $team_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	public function get_all_shareable_links_as_table( $tournament_id, $sort_column, $sort = 'asc', $filter ) {
		global $wpdb;
		$sql_sort = $this->create_team_table_sort_column_sql( $sort_column, 't') . ' ' . ($sort === 'desc' ? 'DESC' : 'ASC');
		$sql_filter = $this->create_team_table_filter( $filter, 't' );
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id, t.name, LOWER(t.country) as country, case when t.is_active = 1 then 'yes' else 'no' end as is_active, t.email as email, t.shareable_link_id
			FROM   {$wpdb->prefix}ekc_team t
			WHERE t.tournament_id = %d
			      {$sql_filter} 
			ORDER BY {$sql_sort}
			",
			$tournament_id),
		ARRAY_A );

		return $results;
	}

	public function get_team_by_shareable_link_id( $link_id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 
			"
			SELECT team_id, tournament_id, name, LOWER(country) as country, is_active, email, phone, registration_date, camping_count, breakfast_count, is_on_wait_list, registration_order, seeding_score, initial_score, virtual_rank, shareable_link_id 
			FROM   {$wpdb->prefix}ekc_team
			WHERE  shareable_link_id = %s
			",
       		$link_id
		) ); 

		if ( ! $row ) {
			return null;
		}

		return $this->create_team_from_table_row( $row );
	}

	/***************************************************************************************************************************
	 * Results
	 ***************************************************************************************************************************/

	public function get_tournament_results( $tournament_id, $stage = '', $result_type = '', $tournament_round = 0) {
		$binds = array($tournament_id);
		$sql_stage = '';
		$sql_result_type = '';
		$sql_tournament_round = '';
		if ( $stage ) {
			$sql_stage = ' AND stage = %s ';
			$binds[] = $stage;
		}
		if ( $result_type ) {
			$sql_result_type = ' AND result_type = %s ';
			$binds[] = $result_type;
		}
		if ( $tournament_round > 0 ) {
			$sql_tournament_round = ' AND tournament_round = %d ';
			$binds[] = $tournament_round;
		}
		
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT result_id, tournament_id, team1_id, team2_id, team1_placeholder, team2_placeholder, team1_score, team2_score, stage, pitch, tournament_round, result_type, is_virtual_result
			FROM   {$wpdb->prefix}ekc_result
			WHERE  tournament_id = %d
                   {$sql_stage}{$sql_result_type}{$sql_tournament_round}
			",
			$binds
		));

		$tournament_results = array();
		foreach ( $results as $row ) {
			$tournament_results[] = $this->create_result_from_table_row( $row );
		}
		return $tournament_results;
	}

	public function get_tournament_result_by_id( $result_id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 
			"
			SELECT result_id, tournament_id, team1_id, team2_id, team1_placeholder, team2_placeholder, team1_score, team2_score, stage, pitch, tournament_round, result_type, is_virtual_result
			FROM   {$wpdb->prefix}ekc_result
			WHERE  result_id = %d
			",
			$result_id
		) ); 
	
		if ( ! $row ) {
			return null;
		} 
		return $this->create_result_from_table_row( $row );
	}

	private function create_result_from_table_row( $row ) {
		$tournament_result = new Ekc_Result();
		$tournament_result->set_result_id( $row->result_id );
		$tournament_result->set_tournament_id( $row->tournament_id );
		$tournament_result->set_team1_id( $row->team1_id );
		$tournament_result->set_team2_id( $row->team2_id );
		$tournament_result->set_team1_placeholder( strval( $row->team1_placeholder ) );
		$tournament_result->set_team2_placeholder( strval( $row->team2_placeholder ) );
		$tournament_result->set_team1_score( $row->team1_score );
		$tournament_result->set_team2_score( $row->team2_score );
		$tournament_result->set_pitch( strval( $row->pitch ) );
		$tournament_result->set_stage( strval( $row->stage ) );
		$tournament_result->set_tournament_round( $row->tournament_round );
		$tournament_result->set_result_type( strval( $row->result_type ) );
		$tournament_result->set_virtual_result( boolval( $row->is_virtual_result ) );
		return $tournament_result;
	}

	public function insert_or_update_tournament_result( $tournament_result ) {
		if ( $tournament_result->get_result_id() ) {
			return $this->update_tournament_result( $tournament_result );
		}
		else {
			return $this->insert_tournament_result( $tournament_result );
		}
	}

	public function insert_tournament_result( $tournament_result ) {
		global $wpdb;
		$wpdb->insert( 
			$wpdb->prefix . 'ekc_result', 
			array( 
				'tournament_id'	=> $tournament_result->get_tournament_id(),
				'pitch'			=> $this->truncate_string( $tournament_result->get_pitch(), 20 ),
				'team1_id'		=> $tournament_result->get_team1_id(),
				'team1_placeholder'	=> $this->truncate_string( $tournament_result->get_team1_placeholder(), 500 ),
				'team1_score'		=> $tournament_result->get_team1_score(),
				'team2_id'			=> $tournament_result->get_team2_id(),
				'team2_placeholder'	=> $this->truncate_string( $tournament_result->get_team2_placeholder(), 500 ),
				'team2_score'		=> $tournament_result->get_team2_score(),
				'stage'				=> $this->truncate_string( $tournament_result->get_stage(), 20 ),
				'result_type'		=> $this->truncate_string( $tournament_result->get_result_type(), 20 ),
				'is_virtual_result'	=> intval( $tournament_result->is_virtual_result() ),
				'tournament_round'	=> $tournament_result->get_tournament_round(),
			), 
			array( '%d', '%s', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%d', '%d' ) 
		);

		return $wpdb->insert_id;
	}

	public function update_tournament_result( $tournament_result ) {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix . 'ekc_result', 
			array( 
				'tournament_id'	=> $tournament_result->get_tournament_id(),
				'pitch'			=> $this->truncate_string( $tournament_result->get_pitch(), 20 ),
				'team1_id'		=> $tournament_result->get_team1_id(),
				'team1_placeholder'	=> $this->truncate_string( $tournament_result->get_team1_placeholder(), 500 ),
				'team1_score'		=> $tournament_result->get_team1_score(),
				'team2_id'			=> $tournament_result->get_team2_id(),
				'team2_placeholder'	=> $this->truncate_string( $tournament_result->get_team2_placeholder(), 500 ),
				'team2_score'		=> $tournament_result->get_team2_score(),
				'stage'				=> $this->truncate_string( $tournament_result->get_stage(), 20 ),
				'result_type'		=> $this->truncate_string( $tournament_result->get_result_type(), 20 ),
				'is_virtual_result'	=> intval( $tournament_result->is_virtual_result() ),
				'tournament_round'	=> $tournament_result->get_tournament_round(),
			),
			array( 'result_id'		=> $tournament_result->get_result_id() ),
			array( '%d', '%s', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%d', '%d' ),
			array( '%d' )
		);

		return $tournament_result->get_result_id();
	}

	public function delete_results_for_team( $team_id ) {
		if ( ! $team_id ) {
			return;
		}

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ekc_result', array( 'team1_id' => $team_id ) );
		$wpdb->delete( $wpdb->prefix . 'ekc_result', array( 'team2_id' => $team_id ) );
	}

	public function delete_results_for_round( $tournament_id, $tournament_round ) {
		if ( ! $tournament_id || ! $tournament_round ) {
			return;
		}

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ekc_result', array(
			'tournament_id' => $tournament_id,
			'tournament_round' => $tournament_round
			 ) );
	}

	/***************************************************************************************************************************
	 * Swiss System
	 ***************************************************************************************************************************/

	public function get_current_swiss_system_round( $tournament_id ) {
		global $wpdb;

		$current_round = $wpdb->get_var( $wpdb->prepare( 
			"
			SELECT MAX(tournament_round)
			FROM   {$wpdb->prefix}ekc_result
			WHERE  tournament_id = %d
			AND    stage = %s
			",
			$tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS
		));
		
		return intval( $current_round );
	}

	public function get_current_swiss_system_ranking( $tournament_id ) {
		global $wpdb;

		// use temporary tables for performance (and as mysql does not support common table expressions)
		// temporary table 1: score per team
		$wpdb->query( $wpdb->prepare( 
			"
			CREATE TEMPORARY TABLE {$wpdb->prefix}ekc_temp_score1
			SELECT x.team_id team_id, SUM(x.score) score
			FROM (
			  SELECT team1_id AS team_id, team1_score AS score
			  FROM   {$wpdb->prefix}ekc_result WHERE tournament_id = %d and stage = %s
			  UNION ALL
			  SELECT team2_id AS team_id, team2_score AS score
			  FROM   {$wpdb->prefix}ekc_result WHERE tournament_id = %d and stage = %s) x
			GROUP BY x.team_id
			",
			$tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, 
			$tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS
		));

		// temporary table 2: score per team
		// this is a copy of the first temp table (because mysql cannot use a temporary table twice in a single statement)
		$wpdb->query(
			"
			CREATE TEMPORARY TABLE {$wpdb->prefix}ekc_temp_score2
  			SELECT * FROM {$wpdb->prefix}ekc_temp_score1
			"
		);

		// temporary table 3: opponent score per team
		$wpdb->query( $wpdb->prepare( 
			"
			CREATE TEMPORARY TABLE {$wpdb->prefix}ekc_temp_opponent_score
			SELECT x.team_id team_id, SUM(x.opp_score) opponent_score
			FROM (
			  SELECT rx.team1_id team_id, s.score opp_score
			  FROM   {$wpdb->prefix}ekc_result rx
			  JOIN   {$wpdb->prefix}ekc_temp_score1 s on s.team_id = rx.team2_id
			  WHERE  rx.tournament_id = %d and rx.stage = %s
			  UNION ALL
			  SELECT rx.team2_id team_id, s.score opp_score
			  FROM   {$wpdb->prefix}ekc_result rx
			  JOIN   {$wpdb->prefix}ekc_temp_score2 s on s.team_id = rx.team1_id
			  WHERE  rx.tournament_id = %d and rx.stage = %s) x
			GROUP BY x.team_id
			",
			$tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, 
			$tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS
		));

		// finally select result using our temp tables
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT	t.team_id AS team_id, 
        			t.seeding_score AS seeding_score,
        			s.score as score,
					o.opponent_score as opponent_score,
					t.initial_score as initial_score,
					t.virtual_rank as virtual_rank
			FROM    {$wpdb->prefix}ekc_team t
			JOIN    {$wpdb->prefix}ekc_temp_score1 s on s.team_id = t.team_id
			JOIN    {$wpdb->prefix}ekc_temp_opponent_score o on o.team_id = t.team_id
			WHERE   t.tournament_id = %d
			AND     t.is_active = 1
			ORDER BY -virtual_rank desc, (coalesce(score,0) + coalesce(initial_score,0)) desc, coalesce(opponent_score,0) desc, coalesce(seeding_score,0) desc
			",
			$tournament_id
		));

		// drop temporary tables (needed if multiple ranking tables are shown on the same page)
		$wpdb->query( "DROP TEMPORARY TABLE {$wpdb->prefix}ekc_temp_score1" );
		$wpdb->query( "DROP TEMPORARY TABLE {$wpdb->prefix}ekc_temp_score2" );
		$wpdb->query( "DROP TEMPORARY TABLE {$wpdb->prefix}ekc_temp_opponent_score" );

		$teams = array();
		foreach ( $results as $row ) {
			$teams[] = $this->create_swiss_system_team_from_table_row( $row );
		}
		return $teams;
	}

	public function create_swiss_system_team_from_table_row( $row ) {
		$team = new Ekc_Swiss_System_Team();
		$team->set_team_id( $row->team_id );
		$team->set_score( $row->score );
		$team->set_opponent_score( $row->opponent_score );
		$team->set_seeding_score( $row->seeding_score );
		$team->set_initial_score( $row->initial_score );
		$team->set_virtual_rank( $row->virtual_rank );
		return $team;
	}

	public function get_initial_swiss_system_ranking( $tournament_id ) {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT t.team_id AS team_id, 0 AS score, 0 AS opponent_score, t.seeding_score AS seeding_score, t.initial_score as initial_score, t.virtual_rank as virtual_rank
			FROM   {$wpdb->prefix}ekc_team t
			WHERE t.tournament_id = %d
			AND   t.is_active = 1
			ORDER BY -t.virtual_rank desc, coalesce(t.initial_score, 0) desc, coalesce(t.seeding_score, 0) desc, t.team_id asc
			",
			$tournament_id
		));

		$teams = array();
		foreach ( $results as $row ) {
			$teams[] = $this->create_swiss_system_team_from_table_row( $row );
		}
		return $teams;
	}


	// --------------------------------------------------------------------------------- //
	// Private helper functions
	// ----------------------------------------------------------------------------------//

	private function truncate_string( $string, $length ) {
		return strlen( $string ) > $length ? substr( $string, 0, $length ) : $string;
	}
}
