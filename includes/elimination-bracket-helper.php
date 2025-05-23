<?php

/**
 * Helper class for elimination brackets
 */
class Ekc_Elimination_Bracket_Helper {

  const BRACKET_TYPE_GOLD = 'gold';
  const BRACKET_TYPE_SILVER = 'silver';

	// Result type constants
	// See: ekc_result.result_type 
	const BRACKET_FINALS_1 		= "ko-finals-1";
	const BRACKET_FINALS_2		= "ko-finals-2"; // 3rd place
	const BRACKET_SEMIFINALS_1 	= "ko-semifinals-1";
	const BRACKET_SEMIFINALS_2 	= "ko-semifinals-2";
	const BRACKET_1_4_FINALS_1	= "ko-1/4-finals-1";
	const BRACKET_1_4_FINALS_2	= "ko-1/4-finals-2";
	const BRACKET_1_4_FINALS_3	= "ko-1/4-finals-3";
	const BRACKET_1_4_FINALS_4	= "ko-1/4-finals-4";
	const BRACKET_1_8_FINALS_1	= "ko-1/8-finals-1";
	const BRACKET_1_8_FINALS_2	= "ko-1/8-finals-2";
	const BRACKET_1_8_FINALS_3	= "ko-1/8-finals-3";
	const BRACKET_1_8_FINALS_4	= "ko-1/8-finals-4";
	const BRACKET_1_8_FINALS_5	= "ko-1/8-finals-5";
	const BRACKET_1_8_FINALS_6	= "ko-1/8-finals-6";
	const BRACKET_1_8_FINALS_7	= "ko-1/8-finals-7";
	const BRACKET_1_8_FINALS_8	= "ko-1/8-finals-8";
	const BRACKET_1_16_FINALS_1	= "ko-1/16-finals-1";
	const BRACKET_1_16_FINALS_2	= "ko-1/16-finals-2";
	const BRACKET_1_16_FINALS_3	= "ko-1/16-finals-3";
	const BRACKET_1_16_FINALS_4	= "ko-1/16-finals-4";
	const BRACKET_1_16_FINALS_5	= "ko-1/16-finals-5";
	const BRACKET_1_16_FINALS_6	= "ko-1/16-finals-6";
	const BRACKET_1_16_FINALS_7	= "ko-1/16-finals-7";
	const BRACKET_1_16_FINALS_8	= "ko-1/16-finals-8";
	const BRACKET_1_16_FINALS_9	= "ko-1/16-finals-9";
	const BRACKET_1_16_FINALS_10	= "ko-1/16-finals-10";
	const BRACKET_1_16_FINALS_11	= "ko-1/16-finals-11";
	const BRACKET_1_16_FINALS_12	= "ko-1/16-finals-12";
	const BRACKET_1_16_FINALS_13	= "ko-1/16-finals-13";
	const BRACKET_1_16_FINALS_14	= "ko-1/16-finals-14";
	const BRACKET_1_16_FINALS_15	= "ko-1/16-finals-15";
	const BRACKET_1_16_FINALS_16	= "ko-1/16-finals-16";

	const BRACKET_RESULT_TYPES_1_16_FINALS = array(	
		self::BRACKET_1_16_FINALS_1,	
		self::BRACKET_1_16_FINALS_2,	
		self::BRACKET_1_16_FINALS_3,	
		self::BRACKET_1_16_FINALS_4,	
		self::BRACKET_1_16_FINALS_5,	
		self::BRACKET_1_16_FINALS_6,	
		self::BRACKET_1_16_FINALS_7,	
		self::BRACKET_1_16_FINALS_8,	
		self::BRACKET_1_16_FINALS_9,	
		self::BRACKET_1_16_FINALS_10,	
		self::BRACKET_1_16_FINALS_11,	
		self::BRACKET_1_16_FINALS_12,	
		self::BRACKET_1_16_FINALS_13,	
		self::BRACKET_1_16_FINALS_14,	
		self::BRACKET_1_16_FINALS_15,	
		self::BRACKET_1_16_FINALS_16);

	const BRACKET_RESULT_TYPES_1_8_FINALS = array(	
		self::BRACKET_1_8_FINALS_1,	
		self::BRACKET_1_8_FINALS_2,	
		self::BRACKET_1_8_FINALS_3,	
		self::BRACKET_1_8_FINALS_4,	
		self::BRACKET_1_8_FINALS_5,	
		self::BRACKET_1_8_FINALS_6,	
		self::BRACKET_1_8_FINALS_7,	
		self::BRACKET_1_8_FINALS_8);
		
