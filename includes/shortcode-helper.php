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
		add_shortcode( 'ekc-nation-trophy', array( $this, 'shortcode_nation_trophy' ) );
	}

	/***************************************************************************************************************************
	 * Teams
	 ***************************************************************************************************************************/

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
			return '<span class="ekc-max-team-count">' . esc_html( $count ) . '</span>';
		}
		else {
			$count = $db->get_active_teams_count_by_code_name($tournament_code_name);
			if ( $is_raw_number ) {
				return $count;
			}
			return '<span class="ekc-team-count">' . esc_html( $count ) . '</span>';
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
				'club' => 'false',
				'registration-fee' => 'false',
			),
			$atts,
			'ekc-teams'
		);
		$tournament_code_name = $atts['tournament'];
		$limit = $atts['limit'] === 'all' ? 0 : intval( $atts['limit'] );
		$sort = $atts['sort'];
		$is_wait_list =  filter_var( $atts['waitlist'], FILTER_VALIDATE_BOOLEAN );
		$show_country = filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );
		$show_club = filter_var( $atts['club'], FILTER_VALIDATE_BOOLEAN );
		$show_registration_fee = filter_var( $atts['registration-fee'], FILTER_VALIDATE_BOOLEAN );

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
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc, $show_country, $show_club, $show_registration_fee );
		}
		else {
			$teams = $db->get_active_teams($tournament->get_tournament_id(), $limit, $sort);

			$c = 1;
			if ( $is_sort_desc ) {
				$c = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			}
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc, $show_country, $show_club, $show_registration_fee );
		}
	}

	private function create_teams_table( $teams, $tournament, $counter, $is_sort_desc, $show_country, $show_club, $show_registration_fee ) {
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$header = array();
		$header[] = array('<span class="dashicons dashicons-arrow-down-alt"></span>', 'ekc-column-no');
		if ( $show_country ) {
			$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		}
		if ( $is_single_player ) {
			$header[] = array( esc_html__( 'Player', 'ekc-tournament-manager' ), 'ekc-column-player');
		}
		else {
			$header[] = array( esc_html__( 'Team', 'ekc-tournament-manager' ), 'ekc-column-team');
		}
		if ( $show_club ) {
			$header[] = array( esc_html__( 'Club / City', 'ekc-tournament-manager' ), 'ekc-column-club');
		}
		if ( ! $is_single_player && $tournament->is_player_names_required() ) {	
			$header[] = array( esc_html__( 'Players', 'ekc-tournament-manager' ), 'ekc-column-players');
		}
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $teams as $team ) {
			$row = array();
			$row[] = $counter;
			if ( $show_country ) {
				$row[] = $this->html_flag( $team->get_country() );
			}
			$row[] = $this->html_team( $team, $show_registration_fee );
			if ( $show_club ) {
				$row[] = esc_html($team->get_club());
			}			
			if ( ! $is_single_player && $tournament->is_player_names_required() ) {
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
			$html_body .= $this->html_team_table_row( $row );
			if ( $is_sort_desc ) {
				$counter--;
			}
			else {			
				$counter++;
			}
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_team_table( $html_header . $html_body );
	}

	private function html_team_table( $inner ) {
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

	private function html_team_table_row( $row, $id = '', $is_excluded = false, $rowspan = '' ) {
		$id_part = $id ? ' id="' . esc_attr( $id ) . '" ' : '';
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
			return '<span class="flag-icon flag-icon-' . esc_attr( $country_code ) . '"></span>';
		}
		return '<span></span>';
	}

	private function html_team( $team, $show_registration_fee = false ) {
		return '<span class="' . ( $show_registration_fee && $team->is_registration_fee_paid() ? 'ekc-registration-fee' : '' ) . '">' . esc_html($team->get_name()) . '</span>';
	}
 
	/***************************************************************************************************************************
	 * Elimination Bracket
	 ***************************************************************************************************************************/

	public function shortcode_elimination_bracket( $atts ) {
		// add refresh.js script when this shortcode is used
		wp_enqueue_script( 'ekc-refresh' ); 

		$atts = shortcode_atts(
			array(
				'tournament' => '',
				'bracket'    => Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_GOLD,
				'country'    => 'true',
			),
			$atts,
			'ekc-elimination-bracket'
		);
		$tournament_code_name = $atts['tournament'];
		$bracket_type = $atts['bracket'];
		$show_country = filter_var( $atts['country'], FILTER_VALIDATE_BOOLEAN );

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( ! $tournament ) {
			return '';
		}

        $stage = Ekc_Elimination_Bracket_Helper::get_stage_for_bracket_type( $bracket_type );
		$elimination_rounds = Ekc_Elimination_Bracket_Helper::get_elimination_rounds_for_bracket_type( $tournament, $bracket_type );
		$results = $db->get_tournament_results( $tournament->get_tournament_id(), $stage, null, null );

		$html = '';
		if ( Ekc_Elimination_Bracket_Helper::has_1_16_finals( $elimination_rounds ) ) {
			$html = $html . $this->bracket_round_of_32_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_8_finals( $elimination_rounds ) ) {
			$html = $html . $this->bracket_round_of_16_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_4_finals( $elimination_rounds ) ) {
			$html = $html . $this->bracket_quarterfinals_div( $results, $show_country );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_2_finals( $elimination_rounds ) ) {
			$html = $html . $this->bracket_semifinals_div( $results, $show_country );
		}
		$html = $html . $this->bracket_finals_div( $results, $show_country );
		return $this->bracket_main_div( $this->elimination_bracket_to_css_class( $elimination_rounds ), $html );
	}

	private function bracket_main_div( $participants_css_class, $inner ) {
		return '<div class="tournament-bracket ' . $participants_css_class . '">' . $inner . '</div>';
	}

	private function bracket_finals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_final = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_1);
		$result_3rd = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_2);

		$html = $this->bracket_matchups( $db, $result_final, $result_3rd, $show_country, false );
		return $this->bracket_round( 'finals', __( 'Finals', 'ekc-tournament-manager' ), $html );
	}

	private function bracket_semifinals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_semi_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_1);
		$result_semi_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_2);
		
		$html = $this->bracket_matchups( $db, $result_semi_1, $result_semi_2, $show_country, true );
		return $this->bracket_round( 'semifinals', __( 'Semifinals', 'ekc-tournament-manager' ), $html );
	}

	private function bracket_quarterfinals_div( $results, $show_country ) {
		$db = new Ekc_Database_Access();
		$result_quarter_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_1);
		$result_quarter_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_2);
		$result_quarter_3 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_3);
		$result_quarter_4 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_4);
		
		$html = $this->bracket_matchups( $db, $result_quarter_1, $result_quarter_2, $show_country, true )
		      . $this->bracket_matchups( $db, $result_quarter_3, $result_quarter_4, $show_country, true );
		return $this->bracket_round( 'quarterfinals', __( '1/4 Finals', 'ekc-tournament-manager' ), $html );
	}

	private function bracket_round_of_16_div( $results, $show_country ) {
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
		return $this->bracket_round( 'round-of-16', __( '1/8 Finals', 'ekc-tournament-manager' ), $html );
	}

	private function bracket_round_of_32_div( $results, $show_country ) {
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
  		return $this->bracket_round( 'round-of-32', __( '1/16 Finals', 'ekc-tournament-manager' ), $html );
	}

	private function bracket_round( $round_css_class, $round_label, $inner_html ) {
		return
		'<div class="round ' . $round_css_class . '">
		<span class="round-label">' . esc_html( $round_label ) . '</span>
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
		    <span class="flag-icon flag-icon-' . esc_attr( $team1_country ) . $this->css_class_hidden( $show_country ) . '"></span>
		    <span class="label">' . esc_html( $team1_name ) . '</span>
		    <span class="score"> ' . esc_html( $team1_score ) . '</span>
		  </div>
		  <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '">
		    <span class="flag-icon flag-icon-' . esc_attr( $team2_country ) . $this->css_class_hidden( $show_country ) . '"></span>
		    <span class="label">' . esc_html( $team2_name ) . '</span>
		    <span class="score"> ' . esc_html( $team2_score ) . '</span>
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

	/***************************************************************************************************************************
	 * Swiss System
	 ***************************************************************************************************************************/

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
				$html .= '<h3>' . sprintf( /* translators: %s: tournament round number */ esc_html__( 'Round %s', 'ekc-tournament-manager' ), $round ) . '</h3>';
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
		$header[] = array($is_single_player ? esc_html__( 'Player', 'ekc-tournament-manager' ) : esc_html__( 'Team', 'ekc-tournament-manager' ), 'ekc-column-team');
		$header[] = array( esc_html__( 'Score', 'ekc-tournament-manager' ), 'ekc-column-score');
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $current_ranking as $ranking ) {
			$team = $db->get_team_by_id( $ranking->get_team_id() );
			$is_excluded = intval( $team->get_virtual_rank() ) !== 0;
			$row = array();
			$row[] = $counter;
			if ( $show_country ) {
				$row[] = $this->html_flag( $team->get_country() );
			}
			$row[] = esc_html($team->get_name());
			$row[] = strval( $ranking->get_total_score() ) . '&nbsp;/&nbsp;' . strval( $ranking->get_opponent_score() );
			$html_body .= $this->html_team_table_row( $row, 'rank-' . $counter, $is_excluded );
			$counter++;
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_team_table( $html_header . $html_body );
	}

	private function create_swiss_round_table( $tournament, $results_for_round, $round, $show_country, $score_as_input = false ) {
		$db = new Ekc_Database_Access();
		$swiss_helper = new Ekc_Swiss_System_Helper();
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$is_additional_round = $round > $tournament->get_swiss_system_rounds();
		$max_points_per_round = $tournament->get_swiss_system_max_points_per_round();

		$header = array();
		$header[] = array( esc_html__( 'Pitch', 'ekc-tournament-manager' ), 'ekc-column-pitch');
		if ( $show_country ) {
			$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		}
		$header[] = array($is_single_player ? esc_html__( 'Player', 'ekc-tournament-manager' ) : esc_html__( 'Team', 'ekc-tournament-manager' ), 'ekc-column-team');
		$header[] = array( esc_html__( 'Score', 'ekc-tournament-manager' ), 'ekc-column-score');
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
				$team1_country = $this->html_flag( $team1->get_country() );
			}
			if ( $show_country ) {
				$row[] = $team1_country;
			}
			$team1_name = '';
			if ($team1) {
				$team1_name = $team1->get_name();
			}
			else if ( Ekc_Team::is_bye_id( $result->get_team1_id() ) ) {
				$team1_name = 'BYE';
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
			$html_body .= $this->html_team_table_row( $row, 'pitch-' . $result->get_pitch(), false, 'rowspan' );

			// row for team2
			$row = array();
			$row[] = $result->get_pitch();
			$team2_country = '';
			if ($team2) {
				$team2_country = $this->html_flag( $team2->get_country() );
			}
			if ( $show_country ) {
				$row[] = $team2_country;
			}
			$team2_name = '';
			if ($team2) {
				$team2_name = $team2->get_name();
			}
			else if ( Ekc_Team::is_bye_id( $result->get_team2_id() ) ) {
				$team2_name = 'BYE';
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
			$html_body .= $this->html_team_table_row( $row, '', false, 'rowspan-omit' );
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_team_table( $html_header . $html_body );
	}

	private function html_score_input( $score_value, $html_id, $max_points_per_round ) {
		return '<input id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $html_id ) . '" type="number" class="ekc-score-input" size="5" step="any" min="0" max="' . esc_attr( $max_points_per_round ) . '" value="' . esc_attr( $score_value ) . '" />';
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
		  $now = new DateTime();
		  $display_text = '';
		  $is_round_finished = false;
		  if ( $tournament->get_swiss_system_round_time() ) {
			  $round_end_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
			  $round_end_date->add(new DateInterval('PT' . ($tournament->get_swiss_system_round_time()) . 'M')); // add minutes
			  $time_left = 0;
			  if ( $round_end_date > $now ) {
				$time_left = intval( $now->diff( $round_end_date )->format('%i') ) + 1; // i for minutes, +1 for 'rounding up'
			  }
			  else {
				$is_round_finished = true;
			  }
			  $display_text .= sprintf( /* translators: 1: tournament round number 2: time left in minutes */ esc_html__( 'Round %1$s: %2$s minutes left.', 'ekc-tournament-manager' ), esc_html( $current_round ), esc_html( $time_left ) );
		  }
		  if ( !$is_round_finished && $tournament->get_swiss_system_tiebreak_time() ) {
			$tiebreak_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
			$tiebreak_date->add(new DateInterval('PT' . ($tournament->get_swiss_system_tiebreak_time()) . 'M')); // add minutes
			if ( $tiebreak_date > $now ) {
			  $time_until_tiebreak = intval( $now->diff( $tiebreak_date )->format('%i') ) + 1; // i for minutes, +1 for 'rounding up'
			  $display_text .= sprintf( /* translators: %s: time in minutes */  esc_html__( ' Tie break starts in %s minutes.', 'ekc-tournament-manager' ), esc_html( $time_until_tiebreak ) );
			}
			else {
			  $time_since_tiebreak = intval( $tiebreak_date->diff( $now )->format('%i') ); // i for minutes
			  if ( $time_since_tiebreak < 30) { 
			  	$display_text .= sprintf( /* translators: %s: time in minutes */ esc_html__( ' Tie break since %s minutes.', 'ekc-tournament-manager' ), esc_html( $time_since_tiebreak ) );
			  }
			}
		  }
		  return $display_text;
		}

		if ( $current_round > 0 && ( $tournament->get_swiss_system_round_time() || $tournament->get_swiss_system_tiebreak_time() )) {
			return sprintf( /* translators: %s: tournament round number */ esc_html__( 'Round %s not started yet.', 'ekc-tournament-manager' ), esc_html( $current_round ) );
		}
		return '';
	}

	/***************************************************************************************************************************
	 * Shareable Links
	 ***************************************************************************************************************************/

	public function shortcode_shareable_link( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => 'team-results',
				'country' => 'true',
			),
			$atts,
			'ekc-link'
		);

		$validation_helper = new Ekc_Validation_Helper();
		$link_id = $validation_helper->validate_get_text( 'linkid' );

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
			return esc_html__( 'No data found.', 'ekc-tournament-manager' );
		}

		$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );
		$all_results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, null );
		$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );
		$current_round_result = $this->get_results_for_round( $all_results, $current_round, $team->get_team_id() );	
		
		$html = '<p><a href="' . esc_url( $validation_helper->validate_server_request_uri() ) . '">' . esc_html__( 'Reload page', 'ekc-tournament-manager' ) . '</a></p>';
		if (count( $current_round_result ) > 0) {
			$html .= '<h3>' . sprintf( /* translators: %s: tournament round number */ esc_html__( 'Round %s', 'ekc-tournament-manager' ), esc_html( $current_round ) ) . '</h3>';
			$earliest_reported_result_time = $db->get_earliest_result_log_time( $tournament->get_tournament_id(), $current_round );
			$report_end_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $earliest_reported_result_time );
			if ( $report_end_date ) {
				$report_end_date->add(new DateInterval('PT4H')); // add 4 hours
			}
			// Allow modifications to the result for up to 4 hours after the first result has been reported.
			// Relevant only for the very last round in the tournament. It reduces the risk of intentional or unintentional modifications after the tournament has ended.
			$now = new DateTime();
			if ( !$report_end_date || $report_end_date > $now ) {
				$html .= $this->create_current_round_result( $tournament, $current_round_result, $current_round, $show_country, $link_id );
			}
			else {
				// modification is not possible anymore
				$html .= $this->create_swiss_round_table( $tournament, $current_round_result, $current_round, $show_country );
			}
		}

		for ( $round = $current_round - 1; $round > 0; $round-- ) {
			$result_for_round = $this->get_results_for_round( $all_results, $round, $team->get_team_id() );
			if (count( $result_for_round ) > 0) {
				$html .= '<h3>' . sprintf( /* translators: %s: tournament round number */ esc_html__( 'Round %s', 'ekc-tournament-manager' ), esc_html( $round ) ) . '</h3>';
				$html .= $this->create_swiss_round_table( $tournament, $result_for_round, $round, $show_country );
			}
		}
		return $html;
	}

	private function create_current_round_result( $tournament, $current_round_result, $current_round, $show_country, $link_id ) {
		$nonce_helper = new Ekc_Nonce_Helper();
		// onsubmit handler for form defined in public.js
		$data_result_id = '';
		if ( count( $current_round_result ) > 0 ) {
			$data_result_id = ' data-resultid="' . esc_attr( $current_round_result[0]->get_result_id() ) . '" ';
		}
		$html = '<form id="ekc-result-form" class="ekc-form"' . $data_result_id . ' data-linkid="' . esc_attr( $link_id ) . '" data-nonce="' . esc_attr( wp_create_nonce( $nonce_helper->nonce_text( 'ekc_public_swiss_system_store_result', 'link', $link_id ) ) ) . '">';
		$html .= $this->create_swiss_round_table( $tournament, $current_round_result, $current_round, $show_country, true );
		$html .= '<div class="ekc-controls">';
		$html .= '<button class="ekc-button ekc-button-primary">' . sprintf( /* translators: %s: tournament round number */ esc_html__( 'Save result for round %s', 'ekc-tournament-manager' ), esc_html( $current_round ) ) . '</button>';
		$html .= '<p id="ekc-result-validation"></p>';
		$html .= '</div>';
		$html .= '</form>';
		return $html;
	}
	
	public function shortcode_shareable_link_handle_post() {
		$nonce_helper = new Ekc_Nonce_Helper();
		$validation_helper = new Ekc_Validation_Helper();
		$action = $validation_helper->validate_post_text( 'action' );
		$link_id = $validation_helper->validate_post_text( 'linkid' );
		
		if ( $action === 'ekc_public_swiss_system_store_result' ) {
			if ( ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( 'ekc_public_swiss_system_store_result', 'link', $link_id ) ) ) {
				echo '<span class="dashicons dashicons-no"></span>' . esc_html__( 'Failed to store result', 'ekc-tournament-manager' );
			  	wp_die();
			}
			
			$db = new Ekc_Database_Access();
			$team = $db->get_team_by_shareable_link_id( $link_id );
			if ( $team ) {
				$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );
				$current_round = $db->get_current_swiss_system_round( $tournament->get_tournament_id() );
				$current_round_results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, $current_round );
			
				$current_result = $this->get_results_for_round( $current_round_results, $current_round, $team->get_team_id() );
			
				$this->store_result( $current_result[0], $team->get_team_id() );
				echo '<span class="dashicons dashicons-yes"></span>' . esc_html__( 'Result saved', 'ekc-tournament-manager' );
				wp_die();
			}

			// Fallback
			echo '<span class="dashicons dashicons-no"></span>' . esc_html__( 'Failed to store result', 'ekc-tournament-manager' );
			wp_die();
	  	}
	}
		
	private function store_result( $existing_result, $log_team_id ) {
		$validation_helper = new Ekc_Validation_Helper();
		$team1_score_id = 'team1-score-' . $existing_result->get_result_id();
		$team2_score_id = 'team2-score-' . $existing_result->get_result_id();
		$existing_result->set_team1_score( $validation_helper->validate_post_integer( $team1_score_id ) );
		$existing_result->set_team2_score( $validation_helper->validate_post_integer( $team2_score_id ) );
		$db = new Ekc_Database_Access();
		$db->update_tournament_result( $existing_result );
		$db->insert_tournament_result_log( $existing_result, $log_team_id );
	}

	/***************************************************************************************************************************
	 * Nation Trophy
	 ***************************************************************************************************************************/

	public function shortcode_nation_trophy( $atts ) {
		$atts = shortcode_atts(
			array(
				'tournament-1vs1' => '',
				'tournament-3vs3' => '',
				'tournament-6vs6' => '',
			),
			$atts,
			'ekc-nation-trophy'
		);
		$tournament_1vs1_code_name = $atts['tournament-1vs1'];
		$tournament_3vs3_code_name = $atts['tournament-3vs3'];
		$tournament_6vs6_code_name = $atts['tournament-6vs6'];
		
		if ( trim( $tournament_1vs1_code_name ) === ''
			|| trim( $tournament_3vs3_code_name ) === '' 
			|| trim( $tournament_6vs6_code_name ) === '') {
			return '';
		}

		$db = new Ekc_Database_Access();
		$helper = new Ekc_Nation_Trophy_Helper();

		$country_total_score = array(); // map: country code => total score for country
		$country_teams = array(); // map: country code => ordered list of team ids (sorted by tournament and score)
		$team_description = array(); // map: team_id => Ekc_Nation_Trophy_Rank_Description
		$helper->collect_nation_trophy_results( $tournament_6vs6_code_name, Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_6VS6, $country_total_score, $country_teams, $team_description );
		$helper->collect_nation_trophy_results( $tournament_3vs3_code_name, Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_3VS3, $country_total_score, $country_teams, $team_description );
		$helper->collect_nation_trophy_results( $tournament_1vs1_code_name, Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_1VS1, $country_total_score, $country_teams, $team_description );
		
		arsort( $country_total_score ); // sort descending by value

		// add a separate table body with an expandable header row for each country
		$html_table = '';
		$counter = 1;
		
		foreach ( $country_total_score as $country_code => $country_score ) {
			$html_body = $this->html_nation_trophy_header_row( $counter, $country_code, $country_score );
			foreach ( $country_teams[$country_code] as $team_id ) {
				$team = $db->get_team_by_id( $team_id );
				$html_body .= $this->html_nation_trophy_table_row( $team->get_name(), $team_description[$team_id] );
			}
			
			$html_body = $this->html_table_body($html_body); // wrap with <tbody>
			$html_table .= $html_body;
			$counter++;
		}
		
		return $this->html_nation_trophy_table( $html_table );
	}

	private function html_nation_trophy_table( $html_body ) {
		$html = '<table class="ekc-expandable-table ekc-nation-trophy-table">';
		$html .= '<colgroup class="ekc-colgroup">';
        $html .= '<col class="ekc-col ekc-column-nation-trophy-rank">';
		$html .= '<col class="ekc-col ekc-column-nation-trophy-flag">';
        $html .= '<col class="ekc-col ekc-column-nation-trophy-country">';
        $html .= '<col class="ekc-col ekc-column-nation-trophy-tournament">';
        $html .= '<col class="ekc-col ekc-column-nation-trophy-score">';
		$html .= '</colgroup>';
		$html .= $html_body . '</table>';
		return $html;
	}

	private function html_nation_trophy_header_row( $rank, $country_code, $score ) {
		$country_name = $this->get_country_name( $country_code );
		$html = '<tr class="ekc-tr ekc-expandable-header-row">';
		$html .= '<td class="ekc-td ekc-td-right-open"><span class="dashicons dashicons-arrow-right"></span>' . esc_html( $rank ) . '</td>';
		$html .= '<td class="ekc-td ekc-td-left-open">' . $this->html_flag( $country_code ) . '</td>';
		$html .= '<td class="ekc-td ekc-td-right-open">' . esc_html( $country_name ) . '</td>';
		$html .= '<td class="ekc-td ekc-td-left-open"></td>';
		$html .= '<td class="ekc-td">' . esc_html( $score ) . '</td>';
		return $html . '</tr>';
	}

	private function get_country_name( $country_code ) {
		$country_name = Ekc_Drop_Down_Helper::COUNTRY_COMMON[$country_code];
		if ( !$country_name ) {
			$country_name =  Ekc_Drop_Down_Helper::COUNTRY_OTHER[$country_code];
		}
		if ( !$country_name ) {
			$country_name =  Ekc_Drop_Down_Helper::COUNTRY_SPECIAL[$country_code];
		}
		return $country_name;
	}

	private function html_nation_trophy_table_row( $team_name, $rank_description ) {
		$score_html = '-';
		if ( $rank_description->get_score() > 0 ) {
			$score_html = esc_html( $rank_description->get_score() );
		}
		$html = '<tr class="ekc-tr ekc-expandable-row">';
		$html .= '<td class="ekc-td" colspan="2"></td>';
		$html .= '<td class="ekc-td ekc-td-right-open">' . esc_html( $team_name ) . '</td>';
		$html .= '<td class="ekc-td ekc-td-left-open">' . esc_html( $rank_description->get_description() ) . '</td>';
		$html .= '<td class="ekc-td">' . $score_html . '</td>';
		return $html . '</tr>';
	}
}
