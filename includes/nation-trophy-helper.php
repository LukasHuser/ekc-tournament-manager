<?php

/**
 * Helper class for nation trophy
 */
class Ekc_Nation_Trophy_Helper {

	// Nation trophy tournament types
	const NATION_TROPHY_TOURNAMENT_TYPE_1VS1 = '1vs1';
	const NATION_TROPHY_TOURNAMENT_TYPE_3VS3 = '3vs3';
	const NATION_TROPHY_TOURNAMENT_TYPE_6VS6 = '6vs6';

	/**
	 * Collects nation trophy results for a given tournament.
	 * This function is called for multiple tournaments, such as a 1vs1, 3vs3 and 6vs6 tournament, making up a single EKC event.
	 * Results are collected for each tournament through parameters, which are passed by reference.
	 * 
	 * $tournament_code_name: code name of a 6vs6, 3vs3 or 1vs1 EKC tournament
	 * $tournament_type: tournament type: 6vs6, 3vs3 or 1vs1. One of the constants: Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_1VS1 etc.
	 * $country_total_score: map from country_code to total score of the country, passed by reference, populated by this function
	 * $country_teams: map from country_code to list of team_id, passed by reference, populated by this function, adding teams at the end of the list
	 * $team_description: map from team_id to Ekc_Nation_Trophy_Rank_Description, populated by this function
	 */
	public function collect_nation_trophy_results( $tournament_code_name, $tournament_type, &$country_total_score, &$country_teams, &$team_description ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( ! $tournament ) {
			return;
		}

		$results = $db->get_tournament_results( $tournament->get_tournament_id(), Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO, null, null );
		if ( ! $results ) {
			return;
		}

		$country_results = array(); // two dimensional map: country code => team_id => score of team
		foreach ( $results as $result ) {
			if ( $this->is_relevant_for_ranking( $result ) ) {
				$team1 = $db->get_team_by_id( $result->get_team1_id() );
				$team2 = $db->get_team_by_id( $result->get_team2_id() );
				// could both be false, if not played yet
				$winner_team1 = $result->get_team1_score() > $result->get_team2_score();
				$winner_team2 = $result->get_team2_score() > $result->get_team1_score(); 
				$team1_rank_description = $this->get_rank_description( $tournament_type, $result->get_result_type(), $team1->get_team_id(), $winner_team1 );
				$team2_rank_description = $this->get_rank_description( $tournament_type, $result->get_result_type(), $team2->get_team_id(), $winner_team2 );

				$this->update_country_results( $country_results, $team_description, $team1->get_country(), $team1_rank_description );
				$this->update_country_results( $country_results, $team_description, $team2->get_country(), $team2_rank_description );
			}
		}
		
		foreach ( $country_results as $country_code => $country_result ) {
			arsort( $country_result ); // sort descending by value, i.e. sort teams by score
			if ( ! array_key_exists( $country_code, $country_teams ) ) {
				$country_teams[$country_code] = array();
			}
			if ( ! array_key_exists( $country_code, $country_total_score ) ) {
				$country_total_score[$country_code] = 0;
			}
			$rank = 1;
			foreach ( $country_result as $team_id => $team_score ) {
				if ( $rank <= 1 // highest score counts for 1vs1, 3vs3, 6vs6
					// highest 3 scores count for 1vs1, 3vs3
					|| ( $tournament_type !== Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_6VS6 && $rank <= 3 ) ) {
					$country_total_score[$country_code] += $team_score;
				}
				else {
					$team_description[$team_id]->set_score( 0 );
				}
				$country_teams[$country_code][] = $team_id;
				$rank++;
			}
		}
	}

	/**
	 * $country_results: multidimensional map from country_code to team_id to score, passed by reference, populated by this function
	 * $team_description: map from team_id to Ekc_Nation_Trophy_Rank_Description, populated by this function
	 */
	private function update_country_results( &$country_results, &$team_description, $country, $rank_description ) {
		if ( $country && $rank_description->get_score() > 0 ) {
			if ( ! array_key_exists( $country, $country_results ) ) {
				$country_results[$country] = array();
			}
			if ( ! array_key_exists( $rank_description->get_team_id(), $country_results[$country] ) 
					|| $country_results[$country][$rank_description->get_team_id()] < $rank_description->get_score() ) {
				$country_results[$country][$rank_description->get_team_id()] = $rank_description->get_score();
				$team_description[$rank_description->get_team_id()] = $rank_description;
			}
		}
	}

	private function is_relevant_for_ranking( $result ) {
		if ( ! $result || ! $result->get_team1_id() || ! $result->get_team2_id() ) {
			return false;
		}

		$result_type = $result->get_result_type();
		return Ekc_Elimination_Bracket_Helper::is_1_8_finals( $result_type )
			|| Ekc_Elimination_Bracket_Helper::is_1_4_finals( $result_type )
			// no score for semi finals needed here, finals will cover rank 1 to 4
			|| Ekc_Elimination_Bracket_Helper::is_finals( $result_type );
	}

	/**
	 * Scores for each tournament category:
	 * 
	 * rank | 1vs1 | 3vs3 | 6vs6
	 *  1   | 1000 | 2000 | 1000
	 *  2   | 700  | 1400 | 700
	 *  3   | 500  | 1000 | 500
	 *  4   | 400  | 800  | 400
	 * 5-8  | 300  | 600  | 300
	 * 9-16 | 200  | 400  | 200
	 */
	private function get_rank_description( $tournament_type, $result_type, $team_id, $winner ) {
		$rank_description = new Ekc_Nation_Trophy_Rank_Description();
		$rank_description->set_team_id( $team_id );
		
		$score = 0;
		$result_type_description = '';
		// finals (rank 1 to 4)
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_1 ) {
			if ( $winner ) {
				// rank 1
				$score = 1000;
				$result_type_description = 'Winner';
			}
			else {
				// rank 2
				$score = 700;
				$result_type_description = '2nd Place';
			}
		}
		else if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_2 ) {
			if ( $winner ) {
				// rank 3
				$score = 500;
				$result_type_description = '3rd Place';
			}
			else {
				// rank 4
				$score = 400;
				$result_type_description = '4th Place';
			}
		}
		// no score for semifinal, there will be a score for finals (rank 1 to 4)
		// no score for winning a 1/4 final or 1/8 final, there will be a score for a higher result type
		else if ( !$winner && Ekc_Elimination_Bracket_Helper::is_1_4_finals( $result_type ) ) {
			// 1/4 finals
			$score = 300;
			$result_type_description = 'Round of 8';
		}
		else if ( !$winner && Ekc_Elimination_Bracket_Helper::is_1_8_finals( $result_type ) ) {
			// 1/8 finals
			$score = 200;
			$result_type_description = 'Round of 16';
		}

		if ( $tournament_type === Ekc_Nation_Trophy_Helper::NATION_TROPHY_TOURNAMENT_TYPE_3VS3 ) {
			$score *= 2;
		}
		
		$rank_description->set_score( $score );
		$rank_description->set_description( $tournament_type . ' ' . $result_type_description );
		return $rank_description;
	}
}