	const BRACKET_RESULT_TYPES_1_4_FINALS = array(	
		self::BRACKET_1_4_FINALS_1,	
		self::BRACKET_1_4_FINALS_2,	
		self::BRACKET_1_4_FINALS_3,	
		self::BRACKET_1_4_FINALS_4);

	const BRACKET_RESULT_TYPES_1_2_FINALS = array(
		self::BRACKET_SEMIFINALS_1, 	
		self::BRACKET_SEMIFINALS_2);	

	const BRACKET_RESULT_TYPES_FINALS = array(
		self::BRACKET_FINALS_1,		
		self::BRACKET_FINALS_2);

	const RANK_NUMBERS_BY_RESULT_TYPE = array(
		// semifinals
		self::BRACKET_SEMIFINALS_1 => array(1, 4),
		self::BRACKET_SEMIFINALS_2 => array(3, 2),

		// round of 8
		self::BRACKET_1_4_FINALS_1 => array(1, 8),
		self::BRACKET_1_4_FINALS_2 => array(5, 4),
		self::BRACKET_1_4_FINALS_3 => array(3, 6),
		self::BRACKET_1_4_FINALS_4 => array(7, 2),
		
		// round of 16
		self::BRACKET_1_8_FINALS_1 => array(1, 16),
		self::BRACKET_1_8_FINALS_2 => array(9, 8),
		self::BRACKET_1_8_FINALS_3 => array(5, 12),
		self::BRACKET_1_8_FINALS_4 => array(13, 4),
		self::BRACKET_1_8_FINALS_5 => array(3, 14),
		self::BRACKET_1_8_FINALS_6 => array(11, 6),
		self::BRACKET_1_8_FINALS_7 => array(7, 10),
		self::BRACKET_1_8_FINALS_8 => array(15, 2),

		// round of 32
		self::BRACKET_1_16_FINALS_1 => array(1, 32),
		self::BRACKET_1_16_FINALS_2 => array(17, 16),
		self::BRACKET_1_16_FINALS_3 => array(9, 24),
		self::BRACKET_1_16_FINALS_4 => array(25, 8),
		self::BRACKET_1_16_FINALS_5 => array(5, 28),
		self::BRACKET_1_16_FINALS_6 => array(21, 12),
		self::BRACKET_1_16_FINALS_7 => array(13, 20),
		self::BRACKET_1_16_FINALS_8 => array(29, 4),
		self::BRACKET_1_16_FINALS_9 => array(3, 30),
		self::BRACKET_1_16_FINALS_10 => array(19, 14),
		self::BRACKET_1_16_FINALS_11 => array(11, 22),
		self::BRACKET_1_16_FINALS_12 => array(27, 6),
		self::BRACKET_1_16_FINALS_13 => array(7, 26),
		self::BRACKET_1_16_FINALS_14 => array(23, 10),
		self::BRACKET_1_16_FINALS_15 => array(15, 18),
		self::BRACKET_1_16_FINALS_16 => array(31, 2));

  const RANK_OFFSET = array(
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2   => 4,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4   => 8,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8   => 16,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16  => 32);

