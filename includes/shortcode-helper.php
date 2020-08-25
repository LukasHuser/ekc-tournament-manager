<?php

/**
 * Shortcodes to provide data to the front end
 */
class Ekc_Shortcode_Helper {

	public function add_ekc_shortcodes() {
		add_shortcode( 'ekc-teams', array( $this, 'shortcode_registered_teams' ) );
		add_shortcode( 'ekc-team-count', array( $this, 'shortcode_registered_teams_count' ) );
		add_shortcode( 'ekc-elimination-bracket', array ( $this, 'shortcode_elimination_bracket' ) );
		add_shortcode( 'ekc-swiss-system', array ( $this, 'shortcode_swiss_system' ) );
		add_shortcode( 'ekc-link', array( $this, 'shortcode_shareable_link') );
	}

	public function shortcode_registered_teams_count( $atts ) {
		$atts = shortcode_atts(
			array(
				'tournament' => '',
				'max' => 'false',
				'raw-number' => 'false'
			),
			$atts,
			'ekc-team-count'
		);
		$tournament_code_name = $atts['tournament'];
		$is_max_teams = filter_var( $atts['max'], FILTER_VALIDATE_BOOLEAN );
		$is_raw_number = filter_var( $atts['raw-number'], FILTER_VALIDATE_BOOLEAN );

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		
		$db = new Ekc_Database_Access();

		if ( $is_max_teams ) {
			$count = $db->get_max_teams_by_code_name($tournament_code_name);
			if ( $is_raw_number ) {
				return $count;
			}
			return '<span class="ekc-max-team-count">' . $count . '</span>';
		}
		else {
			$count = $db->get_active_teams_count_by_code_name($tournament_code_name);
			if ( $is_raw_number ) {
				return $count;
			}
			return '<span class="ekc-team-count">' . $count . '</span>';
		}
	}

