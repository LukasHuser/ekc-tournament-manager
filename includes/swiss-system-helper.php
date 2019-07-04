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
			foreach ( $groups as $group ) {
				$matchups = array_merge( $matchups, $helper->match_slide( $group, $all_results ));
			}
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

	private function match_slide( $group, $all_results ) {
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

	private function match_top( $group, $all_results ) {
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

	private function played_already( $team1, $team2, $all_results ) {
		foreach ( $all_results as $result ) {
			if ( (intval( $result->get_team1_id() ) === intval($team1->get_team_id()) and intval($result->get_team2_id()) === intval($team2->get_team_id()) )
			  or (intval($result->get_team1_id()) === intval($team2->get_team_id()) and intval($result->get_team2_id()) === intval($team1->get_team_id()) )) {
				return true;
			  }
		}
		return false;
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
}