	public static function has_1_16_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16;
	}

	public static function has_1_8_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8;
	}	

	public static function has_1_4_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4;
	}

	public static function has_1_2_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4
			or $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2;
	}

	public static function get_result_types ( $elimination_bracket ) {
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2 ) {
			return array_merge(
				self::BRACKET_RESULT_TYPES_FINALS,
				self::BRACKET_RESULT_TYPES_1_2_FINALS );
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4 ) {
			return array_merge(
				self::BRACKET_RESULT_TYPES_FINALS,
				self::BRACKET_RESULT_TYPES_1_2_FINALS,
				self::BRACKET_RESULT_TYPES_1_4_FINALS); 
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8 ) {
			return array_merge(
				self::BRACKET_RESULT_TYPES_FINALS,
				self::BRACKET_RESULT_TYPES_1_2_FINALS,
				self::BRACKET_RESULT_TYPES_1_4_FINALS,
				self::BRACKET_RESULT_TYPES_1_8_FINALS);
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16 ) {
			return array_merge(
				self::BRACKET_RESULT_TYPES_FINALS,
				self::BRACKET_RESULT_TYPES_1_2_FINALS,
				self::BRACKET_RESULT_TYPES_1_4_FINALS,
				self::BRACKET_RESULT_TYPES_1_8_FINALS,
				self::BRACKET_RESULT_TYPES_1_16_FINALS);
		}
		return array();
	}

	public static function is_1_16_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_1_18_FINALS );
	}

	public static function is_1_8_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_1_8_FINALS );
	}

	public static function is_1_4_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_1_4_FINALS );
	}

	public static function is_1_2_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_1_2_FINALS );
	}

	public static function is_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_FINALS );
	}

  public static function get_stage_for_bracket_type( $bracket_type ) {
		if ( $bracket_type === self::BRACKET_TYPE_SILVER ) {
      return Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO_SILVER;
    }
    return Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO; // default is gold bracket
	}

  public static function get_elimination_rounds_for_bracket_type( $tournament, $bracket_type ) {
		if ( $bracket_type === self::BRACKET_TYPE_SILVER ) {
      return $tournament->get_elimination_silver_rounds();
    }
    return $tournament->get_elimination_rounds(); // default is gold bracket
	}

	public static function get_result_for_result_type( $results, $result_type ) {
		foreach ( $results as $result ) {
		  if ( $result->get_result_Type() === $result_type ) {
			return $result;
		  }
		}
		return null;
	}

	public static function get_rank_numbers_for_result_type( $result_type ) {
		if ( array_key_exists( $result_type, self::RANK_NUMBERS_BY_RESULT_TYPE ) ) {
			return self::RANK_NUMBERS_BY_RESULT_TYPE[$result_type];
		}
		// unknown result type
		return array();
	}

  public static function get_rank_offset( $tournament, $bracket_type ) {
    if ( $bracket_type === self::BRACKET_TYPE_SILVER && $tournament->get_elimination_rounds() ) {
      return self::RANK_OFFSET[$tournament->get_elimination_rounds()];
    }
    return 0;
	}

	public function elimination_bracket_from_swiss_system_ranking( $tournament_id, $bracket_type ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
    $rank_offset = self::get_rank_offset( $tournament, $bracket_type );
    $stage = self::get_stage_for_bracket_type( $bracket_type );
    $elimination_rounds = self::get_elimination_rounds_for_bracket_type( $tournament, $bracket_type );
    
		if ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16 ) {
			$this->insert_elimination_bracket_results( self::BRACKET_RESULT_TYPES_1_16_FINALS, $tournament_id, $stage, $rank_offset );
		}
		elseif ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8 ) {
			$this->insert_elimination_bracket_results( self::BRACKET_RESULT_TYPES_1_8_FINALS, $tournament_id, $stage, $rank_offset );
		}
		elseif ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4 ) {
			$this->insert_elimination_bracket_results( self::BRACKET_RESULT_TYPES_1_4_FINALS, $tournament_id, $stage, $rank_offset );
		}
		elseif ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2 ) {
			$this->insert_elimination_bracket_results( self::BRACKET_RESULT_TYPES_1_2_FINALS, $tournament_id, $stage, $rank_offset );
		}
	}

	private function insert_elimination_bracket_results( $result_types, $tournament_id, $stage, $rank_offset ) {
		$db = new Ekc_Database_Access();
		$ranking = $db->get_current_swiss_system_ranking( $tournament_id );

		$pitch = 1 + ($rank_offset / 2);
		foreach( $result_types as $result_type ) {
			$rank_numbers = Ekc_Elimination_Bracket_Helper::get_rank_numbers_for_result_type( $result_type );

			// ranking array is 0 based, rank numbers are 1 based
			$team1_rank_index = $rank_numbers[0] - 1 + $rank_offset;
			$team2_rank_index = $rank_numbers[1] - 1 + $rank_offset;
			
			$result = new Ekc_Result();
			$result->set_stage( $stage );
			$result->set_result_type( $result_type );
			$result->set_tournament_id( $tournament_id );
			$result->set_team1_id( array_key_exists( $team1_rank_index, $ranking ) ? $ranking[$team1_rank_index]->get_team_id() : null );
			$result->set_team2_id( array_key_exists( $team2_rank_index, $ranking ) ? $ranking[$team2_rank_index]->get_team_id() : null );
			$result->set_pitch( strval( $pitch ) );
			$pitch++;

			$db->insert_tournament_result( $result );
		}
	}
}



