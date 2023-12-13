<?php

/**
 * Helper class for elimination brackets
 */
class Ekc_Elimination_Bracket_Helper {

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
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_1,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_2,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_3,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_4,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_5,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_6,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_7,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_8,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_9,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_10,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_11,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_12,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_13,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_14,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_15,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_16);

	const BRACKET_RESULT_TYPES_1_8_FINALS = array(	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_1,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_2,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_3,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_4,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_5,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_6,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_7,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_8);
		
	const BRACKET_RESULT_TYPES_1_4_FINALS = array(	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_1,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_2,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_3,	
		Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_4);

	const BRACKET_RESULT_TYPES_1_2_FINALS = array(
		Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_1, 	
		Ekc_Elimination_Bracket_Helper::BRACKET_SEMIFINALS_2);	

	const BRACKET_RESULT_TYPES_FINALS = array(
		Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_1,		
		Ekc_Elimination_Bracket_Helper::BRACKET_FINALS_2);	
	

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
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS );
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4 ) {
			return array_merge(
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS); 
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8 ) {
			return array_merge(
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS);
		}
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16 ) {
			return array_merge(
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS,
				Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_16_FINALS);
		}
		return array();
	}

	public static function is_1_16_finals( $result_type ) {
		return in_array( $result_type, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_18_FINALS );
	}

	public static function is_1_8_finals( $result_type ) {
		return in_array( $result_type, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS );
	}

	public static function is_1_4_finals( $result_type ) {
		return in_array( $result_type, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS );
	}

	public static function is_1_2_finals( $result_type ) {
		return in_array( $result_type, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS );
	}

	public static function is_finals( $result_type ) {
		return in_array( $result_type, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS );
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

		// round of 8
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_1 ) {
			return array("1", "8");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_2 ) {
			return array("5", "4");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_3 ) {
			return array("3", "6");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_4_FINALS_4 ) {
			return array("7", "2");
		}

		// round of 16
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_1 ) {
			return array("1", "16");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_2 ) {
			return array("9", "8");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_3 ) {
			return array("5", "12");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_4 ) {
			return array("13", "4");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_5 ) {
			return array("3", "14");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_6 ) {
			return array("11", "6");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_7 ) {
			return array("7", "10");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_8_FINALS_8 ) {
			return array("15", "2");
		}

		// round of 32
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_1 ) {
			return array("1", "32");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_2 ) {
			return array("17", "16");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_3 ) {
			return array("9", "24");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_4 ) {
			return array("25", "8");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_5 ) {
			return array("5", "28");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_6 ) {
			return array("21", "12");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_7 ) {
			return array("13", "20");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_8 ) {
			return array("29", "4");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_9 ) {
			return array("3", "30");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_10 ) {
			return array("19", "14");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_11 ) {
			return array("11", "22");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_12 ) {
			return array("27", "6");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_13 ) {
			return array("7", "26");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_14 ) {
			return array("23", "10");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_15 ) {
			return array("15", "18");
		}
		if ( $result_type === Ekc_Elimination_Bracket_Helper::BRACKET_1_16_FINALS_16 ) {
			return array("31", "2");
		}		

		// unknown result type
		return array();
	}
}