	public function shortcode_registered_teams( $atts ) {
		$atts = shortcode_atts(
			array(
				'tournament' => '',
				'limit' => 'all',
				'sort' => 'asc',
				'waitlist' => 'false',
				'country' => 'true',
			),
			$atts,
			'ekc-teams'
		);
		$tournament_code_name = $atts['tournament'];
		$limit = $atts['limit'] === 'all' ? 0 : intval( $atts['limit'] );
		$sort = $atts['sort'];
		$is_wait_list =  filter_var( $atts['waitlist'], FILTER_VALIDATE_BOOLEAN );
		$show_country = filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		if ( $sort !== 'asc' ) {
			$sort = 'desc';
		}
		
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name($tournament_code_name);
		if ( ! $tournament ) {
			return '';
		}

		$is_sort_desc = $sort === 'desc';

		if ( $is_wait_list ) {
			$teams = $db->get_active_teams_on_wait_list($tournament->get_tournament_id(), $sort);
			if ( ! is_array($teams) ) {
				return '';
			}

			$c = $db->get_max_teams_by_tournament_id( $tournament->get_tournament_id() ) + 1;
			if ( $is_sort_desc ) {
				$c += count( $teams );
			}
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc, $show_country );
		}
		else {
			$teams = $db->get_active_teams($tournament->get_tournament_id(), $limit, $sort);

			$c = 1;
			if ( $is_sort_desc ) {
				$c = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			}
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc, $show_country );
		}
	}

	private function create_teams_table( $teams, $tournament, $counter, $is_sort_desc, $show_country ) {
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$header = array();
		$header[] = array('<span class="dashicons dashicons-arrow-down-alt"></span>', 'ekc-column-no');
		if ( $show_country ) {
			$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		}
		if ( ! $is_single_player ) {
			$header[] = array('Team', 'ekc-column-team');
		}
		if ( $tournament->is_player_names_required() || $is_single_player ) {	
			$header[] = array($is_single_player ? 'Player' : 'Players', 'ekc-column-player');
		}
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $teams as $team ) {
			$row = array();
			$row[] = $counter;
			if ( $show_country ) {
				$row[] = $this->html_flag( esc_html($team->get_country()) );
			}
			if ( ! $is_single_player || ! $tournament->is_player_names_required() ) {
				$row[] = esc_html($team->get_name());
			}			
			if ( $tournament->is_player_names_required() ) {
				$first = true;
				$row_content = '';	
				for ($i = 0; $i < 6; $i++) {
					$player = $team->get_player($i);
					if ( ! $player ) {
						break;					
					}
					$row_content .= ( ( ! $first ) ? '&nbsp;<span class="ekc-column-team-separator">//</span> ' : '' ) . esc_html($player->get_first_name()) . '&nbsp;' . esc_html($player->get_last_name());
					$first = false;
				}
				$row[] = $row_content;
			}
			$html_body .= $this->html_table_row( $row );
			if ( $is_sort_desc ) {
				$counter--;
			}
			else {			
				$counter++;
			}
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_table( $html_header . $html_body );
	}

	private function html_table( $inner ) {
		return '<table class="ekc-table ekc-team-table">' . $inner . '</table>';
	}

	private function html_table_head( $header_row ) {
		$result = '<colgroup class="ekc-colgroup">';
		foreach( $header_row as $header ) {
			$result .= '<col class="ekc-col ' . $header[1] . '" />';
		}
		$result .= '</colgroup>';
		$result .= '<thead class="ekc-thead"><tr class="ekc-tr">';
		foreach( $header_row as $header ) {
			$result .= '<th class="ekc-th">' . $header[0] . '</th>';
		}
		return $result . '</tr></thead>';
	}

	private function html_table_body( $inner ) {
		return '<tbody class="ekc-tbody">' . $inner . '</tbody>';
	}

	private function html_table_row( $row, $id = '', $is_excluded = false, $rowspan = '' ) {
		$id_part = $id ? ' id="' . esc_html( $id ) . '" ' : '';
		$excluded_part = $is_excluded ? ' ekc-excluded-team' : '';
		$result = '<tr' . $id_part .  ' class="ekc-tr' . $excluded_part . '">';
		$counter = 0;
		foreach( $row as $data ) {
			if ( $counter === 0) { // rowspan option is always for first column
				if ( $rowspan === 'rowspan' ) {
					$result .= '<td class="ekc-td" rowspan="2">' . $data . '</td>';
					$counter++;
					continue;
				}
				elseif ( $rowspan === 'rowspan-omit' ) {
					$counter++;
					continue;
				}
			}
			$result .= '<td class="ekc-td">' . $data . '</td>';
			$counter++;
		}
		return $result . '</tr>';
	}

	private function html_flag( $country_code ) {
		if ( $country_code ) {
			return '<span class="flag-icon flag-icon-' . $country_code . '"></span>';
		}
		return '<span></span>';
	}

	public function shortcode_elimination_bracket( $atts ) {
		// add refresh.js script when this shortcode is used
		wp_enqueue_script( 'ekc-refresh' ); 

		$atts = shortcode_atts(
			array(
				'tournament' => '',
				'country'    => 'true',
			),
			$atts,
			'ekc-elimination-bracket'
		);
		$tournament_code_name = $atts['tournament'];
		$show_country = filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( ! $tournament ) {
			return '';
		}
		$results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO, null, null );
		$elimination_bracket = $tournament->get_elimination_rounds();

		$html = '';
		if ( Ekc_Elimination_Bracket_Helper::has_1_16_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_round_of_32_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_8_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_round_of_16_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_4_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_quarterfinals_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_2_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_semifinals_div( $results, $show_country );
		}
		$html = $html . $this->bracket_finals_div( $results, $show_country );
		return $this->bracket_main_div( $this->elimination_bracket_to_css_class( $elimination_bracket ), $html );
	}

	private function bracket_main_div( $participants_css_class, $inner ) {
		return '<div class="tournament-bracket ' . $participants_css_class . '">' . $inner . '</div>';
	}

	private function bracket_finals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_final = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_1);
		$result_3rd = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_2);

		$html = $this->bracket_matchups( $db, $result_final, $result_3rd, $show_country, false );
		return $this->bracket_round( 'finals', 'Finals', $html );
	}

	private function bracket_semifinals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_semi_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_1);
		$result_semi_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_2);
		
		$html = $this->bracket_matchups( $db, $result_semi_1, $result_semi_2, $show_country, true );
		return $this->bracket_round( 'semifinals', 'Semifinals', $html );
	}

	private function bracket_quarterfinals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_quarter_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_1);
		$result_quarter_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_2);
		$result_quarter_3 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_3);
		$result_quarter_4 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_4);
		
		$html = $this->bracket_matchups( $db, $result_quarter_1, $result_quarter_2, $show_country, true )
		      . $this->bracket_matchups( $db, $result_quarter_3, $result_quarter_4, $show_country, true );
		return $this->bracket_round( 'quarterfinals', '1/4 Finals', $html );
	}

	private function bracket_round_of_16_div( $results ) {
		$db = new Ekc_Database_Access();
		
		$result_1_8_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_1);
		$result_1_8_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_2);
		$result_1_8_3 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_3);
		$result_1_8_4 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_4);
		$result_1_8_5 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_5);
		$result_1_8_6 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_6);
		$result_1_8_7 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_7);
		$result_1_8_8 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_8);
		
		$html = $this->bracket_matchups( $db, $result_1_8_1, $result_1_8_2, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_8_3, $result_1_8_4, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_8_5, $result_1_8_6, $show_country, true )
		      . $this->bracket_matchups( $db, $result_1_8_7, $result_1_8_8, $show_country, true );
		return $this->bracket_round( 'round-of-16', '1/8 Finals', $html );
	}

	private function bracket_round_of_32_div( $results ) {
		$db = new Ekc_Database_Access();

		$result_1_16_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_1);
		$result_1_16_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_2);
		$result_1_16_3 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_3);
		$result_1_16_4 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_4);
		$result_1_16_5 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_5);
		$result_1_16_6 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_6);
		$result_1_16_7 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_7);
		$result_1_16_8 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_8);
		$result_1_16_9 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_9);
		$result_1_16_10 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_10);
		$result_1_16_11 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_11);
		$result_1_16_12 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_12);
		$result_1_16_13 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_13);
		$result_1_16_14 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_14);
		$result_1_16_15 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_15);
		$result_1_16_16 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_16);
		
		$html = $this->bracket_matchups( $db, $result_1_16_1, $result_1_16_2, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_16_3, $result_1_16_4, $show_country, true )
		      . $this->bracket_matchups( $db, $result_1_16_5, $result_1_16_6, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_16_7, $result_1_16_8, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_16_9, $result_1_16_10, $show_country, true )
			  . $this->bracket_matchups( $db, $result_1_16_11, $result_1_16_12, $show_country, true )
		      . $this->bracket_matchups( $db, $result_1_16_13, $result_1_16_14, $show_country, true )
		      . $this->bracket_matchups( $db, $result_1_16_15, $result_1_16_16, $show_country, true );
  		return $this->bracket_round( 'round-of-32', '1/16 Finals', $html );
	}

	private function bracket_round( $round_css_class, $round_label, $inner_html ) {
		return
		'<div class="round ' . $round_css_class . '">
		<span class="round-label">' . $round_label . '</span>
		' . $inner_html . '
		</div>';
	}

	private function bracket_matchups( $db, $result1, $result2, $show_country, $connector = true ) {
		$html_connector = $connector ? $this->bracket_connector() : '';

		return
		'<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			' . $this->bracket_participants( $db, $result1, $show_country ) . '
			</div>
			<div class="matchup">
			' . $this->bracket_participants( $db, $result2, $show_country ) . '
			</div>
		  </div>
		  ' . $html_connector  . '
	    </div>';
	}

	private function bracket_participants( $db, $result, $show_country ) {
		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result ) {
			$team1 = $db->get_team_by_id ( $result->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result ) {
			$team2 = $db->get_team_by_id ( $result->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result->get_team2_score();
			}	
		}

		return
		'<div class="participants">
		  <div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '">
		    <span class="flag-icon flag-icon-' . $team1_country . $this->css_class_hidden( $show_country ) . '"></span>
		    <span class="label">' . $team1_name . '</span>
		    <span class="score"> ' . $team1_score . '</span>
		  </div>
		  <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '">
		    <span class="flag-icon flag-icon-' . $team2_country . $this->css_class_hidden( $show_country ) . '"></span>
		    <span class="label">' . $team2_name . '</span>
		    <span class="score"> ' . $team2_score . '</span>
		  </div>
	    </div>';
	}

	private function bracket_connector() {
		return
		'<div class="connector">
		  <div class="merger"></div>
		  <div class="line"></div>
	    </div>';
	}

	private function css_class_hidden( $is_visible ) {
		if ( $is_visible ) {
			return '';
		}
		return ' ekc-hidden ';
	}

	private function get_name_or_placeholder( $placeholder, $team ){
		if ( $placeholder) {
			return $placeholder;
		}
		if ( $team ) {
			return $team->get_name();
		}
	}

	private function elimination_bracket_to_css_class ( $elimination_bracket ) {
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16) {
			return 'participants-32';
		}
		elseif ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8) {
			return 'participants-16';
		}
		elseif ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4) {
			return 'participants-8';
		}
		elseif ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2) {
			return 'participants-4';
		}
	}

	public function shortcode_swiss_system( $atts ) {
		// add refresh.js script when this shortcode is used
		wp_enqueue_script( 'ekc-refresh' ); 

		$atts = shortcode_atts(
			array(
				'tournament' => '',
				'ranking' => 'false',
				'rounds' => '2',
				'timer' => 'false',
				'country' => 'true',
			),
			$atts,
			'ekc-swiss-system'
		);
		$tournament_code_name = $atts['tournament'];
		$is_show_ranking =  filter_var( $atts['ranking'], FILTER_VALIDATE_BOOLEAN );
		$is_show_country =  filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );
		$is_show_timer =  filter_var( $atts['timer'], FILTER_VALIDATE_BOOLEAN );
		$max_rounds = filter_var( $atts['rounds'], FILTER_VALIDATE_INT );
		if ( $atts['rounds'] === 'all' ) {
			$max_rounds = 1000;
		}

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( ! $tournament ) {
			return '';
		}
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();

		if ( $is_show_timer ) {
			return $this->show_timer( $tournament );
		}
		elseif ( $is_show_ranking ) {
			return $this->create_swiss_ranking_table( $tournament, $is_show_country );
		}
		return $this->create_swiss_rounds( $tournament, $max_rounds, $is_show_country );
	}

	private function create_swiss_rounds( $tournament, $max_rounds, $show_country ) {
		$db = new Ekc_Database_Access();
		$html = '';
		$all_results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, null );
		$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );

		if ( $max_rounds > $current_round ) {
			$max_rounds = $current_round;
		}
		$round_limit = $current_round - $max_rounds;
		for ( $round = $current_round; $round > $round_limit; $round-- ) {
			$results_for_round = $this->get_results_for_round( $all_results, $round );
			if (count( $results_for_round ) > 0) {
				$html .= '<h3>Round ' . $round  . '</h3>';
				$html .= $this->create_swiss_round_table( $tournament, $results_for_round, $round, $show_country );
			}
		}
		return $html;
	}

	private function create_swiss_ranking_table( $tournament, $show_country ) {
		$db = new Ekc_Database_Access();
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$counter = 1;
		$current_ranking = $db->get_current_swiss_system_ranking( $tournament->get_tournament_id() );
		
		$header = array();
		$header[] = array('<span class="dashicons dashicons-awards"></span>', 'ekc-column-rank');
		if ( $show_country ) {
			$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		}
		$header[] = array($is_single_player ? 'Player' : 'Team', 'ekc-column-team');
		$header[] = array('Score', 'ekc-column-score');
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $current_ranking as $ranking ) {
			$team = $db->get_team_by_id( $ranking->get_team_id() );
			$is_excluded = intval( $team->get_virtual_rank() ) !== 0;
			$row = array();
			$row[] = $counter;
			if ( $show_country ) {
				$row[] = $this->html_flag( esc_html($team->get_country()) );
			}
			$row[] = esc_html($team->get_name());
			$row[] = strval( $ranking->get_total_score() ) . '&nbsp;/&nbsp;' . strval( $ranking->get_opponent_score() );
			$html_body .= $this->html_table_row( $row, 'rank-' . $counter, $is_excluded );
			$counter++;
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_table( $html_header . $html_body );
	}

	private function create_swiss_round_table( $tournament, $results_for_round, $round, $show_country, $score_as_input = false ) {
		$db = new Ekc_Database_Access();
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$is_additional_round = $round > $tournament->get_swiss_system_rounds();
		$max_points_per_round = $tournament->get_swiss_system_max_points_per_round();

		$header = array();
		$header[] = array('Pitch', 'ekc-column-pitch');
		if ( $show_country ) {
			$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		}
		$header[] = array($is_single_player ? 'Player' : 'Team', 'ekc-column-team');
		$header[] = array('Score', 'ekc-column-score');
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $results_for_round as $result ) {
			$team1 = $db->get_team_by_id( $result->get_team1_id() );
			$team2 = $db->get_team_by_id( $result->get_team2_id() );

			if ( $is_additional_round && $team1 && $team2 ) {
				$is_team1_excluded = intval( $team1->get_virtual_rank() ) !== 0;
				$is_team2_excluded = intval( $team2->get_virtual_rank() ) !== 0;
				if ( $is_team1_excluded && $is_team2_excluded ) {
					continue;
				}
			}

			// row for team1
			$row = array();
			$row[] = $result->get_pitch();
			$team1_country = '';
			if ($team1) {
				$team1_country = $this->html_flag( esc_html($team1->get_country()) );
			}
			if ( $show_country ) {
				$row[] = $team1_country;
			}
			$team1_name = '';
			if ($team1) {
				$team1_name = $team1->get_name();
			}
			$row[] = $team1_name;
			$team1_score = '';
			if ( ! is_null( $result->get_team1_score() ) ) {
				$team1_score = $result->get_team1_score() == 0 ? '0' : strval( $result->get_team1_score() );
			}
			if ( $score_as_input ) {
				$team1_score = $this->html_score_input( $team1_score, 'team1-score-' . $result->get_result_id(), $max_points_per_round );
			}
			$row[] = $team1_score;
			$html_body .= $this->html_table_row( $row, 'pitch-' . $result->get_pitch(), false, 'rowspan' );

			// row for team2
			$row = array();
			$row[] = $result->get_pitch();
			$team2_country = '';
			if ($team2) {
				$team2_country = $this->html_flag( esc_html($team2->get_country()) );
			}
			if ( $show_country ) {
				$row[] = $team2_country;
			}
			$team2_name = '';
			if ($team2) {
				$team2_name = $team2->get_name();
			}
			$row[] = $team2_name;
			$team2_score = '';
			if ( ! is_null( $result->get_team2_score() ) ) {
				$team2_score = $result->get_team2_score() == 0 ? '0' :  strval( $result->get_team2_score() );
			}
			if ( $score_as_input ) {
				$team2_score = $this->html_score_input( $team2_score, 'team2-score-' . $result->get_result_id(), $max_points_per_round );
			}
			$row[] = $team2_score;
			$html_body .= $this->html_table_row( $row, '', false, 'rowspan-omit' );
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_table( $html_header . $html_body );
	}

	private function html_score_input( $score_value, $html_id, $max_points_per_round ) {
		return '<input id="' . $html_id . '" name="' . $html_id . '" type="number" step="any" min="0" max="' . $max_points_per_round . '" value="' . $score_value . '" />';
	}

	private function get_results_for_round( $results, $tournament_round, $team_id = null ) {
		$filtered_results = array();
		foreach ( $results as $result ) {
		  if ( strval( $result->get_tournament_round() ) === strval( $tournament_round ) ) {
			if ( $team_id ) {
				if ( intval( $result->get_team1_id() ) === intval( $team_id ) 
				  || intval( $result->get_team2_id() ) === intval( $team_id ) ) {
					$filtered_results[] = $result;
				  }
			}
			else {
				$filtered_results[] = $result;
			}
		  }
		}
		return $filtered_results;
	  }

	  private function show_timer( $tournament ) {
		$db = new Ekc_Database_Access();
		$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );
		$round_start_time = $db->get_tournament_round_start( $tournament->get_tournament_id(), $current_round );

		if ( $round_start_time ) {
		  $round_end_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
		  $round_end_date->add(new DateInterval('PT' . $tournament->get_swiss_system_round_time() . 'M')); // add minutes
		  $now = new DateTime();
		  $time_left = '0';
		  if ( $round_end_date > $now ) {
			$time_left = $now->diff( $round_end_date )->format('%i');
		  }
		  return 'Round '. $current_round . ': ' . $time_left . ' minutes left.';
		}

		if ( $current_round > 0 && $tournament->get_swiss_system_round_time() ) {
			return 'Round ' . $current_round . ' not started yet.';
		}
		return '';
	}

	public function shortcode_shareable_link( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => 'team-results',
				'country' => 'true',
			),
			$atts,
			'ekc-link'
		);

		$link_id = ( isset($_GET['linkid'] ) ) ? sanitize_text_field( wp_unslash( $_GET['linkid'] ) ) : '';
		$page_id = ( isset($_GET['page_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page_id'] ) ) : '';
		
		$url_path = '?'. ($page_id ? 'page_id=' . $page_id . '&' : '') . 'linkid=' . $link_id;

		$db = new Ekc_Database_Access();
		$team = $db->get_team_by_shareable_link_id( $link_id );

		$show_country =  filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );

		if ( $atts['type'] === 'team-name') {
			if ( $team ) {
				return $team->get_name();
			}
			return '';
		}
		if ( $atts['type'] === 'timer') {
			if ( $team ) {
				$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );
				return $this->show_timer( $tournament );
			}
			return '';
		}
		// else: type == team-results

		if ( ! $team ) {
			return 'No data found.';

		}

		$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );
		$all_results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, null );
		$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );
		$current_round_result = $this->get_results_for_round( $all_results, $current_round, $team->get_team_id() );
		
		$html = '<p><a href="' . $_SERVER['REQUEST_URI'] . '">Reload page</a></p>';
		if (count( $current_round_result ) > 0) {
			$html .= '<h3>Round ' . $current_round  . '</h3>';
			$html .= $this->create_current_round_result( $tournament, $current_round_result, $current_round, $show_country, $url_path );
		}	

		for ( $round = $current_round - 1; $round > 0; $round-- ) {
			$result_for_round = $this->get_results_for_round( $all_results, $round, $team->get_team_id() );
			if (count( $result_for_round ) > 0) {
				$html .= '<h3>Round ' . $round  . '</h3>';
				$html .= $this->create_swiss_round_table( $tournament, $result_for_round, $round, $show_country );
			}
		}
		return $html;
	}

	private function create_current_round_result( $tournament, $current_round_result, $current_round, $show_country, $url_path ) {
		$html = '<form class="ekc-form" method="post" action="' . $url_path . '" accept-charset="utf-8">';
		$html .= $this->create_swiss_round_table( $tournament, $current_round_result, $current_round, $show_country, true );
		$html .= '<div class="ekc-controls">';
		$html .= '<button type="submit" class="ekc-button ekc-button-primary">Save result for round ' . $current_round . '</button>';
		$html .= '<input id="action" name="action" type="hidden" value="storeresult" />';
		$html .= '</div>';
		$html .= '</form>';
		return $html;
	}
	
	public function shortcode_shareable_link_handle_post() {
		$link_id = ( isset( $_GET['linkid'] ) ) ? sanitize_text_field( wp_unslash( $_GET['linkid'] ) ) : '';
		
		$db = new Ekc_Database_Access();
		$team = $db->get_team_by_shareable_link_id( $link_id );
		
		if ( $team ) {
			$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );
			$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );
			$current_round_results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, $current_round );
			
			$current_result = $this->get_results_for_round( $current_round_results, $current_round, $team->get_team_id() );
			
			$this->store_result( $current_result[0] );
		}
	}
		
	private function store_result( $existing_result ) {
		$team1_score_id = 'team1-score-' . $existing_result->get_result_id();
		$team2_score_id = 'team2-score-' . $existing_result->get_result_id();
		if ( isset( $_POST[ $team1_score_id ] ) ) {
			$existing_result->set_team1_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST[ $team1_score_id ] ) ) ) );
		}
		if ( isset( $_POST[ $team2_score_id ] ) ) {
			$existing_result->set_team2_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST[ $team2_score_id ] ) ) ) );
		}
		$db = new Ekc_Database_Access();
		$db->update_tournament_result( $existing_result );
	}
}
