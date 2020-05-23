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
			),
			$atts,
			'ekc-teams'
		);
		$tournament_code_name = $atts['tournament'];
		$limit = $atts['limit'] === 'all' ? 0 : intval( $atts['limit'] );
		$sort = $atts['sort'];
		$is_wait_list =  filter_var( $atts['waitlist'], FILTER_VALIDATE_BOOLEAN );

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
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc );
		}
		else {
			$teams = $db->get_active_teams($tournament->get_tournament_id(), $limit, $sort);

			$c = 1;
			if ( $is_sort_desc ) {
				$c = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			}
			return $this->create_teams_table( $teams, $tournament, $c, $is_sort_desc );
		}
	}

	private function create_teams_table( $teams, $tournament, $counter, $is_sort_desc ) {
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$header = array();
		$header[] = array('<span class="dashicons dashicons-arrow-down-alt"></span>', 'ekc-column-no');
		$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
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
			$row[] = $this->html_flag( esc_html($team->get_country()) );
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
			),
			$atts,
			'ekc-elimination-bracket'
		);
		$tournament_code_name = $atts['tournament'];

		if ( trim( $tournament_code_name ) === '' ) {
			return '';
		}
		
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name($tournament_code_name);
		if ( ! $tournament ) {
			return '';
		}
		$results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO, null, null );
		$elimination_bracket = $tournament->get_elimination_rounds();

		$html = '';
		if ( Ekc_Elimination_Bracket_Helper::has_1_16_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_round_of_32_div( $results );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_8_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_round_of_16_div( $results );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_4_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_quarterfinals_div( $results );
		}
		if ( Ekc_Elimination_Bracket_Helper::has_1_2_finals( $elimination_bracket ) ) {
			$html = $html . $this->bracket_semifinals_div( $results );
		}
		$html = $html . $this->bracket_finals_div( $results );
		return $this->bracket_main_div( $this->elimination_bracket_to_css_class( $elimination_bracket ), $html );
	}

	private function bracket_main_div( $participants_css_class, $inner ) {
		return '<div class="tournament-bracket ' . $participants_css_class . '">' . $inner . '</div>';
	}

	private function bracket_finals_div( $results ) {
		$db = new Ekc_Database_Access();
		$result_final = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_1);
		$result_3rd = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_2);

		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result_final ) {
			$team1 = $db->get_team_by_id ( $result_final->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result_final->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result_final->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result_final ) {
			$team2 = $db->get_team_by_id ( $result_final->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result_final->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result_final->get_team2_score();
			}	
		}

		$team3_name = '';
		$team3_country = '';
		$team3_score = '';
		if ( $result_3rd ) {
			$team3 = $db->get_team_by_id ( $result_3rd->get_team1_id() );
			if ( $team3 ) {
				$team3_name = $this->get_name_or_placeholder( $result_3rd->get_team1_placeholder() , $team3 );
				$team3_country = $team3->get_country();
				$team3_score = $result_3rd->get_team1_score();
			}
		}

		$team4_name = '';
		$team4_country = '';
		$team4_score = '';
		if ( $result_3rd ) {
			$team4 = $db->get_team_by_id ( $result_3rd->get_team2_id() );
			if ( $team4 ) {
				$team4_name = $this->get_name_or_placeholder( $result_3rd->get_team2_placeholder() , $team4 );
				$team4_country = $team4->get_country();
				$team4_score = $result_3rd->get_team2_score();
			}
		}

		return
		'<div class="round finals">
		<span class="round-label">Finals</span>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
				<div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team1_country . '"></span><span class="label">' . $team1_name . '</span><span class="score"> ' . $team1_score . '</span></div>
				<div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team2_country . '"></span><span class="label">' . $team2_name . '</span><span class="score"> ' . $team2_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
				<div class="participants">
					<div class="participant ' . ($team3_score < $team4_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team3_country . '"></span><span class="label">' . $team3_name . '</span><span class="score"> ' . $team3_score . '</span></div>
					<div class="participant ' . ($team4_score < $team3_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team4_country . '"></span><span class="label">' . $team4_name . '</span><span class="score"> ' . $team4_score . '</span></div>
				</div>
			  </div>
			</div>
		  </div>
		</div>';
	}

	private function bracket_semifinals_div( $results ) {
		$db = new Ekc_Database_Access();
		$result_semi_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_1);
		$result_semi_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_2);
		
		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result_semi_1 ) {
			$team1 = $db->get_team_by_id ( $result_semi_1->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result_semi_1->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result_semi_1->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result_semi_1 ) {
			$team2 = $db->get_team_by_id ( $result_semi_1->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result_semi_1->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result_semi_1->get_team2_score();
			}
		}

		$team3_name = '';
		$team3_country = '';
		$team3_score = '';
		if ( $result_semi_2 ) {
			$team3 = $db->get_team_by_id ( $result_semi_2->get_team1_id() );
			if ( $team3 ) {
				$team3_name = $this->get_name_or_placeholder( $result_semi_2->get_team1_placeholder() , $team3 );
				$team3_country = $team3->get_country();
				$team3_score = $result_semi_2->get_team1_score();
			}
		}
		
		$team4_name = '';
		$team4_country = '';
		$team4_score = '';
		if ( $result_semi_2 ) {
			$team4 = $db->get_team_by_id ( $result_semi_2->get_team2_id() );
			if ( $team4 ) {
				$team4_name = $this->get_name_or_placeholder( $result_semi_2->get_team2_placeholder() , $team4 );
				$team4_country = $team4->get_country();
				$team4_score = $result_semi_2->get_team2_score();
			}
		}

		return
		'<div class="round semifinals">
		  <span class="round-label">Semifinals</span>
		  <div class="single-bracket">
		    <div class="matchups">
			    <div class="matchup">
			      <div class="participants">
			       <div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team1_country . '"></span><span class="label">' . $team1_name . '</span><span class="score"> ' . $team1_score . '</span></div>
			       <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team2_country . '"></span><span class="label">' . $team2_name . '</span><span class="score"> ' . $team2_score . '</span></div>
			      </div>
			    </div>
			    <div class="matchup">
			     <div class="participants">
					  <div class="participant ' . ($team3_score < $team4_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team3_country . '"></span><span class="label">' . $team3_name . '</span><span class="score"> ' . $team3_score . '</span></div>
			      <div class="participant ' . ($team4_score < $team3_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team4_country . '"></span><span class="label">' . $team4_name . '</span><span class="score"> ' . $team4_score . '</span></div>
			     </div>
			    </div>
			  </div>
			  <div class="connector">
			    <div class="merger"></div>
			    <div class="line"></div>
			  </div>
		  </div>
		</div>';
	}

	private function bracket_quarterfinals_div( $results ) {
		$db = new Ekc_Database_Access();
		
		$result_quarter_1 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_1);
		$result_quarter_2 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_2);
		$result_quarter_3 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_3);
		$result_quarter_4 = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_4);
		
		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result_quarter_1 ) {
			$team1 = $db->get_team_by_id ( $result_quarter_1->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result_quarter_1->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result_quarter_1->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result_quarter_1 ) {
			$team2 = $db->get_team_by_id ( $result_quarter_1->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result_quarter_1->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result_quarter_1->get_team2_score();
			}
		}

		$team3_name = '';
		$team3_country = '';
		$team3_score = '';
		if ( $result_quarter_2 ) {
			$team3 = $db->get_team_by_id ( $result_quarter_2->get_team1_id() );
			if ( $team3 ) {
				$team3_name = $this->get_name_or_placeholder( $result_quarter_2->get_team1_placeholder() , $team3 );
				$team3_country = $team3->get_country();
				$team3_score = $result_quarter_2->get_team1_score();
			}
		}
		
		$team4_name = '';
		$team4_country = '';
		$team4_score = '';
		if ( $result_quarter_2 ) {
			$team4 = $db->get_team_by_id ( $result_quarter_2->get_team2_id() );
			if ( $team4 ) {
				$team4_name = $this->get_name_or_placeholder( $result_quarter_2->get_team2_placeholder() , $team4 );
				$team4_country = $team4->get_country();
				$team4_score = $result_quarter_2->get_team2_score();
			}
		}
		
		$team5_name = '';
		$team5_country = '';
		$team5_score = '';
		if ( $result_quarter_3 ) {
			$team5 = $db->get_team_by_id ( $result_quarter_3->get_team1_id() );
			if ( $team5 ) {
				$team5_name = $this->get_name_or_placeholder( $result_quarter_3->get_team1_placeholder() , $team5 );
				$team5_country = $team5->get_country();
				$team5_score = $result_quarter_3->get_team1_score();
			}
		}

		$team6_name = '';
		$team6_country = '';
		$team6_score = '';
		if ( $result_quarter_3 ) {
			$team6 = $db->get_team_by_id ( $result_quarter_3->get_team2_id() );
			if ( $team6 ) {
				$team6_name = $this->get_name_or_placeholder( $result_quarter_3->get_team2_placeholder() , $team6 );
				$team6_country = $team6->get_country();
				$team6_score = $result_quarter_3->get_team2_score();
			}
		}

		$team7_name = '';
		$team7_country = '';
		$team7_score = '';
		if ( $result_quarter_4 ) {
			$team7 = $db->get_team_by_id ( $result_quarter_4->get_team1_id() );
			if ( $team7 ) {
				$team7_name = $this->get_name_or_placeholder( $result_quarter_4->get_team1_placeholder() , $team7 );
				$team7_country = $team7->get_country();
				$team7_score = $result_quarter_4->get_team1_score();
			}
		}
		
		$team8_name = '';
		$team8_country = '';
		$team8_score = '';
		if ( $result_quarter_4 ) {
			$team8 = $db->get_team_by_id ( $result_quarter_4->get_team2_id() );
			if ( $team8 ) {
				$team8_name = $this->get_name_or_placeholder( $result_quarter_4->get_team2_placeholder() , $team8 );
				$team8_country = $team8->get_country();
				$team8_score = $result_quarter_4->get_team2_score();
			}
		}

		return
		'<div class="round quarterfinals">
		<span class="round-label">1/4 Finals</span>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team1_country . '"></span><span class="label">' . $team1_name . '</span><span class="score"> ' . $team1_score . '</span></div>
			  <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team2_country . '"></span><span class="label">' . $team2_name . '</span><span class="score"> ' . $team2_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team3_score < $team4_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team3_country . '"></span><span class="label">' . $team3_name . '</span><span class="score"> ' . $team3_score . '</span></div>
			  <div class="participant ' . ($team4_score < $team3_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team4_country . '"></span><span class="label">' . $team4_name . '</span><span class="score"> ' . $team4_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team5_score < $team6_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team5_country . '"></span><span class="label">' . $team5_name . '</span><span class="score"> ' . $team5_score . '</span></div>
			  <div class="participant ' . ($team6_score < $team5_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team6_country . '"></span><span class="label">' . $team6_name . '</span><span class="score"> ' . $team6_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team7_score < $team8_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team7_country . '"></span><span class="label">' . $team7_name . '</span><span class="score"> ' . $team7_score . '</span></div>
			  <div class="participant ' . ($team8_score < $team7_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team8_country . '"></span><span class="label">' . $team8_name . '</span><span class="score"> ' . $team8_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
	  </div>';
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
		
		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result_1_8_1 ) {
			$team1 = $db->get_team_by_id ( $result_1_8_1->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result_1_8_1->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result_1_8_1->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result_1_8_1 ) {
			$team2 = $db->get_team_by_id ( $result_1_8_1->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result_1_8_1->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result_1_8_1->get_team2_score();
			}
		}

		$team3_name = '';
		$team3_country = '';
		$team3_score = '';
		if ( $result_1_8_2 ) {
			$team3 = $db->get_team_by_id ( $result_1_8_2->get_team1_id() );
			if ( $team3 ) {
				$team3_name = $this->get_name_or_placeholder( $result_1_8_2->get_team1_placeholder() , $team3 );
				$team3_country = $team3->get_country();
				$team3_score = $result_1_8_2->get_team1_score();
			}
		}
		
		$team4_name = '';
		$team4_country = '';
		$team4_score = '';
		if ( $result_1_8_2 ) {
			$team4 = $db->get_team_by_id ( $result_1_8_2->get_team2_id() );
			if ( $team4 ) {
				$team4_name = $this->get_name_or_placeholder( $result_1_8_2->get_team2_placeholder() , $team4 );
				$team4_country = $team4->get_country();
				$team4_score = $result_1_8_2->get_team2_score();
			}
		}
		
		$team5_name = '';
		$team5_country = '';
		$team5_score = '';
		if ( $result_1_8_3 ) {
			$team5 = $db->get_team_by_id ( $result_1_8_3->get_team1_id() );
			if ( $team5 ) {
				$team5_name = $this->get_name_or_placeholder( $result_1_8_3->get_team1_placeholder() , $team5 );
				$team5_country = $team5->get_country();
				$team5_score = $result_1_8_3->get_team1_score();
			}
		}

		$team6_name = '';
		$team6_country = '';
		$team6_score = '';
		if ( $result_1_8_3 ) {
			$team6 = $db->get_team_by_id ( $result_1_8_3->get_team2_id() );
			if ( $team6 ) {
				$team6_name = $this->get_name_or_placeholder( $result_1_8_3->get_team2_placeholder() , $team6 );
				$team6_country = $team6->get_country();
				$team6_score = $result_1_8_3->get_team2_score();
			}	
		}

		$team7_name = '';
		$team7_country = '';
		$team7_score = '';
		if ( $result_1_8_4 ) {
			$team7 = $db->get_team_by_id ( $result_1_8_4->get_team1_id() );
			if ( $team7 ) {
				$team7_name = $this->get_name_or_placeholder( $result_1_8_4->get_team1_placeholder() , $team7 );
				$team7_country = $team7->get_country();
				$team7_score = $result_1_8_4->get_team1_score();
			}	
		}
		
		$team8_name = '';
		$team8_country = '';
		$team8_score = '';
		if ( $result_1_8_4 ) {
			$team8 = $db->get_team_by_id ( $result_1_8_4->get_team2_id() );
			if ( $team8 ) {
				$team8_name = $this->get_name_or_placeholder( $result_1_8_4->get_team2_placeholder() , $team8 );
				$team8_country = $team8->get_country();
				$team8_score = $result_1_8_4->get_team2_score();
			}
		}
		
		$team9_name = '';
		$team9_country = '';
		$team9_score = '';
		if ( $result_1_8_5 ) {
			$team9 = $db->get_team_by_id ( $result_1_8_5->get_team1_id() );
			if ( $team9 ) {
				$team9_name = $this->get_name_or_placeholder( $result_1_8_5->get_team1_placeholder() , $team9 );
				$team9_country = $team9->get_country();
				$team9_score = $result_1_8_5->get_team1_score();
			}
		}

		$team10_name = '';
		$team10_country = '';
		$team10_score = '';
		if ( $result_1_8_5 ) {
			$team10 = $db->get_team_by_id ( $result_1_8_5->get_team2_id() );
			if ( $team10 ) {
				$team10_name = $this->get_name_or_placeholder( $result_1_8_5->get_team2_placeholder() , $team10 );
				$team10_country = $team10->get_country();
				$team10_score = $result_1_8_5->get_team2_score();
			}
		}

		$team11_name = '';
		$team11_country = '';
		$team11_score = '';
		if ( $result_1_8_6 ) {
			$team11 = $db->get_team_by_id ( $result_1_8_6->get_team1_id() );
			if ( $team11 ) {
				$team11_name = $this->get_name_or_placeholder( $result_1_8_6->get_team1_placeholder() , $team11 );
				$team11_country = $team11->get_country();
				$team11_score = $result_1_8_6->get_team1_score();
			}
		}
		
		$team12_name = '';
		$team12_country = '';
		$team12_score = '';
		if ( $result_1_8_6 ) {
			$team12 = $db->get_team_by_id ( $result_1_8_6->get_team2_id() );
			if ( $team12 ) {
				$team12_name = $this->get_name_or_placeholder( $result_1_8_6->get_team2_placeholder() , $team12 );
				$team12_country = $team12->get_country();
				$team12_score = $result_1_8_6->get_team2_score();
			}
		}
		
		$team13_name = '';
		$team13_country = '';
		$team13_score = '';
		if ( $result_1_8_7 ) {
			$team13 = $db->get_team_by_id ( $result_1_8_7->get_team1_id() );
			if ( $team13 ) {
				$team13_name = $this->get_name_or_placeholder( $result_1_8_7->get_team1_placeholder() , $team13 );
				$team13_country = $team13->get_country();
				$team13_score = $result_1_8_7->get_team1_score();
			}
		}

		$team14_name = '';
		$team14_country = '';
		$team14_score = '';
		if ( $result_1_8_7 ) {
			$team14 = $db->get_team_by_id ( $result_1_8_7->get_team2_id() );
			if ( $team14 ) {
				$team14_name = $this->get_name_or_placeholder( $result_1_8_7->get_team2_placeholder() , $team14 );
				$team14_country = $team14->get_country();
				$team14_score = $result_1_8_7->get_team2_score();
			}
		}

		$team15_name = '';
		$team15_country = '';
		$team15_score = '';
		if ( $result_1_8_8 ) {
			$team15 = $db->get_team_by_id ( $result_1_8_8->get_team1_id() );
			if ( $team15 ) {
				$team15_name = $this->get_name_or_placeholder( $result_1_8_8->get_team1_placeholder() , $team15 );
				$team15_country = $team15->get_country();
				$team15_score = $result_1_8_8->get_team1_score();
			}
		}
		
		$team16_name = '';
		$team16_country = '';
		$team16_score = '';
		if ( $result_1_8_8 ) {
			$team16 = $db->get_team_by_id ( $result_1_8_8->get_team2_id() );
			if ( $team16 ) {
				$team16_name = $this->get_name_or_placeholder( $result_1_8_8->get_team2_placeholder() , $team16 );
				$team16_country = $team16->get_country();
				$team16_score = $result_1_8_8->get_team2_score();
			}
		}

		return
		'<div class="round round-of-16">
		<span class="round-label">1/8 Finals</span>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team1_country . '"></span><span class="label">' . $team1_name . '</span><span class="score"> ' . $team1_score . '</span></div>
			  <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team2_country . '"></span><span class="label">' . $team2_name . '</span><span class="score"> ' . $team2_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team3_score < $team4_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team3_country . '"></span><span class="label">' . $team3_name . '</span><span class="score"> ' . $team3_score . '</span></div>
			  <div class="participant ' . ($team4_score < $team3_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team4_country . '"></span><span class="label">' . $team4_name . '</span><span class="score"> ' . $team4_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team5_score < $team6_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team5_country . '"></span><span class="label">' . $team5_name . '</span><span class="score"> ' . $team5_score . '</span></div>
			  <div class="participant ' . ($team6_score < $team5_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team6_country . '"></span><span class="label">' . $team6_name . '</span><span class="score"> ' . $team6_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team7_score < $team8_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team7_country . '"></span><span class="label">' . $team7_name . '</span><span class="score"> ' . $team7_score . '</span></div>
			  <div class="participant ' . ($team8_score < $team7_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team8_country . '"></span><span class="label">' . $team8_name . '</span><span class="score"> ' . $team8_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
		<div class="single-bracket">
			<div class="matchups">
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team9_score < $team10_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team9_country . '"></span><span class="label">' . $team9_name . '</span><span class="score"> ' . $team9_score . '</span></div>
				<div class="participant ' . ($team10_score < $team9_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team10_country . '"></span><span class="label">' . $team10_name . '</span><span class="score"> ' . $team10_score . '</span></div>
				</div>
			  </div>
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team11_score < $team12_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team11_country . '"></span><span class="label">' . $team11_name . '</span><span class="score"> ' . $team11_score . '</span></div>
				<div class="participant ' . ($team12_score < $team11_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team12_country . '"></span><span class="label">' . $team12_name . '</span><span class="score"> ' . $team12_score . '</span></div>
				</div>
			  </div>
			</div>
			<div class="connector">
			  <div class="merger"></div>
			  <div class="line"></div>
			</div>
		</div>
		  <div class="single-bracket">
			<div class="matchups">
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team13_score < $team14_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team13_country . '"></span><span class="label">' . $team13_name . '</span><span class="score"> ' . $team13_score . '</span></div>
				<div class="participant ' . ($team14_score < $team13_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team14_country . '"></span><span class="label">' . $team14_name . '</span><span class="score"> ' . $team14_score . '</span></div>
				</div>
			  </div>
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team15_score < $team16_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team15_country . '"></span><span class="label">' . $team15_name . '</span><span class="score"> ' . $team15_score . '</span></div>
				<div class="participant ' . ($team16_score < $team15_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team16_country . '"></span><span class="label">' . $team16_name . '</span><span class="score"> ' . $team16_score . '</span></div>
				</div>
			  </div>
			</div>
			<div class="connector">
			  <div class="merger"></div>
			  <div class="line"></div>
			</div>
		  </div>
	</div>';
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
		
		$team1_name = '';
		$team1_country = '';
		$team1_score = '';
		if ( $result_1_16_1 ) {
			$team1 = $db->get_team_by_id ( $result_1_16_1->get_team1_id() );
			if ( $team1 ) {
				$team1_name = $this->get_name_or_placeholder( $result_1_16_1->get_team1_placeholder() , $team1 );
				$team1_country = $team1->get_country();
				$team1_score = $result_1_16_1->get_team1_score();
			}
		}

		$team2_name = '';
		$team2_country = '';
		$team2_score = '';
		if ( $result_1_16_1 ) {
			$team2 = $db->get_team_by_id ( $result_1_16_1->get_team2_id() );
			if ( $team2 ) {
				$team2_name = $this->get_name_or_placeholder( $result_1_16_1->get_team2_placeholder() , $team2 );
				$team2_country = $team2->get_country();
				$team2_score = $result_1_16_1->get_team2_score();
			}
		}

		$team3_name = '';
		$team3_country = '';
		$team3_score = '';
		if ( $result_1_16_2 ) {
			$team3 = $db->get_team_by_id ( $result_1_16_2->get_team1_id() );
			if ( $team3 ) {
				$team3_name = $this->get_name_or_placeholder( $result_1_16_2->get_team1_placeholder() , $team3 );
				$team3_country = $team3->get_country();
				$team3_score = $result_1_16_2->get_team1_score();
			}
		}
		
		$team4_name = '';
		$team4_country = '';
		$team4_score = '';
		if ( $result_1_16_2 ) {
			$team4 = $db->get_team_by_id ( $result_1_16_2->get_team2_id() );
			if ( $team4 ) {
				$team4_name = $this->get_name_or_placeholder( $result_1_16_2->get_team2_placeholder() , $team4 );
				$team4_country = $team4->get_country();
				$team4_score = $result_1_16_2->get_team2_score();
			}
		}
		
		$team5_name = '';
		$team5_country = '';
		$team5_score = '';
		if ( $result_1_16_3 ) {
			$team5 = $db->get_team_by_id ( $result_1_16_3->get_team1_id() );
			if ( $team5 ) {
				$team5_name = $this->get_name_or_placeholder( $result_1_16_3->get_team1_placeholder() , $team5 );
				$team5_country = $team5->get_country();
				$team5_score = $result_1_16_3->get_team1_score();
			}
		}

		$team6_name = '';
		$team6_country = '';
		$team6_score = '';
		if ( $result_1_16_3 ) {
			$team6 = $db->get_team_by_id ( $result_1_16_3->get_team2_id() );
			if ( $team6 ) {
				$team6_name = $this->get_name_or_placeholder( $result_1_16_3->get_team2_placeholder() , $team6 );
				$team6_country = $team6->get_country();
				$team6_score = $result_1_16_3->get_team2_score();
			}
		}

		$team7_name = '';
		$team7_country = '';
		$team7_score = '';
		if ( $result_1_16_4 ) {
			$team7 = $db->get_team_by_id ( $result_1_16_4->get_team1_id() );
			if ( $team7 ) {
				$team7_name = $this->get_name_or_placeholder( $result_1_16_4->get_team1_placeholder() , $team7 );
				$team7_country = $team7->get_country();
				$team7_score = $result_1_16_4->get_team1_score();
			}
		}
		
		$team8_name = '';
		$team8_country = '';
		$team8_score = '';
		if ( $result_1_16_4 ) {
			$team8 = $db->get_team_by_id ( $result_1_16_4->get_team2_id() );
			if ( $team8 ) {
				$team8_name = $this->get_name_or_placeholder( $result_1_16_4->get_team2_placeholder() , $team8 );
				$team8_country = $team8->get_country();
				$team8_score = $result_1_16_4->get_team2_score();
			}
		}
		
		$team9_name = '';
		$team9_country = '';
		$team9_score = '';
		if ( $result_1_16_5 ) {
			$team9 = $db->get_team_by_id ( $result_1_16_5->get_team1_id() );
			if ( $team9 ) {
				$team9_name = $this->get_name_or_placeholder( $result_1_16_5->get_team1_placeholder() , $team9 );
				$team9_country = $team9->get_country();
				$team9_score = $result_1_16_5->get_team1_score();
			}
		}

		$team10_name = '';
		$team10_country = '';
		$team10_score = '';
		if ( $result_1_16_5 ) {
			$team10 = $db->get_team_by_id ( $result_1_16_5->get_team2_id() );
			if ( $team10 ) {
				$team10_name = $this->get_name_or_placeholder( $result_1_16_5->get_team2_placeholder() , $team10 );
				$team10_country = $team10->get_country();
				$team10_score = $result_1_16_5->get_team2_score();
			}
		}

		$team11_name = '';
		$team11_country = '';
		$team11_score = '';
		if ( $result_1_16_6 ) {
			$team11 = $db->get_team_by_id ( $result_1_16_6->get_team1_id() );
			if ( $team11 ) {
				$team11_name = $this->get_name_or_placeholder( $result_1_16_6->get_team1_placeholder() , $team11 );
				$team11_country = $team11->get_country();
				$team11_score = $result_1_16_6->get_team1_score();
			}
		}
		
		$team12_name = '';
		$team12_country = '';
		$team12_score = '';
		if ( $result_1_16_6 ) {
			$team12 = $db->get_team_by_id ( $result_1_16_6->get_team2_id() );
			if ( $team12 ) {
				$team12_name = $this->get_name_or_placeholder( $result_1_16_6->get_team2_placeholder() , $team12 );
				$team12_country = $team12->get_country();
				$team12_score = $result_1_16_6->get_team2_score();
			}
		}
		
		$team13_name = '';
		$team13_country = '';
		$team13_score = '';
		if ( $result_1_16_7 ) {
			$team13 = $db->get_team_by_id ( $result_1_16_7->get_team1_id() );
			if ( $team13 ) {
				$team13_name = $this->get_name_or_placeholder( $result_1_16_7->get_team1_placeholder() , $team13 );
				$team13_country = $team13->get_country();
				$team13_score = $result_1_16_7->get_team1_score();
			}
		}

		$team14_name = '';
		$team14_country = '';
		$team14_score = '';
		if ( $result_1_16_7 ) {
			$team14 = $db->get_team_by_id ( $result_1_16_7->get_team2_id() );
			if ( $team14 ) {
				$team14_name = $this->get_name_or_placeholder( $result_1_16_7->get_team2_placeholder() , $team14 );
				$team14_country = $team14->get_country();
				$team14_score = $result_1_16_7->get_team2_score();
			}
		}

		$team15_name = '';
		$team15_country = '';
		$team15_score = '';
		if ( $result_1_16_8 ) {
			$team15 = $db->get_team_by_id ( $result_1_16_8->get_team1_id() );
			if ( $team15 ) {
				$team15_name = $this->get_name_or_placeholder( $result_1_16_8->get_team1_placeholder() , $team15 );
				$team15_country = $team15->get_country();
				$team15_score = $result_1_16_8->get_team1_score();
			}
		}
		
		$team16_name = '';
		$team16_country = '';
		$team16_score = '';
		if ( $result_1_16_8 ) {
			$team16 = $db->get_team_by_id ( $result_1_16_8->get_team2_id() );
			if ( $team16 ) {
				$team16_name = $this->get_name_or_placeholder( $result_1_16_8->get_team2_placeholder() , $team16 );
				$team16_country = $team16->get_country();
				$team16_score = $result_1_16_8->get_team2_score();
			}
		}

		$team17_name = '';
		$team17_country = '';
		$team17_score = '';
		if ( $result_1_16_9 ) {
			$team17 = $db->get_team_by_id ( $result_1_16_9->get_team1_id() );
			if ( $team17 ) {
				$team17_name = $this->get_name_or_placeholder( $result_1_16_9->get_team1_placeholder() , $team17 );
				$team17_country = $team17->get_country();
				$team17_score = $result_1_16_9->get_team1_score();
			}
		}

		$team18_name = '';
		$team18_country = '';
		$team18_score = '';
		if ( $result_1_16_9 ) {
			$team18 = $db->get_team_by_id ( $result_1_16_9->get_team2_id() );
			if ( $team18 ) {
				$team18_name = $this->get_name_or_placeholder( $result_1_16_9->get_team2_placeholder() , $team18 );
				$team18_country = $team18->get_country();
				$team18_score = $result_1_16_9->get_team2_score();
			}
		}

		$team19_name = '';
		$team19_country = '';
		$team19_score = '';
		if ( $result_1_16_10 ) {
			$team19 = $db->get_team_by_id ( $result_1_16_10->get_team1_id() );
			if ( $team19 ) {
				$team19_name = $this->get_name_or_placeholder( $result_1_16_10->get_team1_placeholder() , $team19 );
				$team19_country = $team19->get_country();
				$team19_score = $result_1_16_10->get_team1_score();
			}
		}
		
		$team20_name = '';
		$team20_country = '';
		$team20_score = '';
		if ( $result_1_16_10 ) {
			$team20 = $db->get_team_by_id ( $result_1_16_10->get_team2_id() );
			if ( $team20 ) {
				$team20_name = $this->get_name_or_placeholder( $result_1_16_10->get_team2_placeholder() , $team20 );
				$team20_country = $team20->get_country();
				$team20_score = $result_1_16_10->get_team2_score();
			}
		}
		
		$team21_name = '';
		$team21_country = '';
		$team21_score = '';
		if ( $result_1_16_11 ) {
			$team21 = $db->get_team_by_id ( $result_1_16_11->get_team1_id() );
			if ( $team21 ) {
				$team21_name = $this->get_name_or_placeholder( $result_1_16_11->get_team1_placeholder() , $team21 );
				$team21_country = $team21->get_country();
				$team21_score = $result_1_16_11->get_team1_score();
			}
		}

		$team22_name = '';
		$team22_country = '';
		$team22_score = '';
		if ( $result_1_16_11 ) {
			$team22 = $db->get_team_by_id ( $result_1_16_11->get_team2_id() );
			if ( $team22 ) {
				$team22_name = $this->get_name_or_placeholder( $result_1_16_11->get_team2_placeholder() , $team22 );
				$team22_country = $team22->get_country();
				$team22_score = $result_1_16_11->get_team2_score();
			}
		}

		$team23_name = '';
		$team23_country = '';
		$team23_score = '';
		if ( $result_1_16_12 ) {
			$team23 = $db->get_team_by_id ( $result_1_16_12->get_team1_id() );
			if ( $team23 ) {
				$team23_name = $this->get_name_or_placeholder( $result_1_16_12->get_team1_placeholder() , $team23 );
				$team23_country = $team23->get_country();
				$team23_score = $result_1_16_12->get_team1_score();
			}
		}
		
		$team24_name = '';
		$team24_country = '';
		$team24_score = '';
		if ( $result_1_16_12 ) {
			$team24 = $db->get_team_by_id ( $result_1_16_12->get_team2_id() );
			if ( $team24 ) {
				$team24_name = $this->get_name_or_placeholder( $result_1_16_12->get_team2_placeholder() , $team24 );
				$team24_country = $team24->get_country();
				$team24_score = $result_1_16_12->get_team2_score();
			}
		}
		
		$team25_name = '';
		$team25_country = '';
		$team25_score = '';
		if ( $result_1_16_13 ) {
			$team25 = $db->get_team_by_id ( $result_1_16_13->get_team1_id() );
			if ( $team25 ) {
				$team25_name = $this->get_name_or_placeholder( $result_1_16_13->get_team1_placeholder() , $team25 );
				$team25_country = $team25->get_country();
				$team25_score = $result_1_16_13->get_team1_score();
			}
		}

		$team26_name = '';
		$team26_country = '';
		$team26_score = '';
		if ( $result_1_16_13 ) {
			$team26 = $db->get_team_by_id ( $result_1_16_13->get_team2_id() );
			if ( $team26 ) {
				$team26_name = $this->get_name_or_placeholder( $result_1_16_13->get_team2_placeholder() , $team26 );
				$team26_country = $team26->get_country();
				$team26_score = $result_1_16_13->get_team2_score();
			}
		}

		$team27_name = '';
		$team27_country = '';
		$team27_score = '';
		if ( $result_1_16_14 ) {
			$team27 = $db->get_team_by_id ( $result_1_16_14->get_team1_id() );
			if ( $team27 ) {
				$team27_name = $this->get_name_or_placeholder( $result_1_16_14->get_team1_placeholder() , $team27 );
				$team27_country = $team27->get_country();
				$team27_score = $result_1_16_14->get_team1_score();
			}
		}
		
		$team28_name = '';
		$team28_country = '';
		$team28_score = '';
		if ( $result_1_16_14 ) {
			$team28 = $db->get_team_by_id ( $result_1_16_14->get_team2_id() );
			if ( $team28 ) {
				$team28_name = $this->get_name_or_placeholder( $result_1_16_14->get_team2_placeholder() , $team28 );
				$team28_country = $team28->get_country();
				$team28_score = $result_1_16_14->get_team2_score();
			}
		}
		
		$team29_name = '';
		$team29_country = '';
		$team29_score = '';
		if ( $result_1_16_15 ) {
			$team29 = $db->get_team_by_id ( $result_1_16_15->get_team1_id() );
			if ( $team29 ) {
				$team29_name = $this->get_name_or_placeholder( $result_1_16_15->get_team1_placeholder() , $team29 );
				$team29_country = $team29->get_country();
				$team29_score = $result_1_16_15->get_team1_score();
			}
		}

		$team30_name = '';
		$team30_country = '';
		$team30_score = '';
		if ( $result_1_16_15 ) {
			$team30 = $db->get_team_by_id ( $result_1_16_15->get_team2_id() );
			if ( $team30 ) {
				$team30_name = $this->get_name_or_placeholder( $result_1_16_15->get_team2_placeholder() , $team30 );
				$team30_country = $team30->get_country();
				$team30_score = $result_1_16_15->get_team2_score();
			}
		}

		$team31_name = '';
		$team31_country = '';
		$team31_score = '';
		if ( $result_1_16_16 ) {
			$team31 = $db->get_team_by_id ( $result_1_16_16->get_team1_id() );
			if ( $team31 ) {
				$team31_name = $this->get_name_or_placeholder( $result_1_16_16->get_team1_placeholder() , $team31 );
				$team31_country = $team31->get_country();
				$team31_score = $result_1_16_16->get_team1_score();
			}
		}
		
		$team32_name = '';
		$team32_country = '';
		$team32_score = '';
		if ( $result_1_16_16 ) {
			$team32 = $db->get_team_by_id ( $result_1_16_16->get_team2_id() );
			if ( $team32 ) {
				$team32_name = $this->get_name_or_placeholder( $result_1_16_16->get_team2_placeholder() , $team32 );
				$team32_country = $team32->get_country();
				$team32_score = $result_1_16_16->get_team2_score();
			}
		}

		return
		'<div class="round round-of-32">
		<span class="round-label">1/16 Finals</span>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team1_score < $team2_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team1_country . '"></span><span class="label">' . $team1_name . '</span><span class="score"> ' . $team1_score . '</span></div>
			  <div class="participant ' . ($team2_score < $team1_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team2_country . '"></span><span class="label">' . $team2_name . '</span><span class="score"> ' . $team2_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team3_score < $team4_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team3_country . '"></span><span class="label">' . $team3_name . '</span><span class="score"> ' . $team3_score . '</span></div>
			  <div class="participant ' . ($team4_score < $team3_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team4_country . '"></span><span class="label">' . $team4_name . '</span><span class="score"> ' . $team4_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
		<div class="single-bracket">
		  <div class="matchups">
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team5_score < $team6_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team5_country . '"></span><span class="label">' . $team5_name . '</span><span class="score"> ' . $team5_score . '</span></div>
			  <div class="participant ' . ($team6_score < $team5_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team6_country . '"></span><span class="label">' . $team6_name . '</span><span class="score"> ' . $team6_score . '</span></div>
			  </div>
			</div>
			<div class="matchup">
			  <div class="participants">
			  <div class="participant ' . ($team7_score < $team8_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team7_country . '"></span><span class="label">' . $team7_name . '</span><span class="score"> ' . $team7_score . '</span></div>
			  <div class="participant ' . ($team8_score < $team7_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team8_country . '"></span><span class="label">' . $team8_name . '</span><span class="score"> ' . $team8_score . '</span></div>
			  </div>
			</div>
		  </div>
		  <div class="connector">
			<div class="merger"></div>
			<div class="line"></div>
		  </div>
		</div>
		<div class="single-bracket">
			<div class="matchups">
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team9_score < $team10_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team9_country . '"></span><span class="label">' . $team9_name . '</span><span class="score"> ' . $team9_score . '</span></div>
				<div class="participant ' . ($team10_score < $team9_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team10_country . '"></span><span class="label">' . $team10_name . '</span><span class="score"> ' . $team10_score . '</span></div>
				</div>
			  </div>
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team11_score < $team12_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team11_country . '"></span><span class="label">' . $team11_name . '</span><span class="score"> ' . $team11_score . '</span></div>
				<div class="participant ' . ($team12_score < $team11_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team12_country . '"></span><span class="label">' . $team12_name . '</span><span class="score"> ' . $team12_score . '</span></div>
				</div>
			  </div>
			</div>
			<div class="connector">
			  <div class="merger"></div>
			  <div class="line"></div>
			</div>
		</div>
		  <div class="single-bracket">
			<div class="matchups">
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team13_score < $team14_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team13_country . '"></span><span class="label">' . $team13_name . '</span><span class="score"> ' . $team13_score . '</span></div>
				<div class="participant ' . ($team14_score < $team13_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team14_country . '"></span><span class="label">' . $team14_name . '</span><span class="score"> ' . $team14_score . '</span></div>
				</div>
			  </div>
			  <div class="matchup">
				<div class="participants">
				<div class="participant ' . ($team15_score < $team16_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team15_country . '"></span><span class="label">' . $team15_name . '</span><span class="score"> ' . $team15_score . '</span></div>
				<div class="participant ' . ($team16_score < $team15_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team16_country . '"></span><span class="label">' . $team16_name . '</span><span class="score"> ' . $team16_score . '</span></div>
				</div>
			  </div>
			</div>
			<div class="connector">
			  <div class="merger"></div>
			  <div class="line"></div>
			</div>
		  </div>
		  <div class="single-bracket">
			  <div class="matchups">
				<div class="matchup">
				  <div class="participants">
				  <div class="participant ' . ($team17_score < $team18_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team17_country . '"></span><span class="label">' . $team17_name . '</span><span class="score"> ' . $team17_score . '</span></div>
				  <div class="participant ' . ($team18_score < $team17_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team18_country . '"></span><span class="label">' . $team18_name . '</span><span class="score"> ' . $team18_score . '</span></div>
				  </div>
				</div>
				<div class="matchup">
				  <div class="participants">
				  <div class="participant ' . ($team19_score < $team20_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team19_country . '"></span><span class="label">' . $team19_name . '</span><span class="score"> ' . $team19_score . '</span></div>
				  <div class="participant ' . ($team20_score < $team19_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team20_country . '"></span><span class="label">' . $team20_name . '</span><span class="score"> ' . $team20_score . '</span></div>
				  </div>
				</div>
			  </div>
			  <div class="connector">
				<div class="merger"></div>
				<div class="line"></div>
			  </div>
			</div>
			<div class="single-bracket">
			  <div class="matchups">
				<div class="matchup">
				  <div class="participants">
				  <div class="participant ' . ($team21_score < $team22_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team21_country . '"></span><span class="label">' . $team21_name . '</span><span class="score"> ' . $team21_score . '</span></div>
				  <div class="participant ' . ($team22_score < $team21_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team22_country . '"></span><span class="label">' . $team22_name . '</span><span class="score"> ' . $team22_score . '</span></div>
				  </div>
				</div>
				<div class="matchup">
				  <div class="participants">
				  <div class="participant ' . ($team23_score < $team24_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team23_country . '"></span><span class="label">' . $team23_name . '</span><span class="score"> ' . $team23_score . '</span></div>
				  <div class="participant ' . ($team24_score < $team23_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team24_country . '"></span><span class="label">' . $team24_name . '</span><span class="score"> ' . $team24_score . '</span></div>
				  </div>
				</div>
			  </div>
			  <div class="connector">
				<div class="merger"></div>
				<div class="line"></div>
			  </div>
			</div>
			<div class="single-bracket">
				<div class="matchups">
				  <div class="matchup">
					<div class="participants">
					<div class="participant ' . ($team25_score < $team26_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team25_country . '"></span><span class="label">' . $team25_name . '</span><span class="score"> ' . $team25_score . '</span></div>
					<div class="participant ' . ($team26_score < $team25_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team26_country . '"></span><span class="label">' . $team26_name . '</span><span class="score"> ' . $team26_score . '</span></div>
					</div>
				  </div>
				  <div class="matchup">
					<div class="participants">
					<div class="participant ' . ($team27_score < $team28_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team27_country . '"></span><span class="label">' . $team27_name . '</span><span class="score"> ' . $team27_score . '</span></div>
					<div class="participant ' . ($team28_score < $team27_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team28_country . '"></span><span class="label">' . $team28_name . '</span><span class="score"> ' . $team28_score . '</span></div>
					</div>
				  </div>
				</div>
				<div class="connector">
				  <div class="merger"></div>
				  <div class="line"></div>
				</div>
			</div>
			  <div class="single-bracket">
				<div class="matchups">
				  <div class="matchup">
					<div class="participants">
					<div class="participant ' . ($team29_score < $team30_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team29_country . '"></span><span class="label">' . $team29_name . '</span><span class="score"> ' . $team29_score . '</span></div>
					<div class="participant ' . ($team30_score < $team29_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team30_country . '"></span><span class="label">' . $team30_name . '</span><span class="score"> ' . $team30_score . '</span></div>
					</div>
				  </div>
				  <div class="matchup">
					<div class="participants">
					<div class="participant ' . ($team31_score < $team32_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team31_country . '"></span><span class="label">' . $team31_name . '</span><span class="score"> ' . $team31_score . '</span></div>
					<div class="participant ' . ($team32_score < $team31_score ? 'loser' : 'winner') . '"><span class="flag-icon flag-icon-' . $team32_country . '"></span><span class="label">' . $team32_name . '</span><span class="score"> ' . $team32_score . '</span></div>
					</div>
				  </div>
				</div>
				<div class="connector">
				  <div class="merger"></div>
				  <div class="line"></div>
				</div>
			  </div>
	</div>';
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
			),
			$atts,
			'ekc-swiss-system'
		);
		$tournament_code_name = $atts['tournament'];
		$is_show_ranking =  filter_var( $atts['ranking'], FILTER_VALIDATE_BOOLEAN );
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
			return $this->create_swiss_ranking_table( $tournament );
		}
		return $this->create_swiss_rounds( $tournament, $max_rounds );
	}

	private function create_swiss_rounds( $tournament, $max_rounds ) {
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
				$html .= $this->create_swiss_round_table( $tournament, $results_for_round, $round );
			}
		}
		return $html;
	}

	private function create_swiss_ranking_table( $tournament ) {
		$db = new Ekc_Database_Access();
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$counter = 1;
		$current_ranking = $db->get_current_swiss_system_ranking( $tournament->get_tournament_id() );
		
		$header = array();
		$header[] = array('<span class="dashicons dashicons-awards"></span>', 'ekc-column-rank');
		$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
		$header[] = array($is_single_player ? 'Player' : 'Team', 'ekc-column-team');
		$header[] = array('Score', 'ekc-column-score');
		$html_header = $this->html_table_head($header);
		$html_body = '';

		foreach ( $current_ranking as $ranking ) {
			$team = $db->get_team_by_id( $ranking->get_team_id() );
			$is_excluded = intval( $team->get_virtual_rank() ) !== 0;
			$row = array();
			$row[] = $counter;
			$row[] = $this->html_flag( esc_html($team->get_country()) );
			$row[] = esc_html($team->get_name());
			$row[] = strval( $ranking->get_total_score() ) . '&nbsp;/&nbsp;' . strval( $ranking->get_opponent_score() );
			$html_body .= $this->html_table_row( $row, 'rank-' . $counter, $is_excluded );
			$counter++;
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_table( $html_header . $html_body );
	}

	private function create_swiss_round_table( $tournament, $results_for_round, $round, $score_as_input = false ) {
		$db = new Ekc_Database_Access();
		$is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
		$is_additional_round = $round > $tournament->get_swiss_system_rounds();

		$header = array();
		$header[] = array('Pitch', 'ekc-column-pitch');
		$header[] = array('<span class="dashicons dashicons-flag"></span>', 'ekc-column-country');
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
			$row[] = $team1_country;
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
				$team1_score = $this->html_score_input( $team1_score, 'team1-score-' . $result->get_result_id() );
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
			$row[] = $team2_country;
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
				$team2_score = $this->html_score_input( $team2_score, 'team2-score-' . $result->get_result_id() );
			}
			$row[] = $team2_score;
			$html_body .= $this->html_table_row( $row, '', false, 'rowspan-omit' );
		}
		$html_body = $this->html_table_body($html_body);
		
		return $this->html_table( $html_header . $html_body );
	}

	private function html_score_input( $score_value, $html_id ) {
		return '<input id="' . $html_id . '" name="' . $html_id . '" type="number" step="any" value="' . $score_value . '" />';
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
			),
			$atts,
			'ekc-link'
		);

		$link_id = ( isset($_GET['linkid'] ) ) ? sanitize_text_field( wp_unslash( $_GET['linkid'] ) ) : '';
		$page_id = ( isset($_GET['page_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page_id'] ) ) : '';
		
		$url_path = '?'. ($page_id ? 'page_id=' . $page_id . '&' : '') . 'linkid=' . $link_id;

		$db = new Ekc_Database_Access();
		$team = $db->get_team_by_shareable_link_id( $link_id );

		if ( $atts['type'] === 'team-name') {
			if ( $team ) {
				return $team->get_name();
			}
			else {
				return '';
			}
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
			$html .= $this->create_current_round_result( $tournament, $current_round_result, $current_round, $url_path );
		}	

		for ( $round = $current_round - 1; $round > 0; $round-- ) {
			$result_for_round = $this->get_results_for_round( $all_results, $round, $team->get_team_id() );
			if (count( $result_for_round ) > 0) {
				$html .= '<h3>Round ' . $round  . '</h3>';
				$html .= $this->create_swiss_round_table( $tournament, $result_for_round, $round );
			}
		}
		return $html;
	}

	private function create_current_round_result( $tournament, $current_round_result, $current_round, $url_path ) {
		$html = '<form class="ekc-form" method="post" action="' . $url_path . '" accept-charset="utf-8">';
		$html .= $this->create_swiss_round_table( $tournament, $current_round_result, $current_round, true );
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
