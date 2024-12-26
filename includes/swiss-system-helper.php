<?php

/**
 * Helper class for Swiss System
 */
class Ekc_Swiss_System_Helper {

	public static function calculate_and_store_next_round( $tournament, $next_round ) {
		$total_rounds = $tournament->get_swiss_system_rounds() + $tournament->get_swiss_system_additional_rounds();
		if ( $next_round > $total_rounds ) {
			return; // no more rounds to play
		}

		$db = new Ekc_Database_Access();
		$helper = new Ekc_Swiss_System_Helper();
		$tournament_id = $tournament->get_tournament_id();
		$is_additional_round = $next_round > $tournament->get_swiss_system_rounds();

		$current_ranking = $helper->get_current_ranking( $tournament_id, $next_round );
		$all_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, null );

		$virtual_matchups = array();
		if ( $is_additional_round ) {
			// special handling for additional ranking rounds, where the top players/teams
			// are taken out for an alimination bracket.
			// Add 'virtual' results and score points. We need those virtual results so that teams
			// that played against the top teams can still improve their buchholz score and are
			// not punished for having played against a top team.
			$current_ranking_without_top_teams = array();
			$team1 = null;
			$team2 = null;
			
			foreach( $current_ranking as $team ) {
				if ( intval( $team->get_virtual_rank() ) !== 0  ) {
					if ( $team1 == null ) {
						$team1 = $team;
					}
					elseif ( $team2 == null ) {
						$team2 = $team;
					}
					if ( $team1 != null && $team2 != null ) {
						$matchup = array( $team1, $team2 );
						$virtual_matchups[] = $matchup;
						$team1 = null;
						$team2 = null;
					}
				}
				else {
					$current_ranking_without_top_teams[] = $team;
				}
			}
			$current_ranking = $current_ranking_without_top_teams;
		}
		
		// special pitch limit mode with additional BYEs if number of teams exceeds available pitches
		$bye_matchups = array();
		if ( $helper->is_pitch_limit_mode( $tournament ) ) {
			$teams_count = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			$byes_per_round = $teams_count - ($tournament->get_swiss_system_pitch_limit() * 2);
			$total_number_of_byes = $byes_per_round * $tournament->get_swiss_system_rounds();
			$teams_without_bye_count = $teams_count - $total_number_of_byes;
			$teams_without_bye = array();
			if ( $teams_without_bye_count > 0 ) {
	 			// The top seeded teams in the tournament do not get a BYE
				$teams_without_bye = $db->get_teams_ordered_by_seeding( $tournament->get_tournament_id(), $teams_without_bye_count );
			}

			// highest ranked teams according to current ranking get a BYE, if they weren't matched against a BYE already
			$current_ranking_without_byes = array();
			$additional_bye_id = Ekc_Team::TEAM_ID_BYE + 1;
			$byes_left = $byes_per_round;
			
			foreach( $current_ranking as $team ) {
				if ( $team->get_team_id() === Ekc_Team::TEAM_ID_BYE ) {
					continue;
				}
				if ( $byes_left > 0 && $helper->has_bye( $team, $teams_without_bye, $all_results ) ) {
					$additional_bye =  new Ekc_Swiss_System_Team();
					$additional_bye->set_team_id( $additional_bye_id );
					$additional_bye->set_score( 0 );
					$additional_bye->set_opponent_score( 0 );
					$additional_bye_id++;
					$matchup = array( $team, $additional_bye );
					$bye_matchups[] = $matchup;
					$byes_left--;
				}
				else {
					$current_ranking_without_byes[] = $team;
				}
			}
			$current_ranking = $current_ranking_without_byes;
		}

		$matchups = array();
		if ( $next_round <= $tournament->get_swiss_system_slide_match_rounds() ) {
			// do match slide
			$groups = $helper->group_by_score( $current_ranking );
			$matchups = $helper->match_slide( $groups, $current_ranking, $all_results );
		}
		else {
			// do match top
			$matchups = $helper->match_top( $current_ranking, $all_results );
		}

