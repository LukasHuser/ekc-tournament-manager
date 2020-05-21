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
		
		
		$matchups = array();
		if ( $next_round <= $tournament->get_swiss_system_slide_match_rounds() ) {
			// do match slide
			$groups = $helper->group_by_score( $current_ranking );
			$matchups = $helper->match_slide( $groups, $current_ranking, $all_results );

			///////////////
			// TODO Legacy
			// Remove after testing
//			foreach ( $groups as $group ) {
//				$matchups = array_merge( $matchups, $this->match_slide( $group, $all_results ));
//			}
		}
		else {
			// do match top
			$matchups = $helper->match_top( $current_ranking, $all_results );
		}

		$helper->store_matchups( $tournament_id, $next_round, $matchups, $virtual_matchups );
	}

	private function store_matchups( $tournament_id, $next_round, $matchups, $virtual_matchups ) {
		$db = new Ekc_Database_Access();
		$pitch = 1;

		foreach ( $virtual_matchups as $matchup ) {
			$result = $this->create_result( $tournament_id, $next_round, $matchup[0], $matchup[1]);
			$result->set_virtual_result( true );
			$result->set_pitch( strval( $pitch ) );
			$result->set_team1_score( 1 );
			$result->set_team2_score( 1 );
			
			$db->insert_tournament_result( $result );
			$pitch++;
		}

		foreach ( $matchups as $matchup ) {
			$result = $this->create_result( $tournament_id, $next_round, $matchup[0], $matchup[1]);
			$result->set_virtual_result( false );
			$result->set_pitch( strval( $pitch ) );

			$db->insert_tournament_result( $result );
			$pitch++;
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
		// start index of current group into total ranking
		$group_start_index = 0; 

		foreach ( $groups as $group ) {
			$this->assert_group( $group );

			// middle position within group
			$group_size = count( $group );
			$group_offset = intdiv( $group_size, 2);
			// loop over first half of the group and calculate pairings
			for ( $i = 0; $i < $group_offset; $i++ ) {
				$team1_index = $group_start_index + $i;
				$team1 = $current_ranking[$team1_index];
				// start at mid-point of the group and simultaneously go up and down in the ranking
				for ( $j = 0; $j < $group_offset; $j++ ) {
					$team2_index = $team1_index + $group_offset + $j;
					if ( $team2_index < $group_start_index + $group_size  ) {
						$team2 = $current_ranking[$team2_index];
						// $j is positive, we use a negative weight, because we want a minimum matching
						$initial_weight = -$j;
						$weight = $this->get_pairing_weight( $team1, $team2, $initial_weight, $all_results );
						$edges[] = array( $team1_index, $team2_index, $weight );
					}
					if ( $j != 0 ) {
						$team2_index = $team1_index + $group_offset - $j;
						if ( $team2_index > $team1_index ) {
							$team2 = $current_ranking[$team2_index];
							// $j is positive, we use a negative weight, because we want a minimum matching
							$initial_weight = -$j;
							$weight = $this->get_pairing_weight( $team1, $team2, $initial_weight, $all_results );
							$edges[] = array( $team1_index, $team2_index, $weight );
						}
					}
				}
			}
			// now we need a pairing for each team in the group with each other team down the whole ranking
			// we use a similar rule as simple "match top", but with an added penalty for a matching outside the own group
			for ( $i = 0; $i < $group_size; $i++ ) {
				$team1_index = $group_start_index + $i;
				$team1 = $current_ranking[$team1_index];

				// loop over whole ranking, downwards, outside own group
				$downward_ranking_size = count( $current_ranking ) - $group_start_index - $group_size;
				for ( $j = 0; $j < $downward_ranking_size; $j++ ) {
					$team2_index = $group_start_index + $group_size + $j;
					$team2 = $current_ranking[$team2_index];
					// penalty of 1000 for pairings outside own group
					// $i and $j are positive, we use a negative weight, because we want a minimum matching
					$initial_weight = -1000 - 1 - $i - $j;
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
				
				// Note: $j is strictly larger than $i, therefore $i - $j is always negative.
				// We use negative weights here, because the blossom matching algorithm
				// will calculate a maximum weight matching - but we want a minimum weight matching,
				// so we simply switch sign and use only negative values as weights 
				$initial_weight = $i - $j;
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
		if ( $this->played_already( $team1, $team2, $all_results ) ) {
			// add a penalty of 100000
			// because we use negative values, we actually subtract the penalty
			return $initial_weight - 100000;
		} 
		return $initial_weight;
	}

	private function get_matchups( $edges, $current_ranking ) {
		// maximum weight matching with blossom algorithm
		// we only consider maximum cardinality matchings,
		// so all vertices in the graph (i.e. all teams) are included in the matching
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

	private function get_top_teams_count( $tournament ) {
		if ( $tournamet->get_elimination_rounds() === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2) {
			return 4;
		}
		if ( $tournamet->get_elimination_rounds() === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4) {
			return 8;
		}
		if ( $tournamet->get_elimination_rounds() === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8) {
			return 16;
		}
		if ( $tournamet->get_elimination_rounds() === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16) {
			return 32;
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////
	// TODO Legacy functions
	// Remove after testing

	private function match_top_legacy( $group, $all_results ) {
		$this->assert_group( $group );
		$matchups = array();
		
		while ( count( $group ) > 0 ) {
			$team1 = array_shift($group);
			$found = false;
			for ($i = 0; $i < count($group); $i++) {
				if ( ! $found and ! $this->played_already( $team1, $group[$i], $all_results)) {
					$team2 = array_splice( $group, $i, 1)[0];
					$matchups[] = array( $team1, $team2 );
					$found = true;
				}
			}
			if ( ! $found) {
				// TODO manual correction? backtrack?
				$team2 = array_shift( $group );
				$matchups[] = array( $team1, $team2 );
			}
		}
		return $matchups;
	}

	private function match_slide_legacy( $group, $all_results ) {
		$this->assert_group( $group );
		$matchups = array();
		while ( count( $group ) > 0 ) {
			$team1 = array_shift( $group );
			$offsets = array_map(function($i) {if($i % 2 === 0) return $i; else return -$i;}, range( 0, count( $group ) - 1 ) );
			$index = intdiv( count( $group ), 2);
			$found = false;
			foreach ( $offsets as $offset ) {
				$index = $index + $offset;
				if ( ! $this->played_already( $team1, $group[$index], $all_results)) {
					$team2 = array_splice( $group, $index, 1)[0];
					$matchups[] = array($team1, $team2);
					$found = true;
					break;
				}
			}
			if ( ! $found) {
				// TODO manual correction? backtrack?
				// Maybe simply switch to match_top as a fallback?
				$team2 = array_shift( $group );
				$matchups[] = array( $team1, $team2 );
			}
		}
		return $matchups;
	}
}