		$helper->store_matchups( $tournament, $next_round, $matchups, $virtual_matchups, $bye_matchups );
	}

	private function store_matchups( $tournament, $next_round, $matchups, $virtual_matchups, $bye_matchups ) {
		$db = new Ekc_Database_Access();
		$tournament_id = $tournament->get_tournament_id();
		$pitch = 1;
		if ( $tournament->get_swiss_system_start_pitch() ) {
			$pitch = intval( $tournament->get_swiss_system_start_pitch() );
		}
		$virtual_result_score = 1; // legacy fallback if no virtual result score is defined
		if ( !is_null( $tournament->get_swiss_system_virtual_result_points() ) ) {
			$virtual_result_score = $tournament->get_swiss_system_virtual_result_points();
		}

		foreach ( $virtual_matchups as $matchup ) {
			$result = $this->create_result( $tournament_id, $next_round, $matchup[0], $matchup[1]);
			$result->set_virtual_result( true );
			$result->set_pitch( strval( $pitch ) );
			$result->set_team1_score( $virtual_result_score );
			$result->set_team2_score( $virtual_result_score );
			
			$db->insert_tournament_result( $result );
			$pitch++;
		}

		foreach ( $matchups as $matchup ) {
			$result = $this->create_result( $tournament_id, $next_round, $matchup[0], $matchup[1]);
			$result->set_virtual_result( false );
			$result->set_pitch( strval( $pitch ) );
			if ( $result->get_team1_id() === Ekc_Team::TEAM_ID_BYE && !is_null( $tournament->get_swiss_system_bye_points() ) ) {
				$result->set_team1_score( 0 );
				$result->set_team2_score( $tournament->get_swiss_system_bye_points() );
			}
			else if ( $result->get_team2_id() === Ekc_Team::TEAM_ID_BYE && !is_null( $tournament->get_swiss_system_bye_points() ) ) {
				$result->set_team1_score( $tournament->get_swiss_system_bye_points() );
				$result->set_team2_score( 0 );
			}

			$db->insert_tournament_result( $result );
			$pitch++;
		}

		foreach ( $bye_matchups as $matchup ) {
			$result = $this->create_result( $tournament_id, $next_round, $matchup[0], $matchup[1]);
			$result->set_virtual_result( false );
			$result->set_pitch( '-' );
			if ( !is_null( $tournament->get_swiss_system_bye_points() ) ) {
				// team1 is a regular team, team2 is a bye
				$result->set_team1_score( $tournament->get_swiss_system_bye_points() );
				$result->set_team2_score( 0 );
			}

			$db->insert_tournament_result( $result );
		}
	}
	
	private function create_result( $tournament_id, $round, $team1, $team2 ) {
		$result = new Ekc_Result();
		$result->set_tournament_id( $tournament_id );
		$result->set_team1_id( $team1->get_team_id() );
		$result->set_team2_id( $team2->get_team_id() );
		$result->set_tournament_round( $round );
		$result->set_stage( Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS);
		return $result;
	}


	private function get_current_ranking( $tournament_id, $next_round ) {
		$db = new Ekc_Database_Access();
		$ranking = array();
		if ( intval( $next_round ) === 1 ) {
			// consider all active teams, ignoring the waiting list or maximum number of teams for the tournament
			// teams on the waiting list must have been set to inactive before starting the tournament
			$ranking = $db->get_initial_swiss_system_ranking( $tournament_id );
		}
		else {
			$ranking = $db->get_current_swiss_system_ranking( $tournament_id );
		}
		if ( count( $ranking ) % 2 === 1) {
			// add dummy team BYE
			$bye = new Ekc_Swiss_System_Team();
			$bye->set_team_id( Ekc_Team::TEAM_ID_BYE );
			$bye->set_score( 0 );
			$bye->set_opponent_score( 0 );
			$ranking[] = $bye;
		}
		return $ranking;
	}

	private function group_by_score( $current_ranking ) {
		$groups = array();
		$current_score = $current_ranking[0]->get_score();
		$current_group = array();
		foreach( $current_ranking as $team ) {
			if (strval( $team->get_score() ) === strval( $current_score ) ) {
				$current_group[] = $team;
			}
			elseif (count($current_group) % 2 === 1) {
				$current_group[] = $team;
			}
			else {
				$groups[] = $current_group;
				$current_group = array($team);
				$current_score = $team->get_score();
			}
		}
		$groups[] = $current_group;
		return $groups;
	}

	private function assert_group( $group ) {
		if ( count( $group ) % 2 === 1) {
			// TODO raise some error...
		}
	}

	private function match_slide( $groups, $current_ranking, $all_results ) {
		$edges = array();
		$ranking_size = count( $current_ranking );
		// start index of current group into total ranking
		$group_start_index = 0; 

		foreach ( $groups as $group ) {
			$this->assert_group( $group );

			// middle position within group
			$group_size = count( $group );
			$group_offset = intdiv( $group_size, 2);
			// loop over the group and calculate pairing weights
			for ( $i = 0; $i < $group_size - 1; $i++ ) {
				$team1_index = $group_start_index + $i;
				$team1 = $current_ranking[$team1_index];
				for ( $j = $i+1; $j < $group_size; $j++ ) {
					$team2_index = $group_start_index + $j;
					$team2 = $current_ranking[$team2_index];
					
					// $team2_index - $team1_index ist always positive (distance in the ranking)
					// $team2_index - $team1_index - $group_offset is 0 in the middle of the group, and its absolute value gets higher, if further away from the middle (upwards and downwards in the ranking)
					$initial_weight = abs( $team2_index - $team1_index - $group_offset ); 
					$weight = $this->get_pairing_weight( $team1, $team2, $initial_weight, $all_results );
					$edges[] = array( $team1_index, $team2_index, $weight );
				}
			}

			// Now we need a pairing for each team in the group with each other team down the whole ranking.
			// We use the same rule as "match top", i.e. the weight is calculated from the distance in the ranking.
			for ( $i = 0; $i < $group_size; $i++ ) {
				$team1_index = $group_start_index + $i;
				$team1 = $current_ranking[$team1_index];

				// loop over whole ranking, downwards, outside own group
				$downward_ranking_size = $ranking_size - $group_start_index - $group_size;
				for ( $j = 0; $j < $downward_ranking_size; $j++ ) {
					$team2_index = $group_start_index + $group_size + $j;
					$team2 = $current_ranking[$team2_index];

					// calculate weight from distance in the ranking, always positive (> 0)
					// add penalty of $ranking_size for outside of group pairings (any large enough constant would do as well)
					$initial_weight = $team2_index - $team1_index + $ranking_size;
					$weight = $this->get_pairing_weight( $team1, $team2, $initial_weight, $all_results );
					$edges[] = array( $team1_index, $team2_index, $weight );
				}
			}

			$group_start_index += $group_size;
		}
		return $this->get_matchups( $edges, $current_ranking );
	}

	private function match_top( $current_ranking, $all_results ) {
		$this->assert_group( $current_ranking );
		$edges = array();
		
		$ranking_size = count( $current_ranking );
		for ( $i = 0; $i < $ranking_size - 1; $i++ ) {
			for ( $j = $i + 1; $j < $ranking_size; $j++ ) {
				$team1 = $current_ranking[$i];
				$team2 = $current_ranking[$j];
				
  				// calculate weight from distance in the ranking, always positive (> 0)
				$initial_weight = $j - $i;
				$weight = $this->get_pairing_weight( $team1, $team2, $initial_weight, $all_results );
				$edges[] = array( $i, $j, $weight );
			}
		}
		
		return $this->get_matchups( $edges, $current_ranking );
	}

	private function played_already( $team1, $team2, $all_results ) {
		foreach ( $all_results as $result ) {
			if ( (intval( $result->get_team1_id() ) === intval($team1->get_team_id()) and intval($result->get_team2_id()) === intval($team2->get_team_id()) )
			  or (intval($result->get_team1_id()) === intval($team2->get_team_id()) and intval($result->get_team2_id()) === intval($team1->get_team_id()) )) {
				return true;
			  }
		}
		return false;
	}

	private function get_pairing_weight( $team1, $team2, $initial_weight, $all_results ) {
		// The provided weight corresponds to the distance in the ranking.
		// With a linear weight function, multiple maximum weight matchings might exist with the same sum of weights, but largely differing weights.
		// To avoid this, we use a simple non-linear weight function: the square of the distance.
		$weight = $initial_weight * $initial_weight;
		
		if ( $this->played_already( $team1, $team2, $all_results ) ) {
			// Add a penalty of 100_000_000 (take care not to overflow on MAXINT).
			// Assuming a maximum number of players of 1000, maximum distance in the ranking graph is 1_000_000.
			$weight += 100_000_000;
		} 

		// The blossom algorithm calculates a maximum weight matching, we return negative weights here,
		// because we want a minimum weight matching.
		return -$weight;
	}

	private function get_matchups( $edges, $current_ranking ) {
		// Maximum weight matching with blossom algorithm.
		// We actually want a minimum weight matching, so all weights are negative.
		// We only consider maximum cardinality matchings,
		// so all vertices in the graph (i.e. all teams) are included in the matching.
		$mates = maxWeightMatching( $edges, true );
		$matchups = array();
		for ( $i = 0; $i < count( $mates ); $i++ ) {
			$mate1 = $i;
			$mate2 = $mates[$i];
			if ( $mates[$mate1] !== -1 && $mates[$mate2] !== -1 ) {
				$matchups[] = array( $current_ranking[$mate1], $current_ranking[$mate2] );
				$mates[$mate1] = -1;
				$mates[$mate2] = -1;
			}
		}
		return $matchups;
	}


	/***************************************************************************************************************************
	 * Pitch limit mode
	 ***************************************************************************************************************************/

	public function is_pitch_limit_mode( $tournament ) {
		$pitches = $tournament->get_swiss_system_pitch_limit();
		if ( $pitches ) {
			$db = new Ekc_Database_Access();
			$teams = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			return $teams > ($pitches * 2) + 1;
		}
		return false;
	}

	public function is_pitch_limit_valid( $tournament ) {
		$db = new Ekc_Database_Access();
		$teams_count = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
		$byes_per_round = $teams_count - ($tournament->get_swiss_system_pitch_limit() * 2);
		$total_number_of_byes = $byes_per_round * $tournament->get_swiss_system_rounds();

		return $teams_count >= $total_number_of_byes;
	}

	private function has_bye( $team, $teams_without_bye, $all_results ) {
		foreach( $teams_without_bye as $team_without_bye ) {
			if ( intval($team->get_team_id()) === intval($team_without_bye->get_team_id()) ) {
				return false;
			}
		}
		foreach( $all_results as $result ) {
			if ( intval($result->get_team1_id()) === intval($team->get_team_id()) && Ekc_Team::is_bye_id( $result->get_team2_id() ) ) {
				return false;
			}
			if ( intval($result->get_team2_id()) === intval($team->get_team_id()) && Ekc_Team::is_bye_id( $result->get_team1_id() ) ) {
				return false;
			} 
		}
		return true;
	}

	public function get_byes_for_pitch_limit_mode( $tournament ) {
		$db = new Ekc_Database_Access();
		if ( $this->is_pitch_limit_mode( $tournament ) ) {
			$teams_count = $db->get_active_teams_count_by_tournament_id( $tournament->get_tournament_id() );
			$byes_per_round = $teams_count - ($tournament->get_swiss_system_pitch_limit() * 2);
			$byes = array();
			$additional_bye_id = Ekc_Team::TEAM_ID_BYE + 1;
			for ($i=0; $i<$byes_per_round; $i++) {
				$additional_bye =  new Ekc_Team();
				$additional_bye->set_team_id( $additional_bye_id );
				$additional_bye->set_name( 'BYE ' . ($i + 1) );
				$byes[] = $additional_bye;
				$additional_bye_id++;
			}
			return $byes;
		}
		return array();
	}
}



