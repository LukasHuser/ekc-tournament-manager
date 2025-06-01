<?php

/**
 * Helper class for elimination brackets
 */
class Ekc_Elimination_Bracket_Helper {

  const BRACKET_TYPE_GOLD = 'gold';
  const BRACKET_TYPE_SILVER = 'silver';

	// Result type constants
	// See: ekc_result.result_type 
	const BRACKET_FINALS_1 = 'ko-finals-1';
	const BRACKET_FINALS_2 = 'ko-finals-2'; // 3rd place
	const BRACKET_SEMIFINALS_1 = 'ko-semifinals-1';
	const BRACKET_SEMIFINALS_2 = 'ko-semifinals-2';
	const BRACKET_1_4_FINALS_1 = 'ko-1-4-finals-1';
	const BRACKET_1_4_FINALS_2 = 'ko-1-4-finals-2';
	const BRACKET_1_4_FINALS_3 = 'ko-1-4-finals-3';
	const BRACKET_1_4_FINALS_4 = 'ko-1-4-finals-4';
	const BRACKET_1_8_FINALS_1 = 'ko-1-8-finals-1';
	const BRACKET_1_8_FINALS_2 = 'ko-1-8-finals-2';
	const BRACKET_1_8_FINALS_3 = 'ko-1-8-finals-3';
	const BRACKET_1_8_FINALS_4 = 'ko-1-8-finals-4';
	const BRACKET_1_8_FINALS_5 = 'ko-1-8-finals-5';
	const BRACKET_1_8_FINALS_6 = 'ko-1-8-finals-6';
	const BRACKET_1_8_FINALS_7 = 'ko-1-8-finals-7';
	const BRACKET_1_8_FINALS_8 = 'ko-1-8-finals-8';
	const BRACKET_1_16_FINALS_1 = 'ko-1-16-finals-1';
	const BRACKET_1_16_FINALS_2 = 'ko-1-16-finals-2';
	const BRACKET_1_16_FINALS_3 = 'ko-1-16-finals-3';
	const BRACKET_1_16_FINALS_4 = 'ko-1-16-finals-4';
	const BRACKET_1_16_FINALS_5 = 'ko-1-16-finals-5';
	const BRACKET_1_16_FINALS_6 = 'ko-1-16-finals-6';
	const BRACKET_1_16_FINALS_7 = 'ko-1-16-finals-7';
	const BRACKET_1_16_FINALS_8 = 'ko-1-16-finals-8';
	const BRACKET_1_16_FINALS_9 = 'ko-1-16-finals-9';
	const BRACKET_1_16_FINALS_10 = 'ko-1-16-finals-10';
	const BRACKET_1_16_FINALS_11 = 'ko-1-16-finals-11';
	const BRACKET_1_16_FINALS_12 = 'ko-1-16-finals-12';
	const BRACKET_1_16_FINALS_13 = 'ko-1-16-finals-13';
	const BRACKET_1_16_FINALS_14 = 'ko-1-16-finals-14';
	const BRACKET_1_16_FINALS_15 = 'ko-1-16-finals-15';
	const BRACKET_1_16_FINALS_16 = 'ko-1-16-finals-16';
  const BRACKET_1_32_FINALS_1	= 'ko-1-32-finals-1';
	const BRACKET_1_32_FINALS_2	= 'ko-1-32-finals-2';
	const BRACKET_1_32_FINALS_3	= 'ko-1-32-finals-3';
	const BRACKET_1_32_FINALS_4	= 'ko-1-32-finals-4';
	const BRACKET_1_32_FINALS_5	= 'ko-1-32-finals-5';
	const BRACKET_1_32_FINALS_6	= 'ko-1-32-finals-6';
	const BRACKET_1_32_FINALS_7	= 'ko-1-32-finals-7';
	const BRACKET_1_32_FINALS_8	= 'ko-1-32-finals-8';
	const BRACKET_1_32_FINALS_9	= 'ko-1-32-finals-9';
	const BRACKET_1_32_FINALS_10 = 'ko-1-32-finals-10';
	const BRACKET_1_32_FINALS_11 = 'ko-1-32-finals-11';
	const BRACKET_1_32_FINALS_12 = 'ko-1-32-finals-12';
	const BRACKET_1_32_FINALS_13 = 'ko-1-32-finals-13';
	const BRACKET_1_32_FINALS_14 = 'ko-1-32-finals-14';
	const BRACKET_1_32_FINALS_15 = 'ko-1-32-finals-15';
	const BRACKET_1_32_FINALS_16 = 'ko-1-32-finals-16';
  const BRACKET_1_32_FINALS_17 = 'ko-1-32-finals-17';
	const BRACKET_1_32_FINALS_18 = 'ko-1-32-finals-18';
	const BRACKET_1_32_FINALS_19 = 'ko-1-32-finals-19';
	const BRACKET_1_32_FINALS_20 = 'ko-1-32-finals-20';
	const BRACKET_1_32_FINALS_21 = 'ko-1-32-finals-21';
	const BRACKET_1_32_FINALS_22 = 'ko-1-32-finals-22';
	const BRACKET_1_32_FINALS_23 = 'ko-1-32-finals-23';
	const BRACKET_1_32_FINALS_24 = 'ko-1-32-finals-24';
	const BRACKET_1_32_FINALS_25 = 'ko-1-32-finals-25';
	const BRACKET_1_32_FINALS_26 = 'ko-1-32-finals-26';
	const BRACKET_1_32_FINALS_27 = 'ko-1-32-finals-27';
	const BRACKET_1_32_FINALS_28 = 'ko-1-32-finals-28';
	const BRACKET_1_32_FINALS_29 = 'ko-1-32-finals-29';
	const BRACKET_1_32_FINALS_30 = 'ko-1-32-finals-30';
	const BRACKET_1_32_FINALS_31 = 'ko-1-32-finals-31';
	const BRACKET_1_32_FINALS_32 = 'ko-1-32-finals-32';

	const BRACKET_RESULT_TYPES_1_32_FINALS = array(	
    self::BRACKET_1_32_FINALS_1,	
		self::BRACKET_1_32_FINALS_2,	
		self::BRACKET_1_32_FINALS_3,	
		self::BRACKET_1_32_FINALS_4,	
		self::BRACKET_1_32_FINALS_5,	
		self::BRACKET_1_32_FINALS_6,	
		self::BRACKET_1_32_FINALS_7,	
		self::BRACKET_1_32_FINALS_8,	
		self::BRACKET_1_32_FINALS_9,	
		self::BRACKET_1_32_FINALS_10,	
		self::BRACKET_1_32_FINALS_11,	
		self::BRACKET_1_32_FINALS_12,	
		self::BRACKET_1_32_FINALS_13,	
		self::BRACKET_1_32_FINALS_14,	
		self::BRACKET_1_32_FINALS_15,	
		self::BRACKET_1_32_FINALS_16,
    self::BRACKET_1_32_FINALS_17,	
		self::BRACKET_1_32_FINALS_18,	
		self::BRACKET_1_32_FINALS_19,	
		self::BRACKET_1_32_FINALS_20,	
		self::BRACKET_1_32_FINALS_21,	
		self::BRACKET_1_32_FINALS_22,	
		self::BRACKET_1_32_FINALS_23,	
		self::BRACKET_1_32_FINALS_24,	
		self::BRACKET_1_32_FINALS_25,	
		self::BRACKET_1_32_FINALS_26,	
		self::BRACKET_1_32_FINALS_27,	
		self::BRACKET_1_32_FINALS_28,	
		self::BRACKET_1_32_FINALS_29,	
		self::BRACKET_1_32_FINALS_30,	
		self::BRACKET_1_32_FINALS_31,	
		self::BRACKET_1_32_FINALS_32);

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

  const NEXT_ROUND_RESULT_TYPES = array(
		// round of 8
		self::BRACKET_1_4_FINALS_1 => array( self::BRACKET_SEMIFINALS_1, 1 ),
		self::BRACKET_1_4_FINALS_2 => array( self::BRACKET_SEMIFINALS_1, 2 ),
		self::BRACKET_1_4_FINALS_3 => array( self::BRACKET_SEMIFINALS_2, 1 ),
		self::BRACKET_1_4_FINALS_4 => array( self::BRACKET_SEMIFINALS_2, 2 ),
		
		// round of 16
		self::BRACKET_1_8_FINALS_1 => array( self::BRACKET_1_4_FINALS_1, 1 ),
		self::BRACKET_1_8_FINALS_2 => array( self::BRACKET_1_4_FINALS_1, 2 ),
		self::BRACKET_1_8_FINALS_3 => array( self::BRACKET_1_4_FINALS_2, 1 ),
		self::BRACKET_1_8_FINALS_4 => array( self::BRACKET_1_4_FINALS_2, 2 ),
		self::BRACKET_1_8_FINALS_5 => array( self::BRACKET_1_4_FINALS_3, 1 ),
		self::BRACKET_1_8_FINALS_6 => array( self::BRACKET_1_4_FINALS_3, 2 ),
		self::BRACKET_1_8_FINALS_7 => array( self::BRACKET_1_4_FINALS_4, 1 ),
		self::BRACKET_1_8_FINALS_8 => array( self::BRACKET_1_4_FINALS_4, 2 ),

		// round of 32
		self::BRACKET_1_16_FINALS_1 => array( self::BRACKET_1_8_FINALS_1, 1 ),
		self::BRACKET_1_16_FINALS_2 => array( self::BRACKET_1_8_FINALS_1, 2 ),
		self::BRACKET_1_16_FINALS_3 => array( self::BRACKET_1_8_FINALS_2, 1 ),
		self::BRACKET_1_16_FINALS_4 => array( self::BRACKET_1_8_FINALS_2, 2 ),
		self::BRACKET_1_16_FINALS_5 => array( self::BRACKET_1_8_FINALS_3, 1 ),
		self::BRACKET_1_16_FINALS_6 => array( self::BRACKET_1_8_FINALS_3, 2 ),
		self::BRACKET_1_16_FINALS_7 => array( self::BRACKET_1_8_FINALS_4, 1 ),
		self::BRACKET_1_16_FINALS_8 => array( self::BRACKET_1_8_FINALS_4, 2 ),
		self::BRACKET_1_16_FINALS_9 => array( self::BRACKET_1_8_FINALS_5, 1 ),
		self::BRACKET_1_16_FINALS_10 => array( self::BRACKET_1_8_FINALS_5, 2 ),
		self::BRACKET_1_16_FINALS_11 => array( self::BRACKET_1_8_FINALS_6, 1 ),
		self::BRACKET_1_16_FINALS_12 => array( self::BRACKET_1_8_FINALS_6, 2 ),
		self::BRACKET_1_16_FINALS_13 => array( self::BRACKET_1_8_FINALS_7, 1 ),
		self::BRACKET_1_16_FINALS_14 => array( self::BRACKET_1_8_FINALS_7, 2 ),
		self::BRACKET_1_16_FINALS_15 => array( self::BRACKET_1_8_FINALS_8, 1 ),
		self::BRACKET_1_16_FINALS_16 => array( self::BRACKET_1_8_FINALS_8, 2 ),
  
  	// round of 64
    self::BRACKET_1_32_FINALS_1 => array( self::BRACKET_1_16_FINALS_1, 1 ),
    self::BRACKET_1_32_FINALS_2 => array( self::BRACKET_1_16_FINALS_1, 2 ),
    self::BRACKET_1_32_FINALS_3 => array( self::BRACKET_1_16_FINALS_2, 1 ),
    self::BRACKET_1_32_FINALS_4 => array( self::BRACKET_1_16_FINALS_2, 2 ),
    self::BRACKET_1_32_FINALS_5 => array( self::BRACKET_1_16_FINALS_3, 1 ),
    self::BRACKET_1_32_FINALS_6 => array( self::BRACKET_1_16_FINALS_3, 2 ),
    self::BRACKET_1_32_FINALS_7 => array( self::BRACKET_1_16_FINALS_4, 1 ),
    self::BRACKET_1_32_FINALS_8 => array( self::BRACKET_1_16_FINALS_4, 2 ),
    self::BRACKET_1_32_FINALS_9 => array( self::BRACKET_1_16_FINALS_5, 1 ),
    self::BRACKET_1_32_FINALS_10 => array( self::BRACKET_1_16_FINALS_5, 2 ),
    self::BRACKET_1_32_FINALS_11 => array( self::BRACKET_1_16_FINALS_6, 1 ),
    self::BRACKET_1_32_FINALS_12 => array( self::BRACKET_1_16_FINALS_6, 2 ),
    self::BRACKET_1_32_FINALS_13 => array( self::BRACKET_1_16_FINALS_7, 1 ),
    self::BRACKET_1_32_FINALS_14 => array( self::BRACKET_1_16_FINALS_7, 2 ),
    self::BRACKET_1_32_FINALS_15 => array( self::BRACKET_1_16_FINALS_8, 1 ),
    self::BRACKET_1_32_FINALS_16 => array( self::BRACKET_1_16_FINALS_8, 2 ),
		self::BRACKET_1_32_FINALS_17 => array( self::BRACKET_1_16_FINALS_9, 1 ),
		self::BRACKET_1_32_FINALS_18 => array( self::BRACKET_1_16_FINALS_9, 2 ),
		self::BRACKET_1_32_FINALS_19 => array( self::BRACKET_1_16_FINALS_10, 1 ),
		self::BRACKET_1_32_FINALS_20 => array( self::BRACKET_1_16_FINALS_10, 2 ),
		self::BRACKET_1_32_FINALS_21 => array( self::BRACKET_1_16_FINALS_11, 1 ),
		self::BRACKET_1_32_FINALS_22 => array( self::BRACKET_1_16_FINALS_11, 2 ),
		self::BRACKET_1_32_FINALS_23 => array( self::BRACKET_1_16_FINALS_12, 1 ),
		self::BRACKET_1_32_FINALS_24 => array( self::BRACKET_1_16_FINALS_12, 2 ),
		self::BRACKET_1_32_FINALS_25 => array( self::BRACKET_1_16_FINALS_13, 1 ),
		self::BRACKET_1_32_FINALS_26 => array( self::BRACKET_1_16_FINALS_13, 2 ),
		self::BRACKET_1_32_FINALS_27 => array( self::BRACKET_1_16_FINALS_14, 1 ),
		self::BRACKET_1_32_FINALS_28 => array( self::BRACKET_1_16_FINALS_14, 2 ),
		self::BRACKET_1_32_FINALS_29 => array( self::BRACKET_1_16_FINALS_15, 1 ),
		self::BRACKET_1_32_FINALS_30 => array( self::BRACKET_1_16_FINALS_15, 2 ),
		self::BRACKET_1_32_FINALS_31 => array( self::BRACKET_1_16_FINALS_16, 1 ),
		self::BRACKET_1_32_FINALS_32 => array( self::BRACKET_1_16_FINALS_16, 2 ));

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
		self::BRACKET_1_16_FINALS_16 => array(31, 2),
  
  	// round of 64
    self::BRACKET_1_32_FINALS_1 => array(1, 64),
    self::BRACKET_1_32_FINALS_2 => array(33, 32),
    self::BRACKET_1_32_FINALS_3 => array(17, 48),
    self::BRACKET_1_32_FINALS_4 => array(49, 16),
    self::BRACKET_1_32_FINALS_5 => array(9, 56),
    self::BRACKET_1_32_FINALS_6 => array(41, 24),
    self::BRACKET_1_32_FINALS_7 => array(25, 40),
    self::BRACKET_1_32_FINALS_8 => array(57, 8),
    self::BRACKET_1_32_FINALS_9 => array(5, 60),
    self::BRACKET_1_32_FINALS_10 => array(37, 28),
    self::BRACKET_1_32_FINALS_11 => array(21, 44),
    self::BRACKET_1_32_FINALS_12 => array(53, 12),
    self::BRACKET_1_32_FINALS_13 => array(13, 52),
    self::BRACKET_1_32_FINALS_14 => array(45, 20),
    self::BRACKET_1_32_FINALS_15 => array(29, 36),
    self::BRACKET_1_32_FINALS_16 => array(61, 4),
		self::BRACKET_1_32_FINALS_17 => array(3, 62),
		self::BRACKET_1_32_FINALS_18 => array(35, 30),
		self::BRACKET_1_32_FINALS_19 => array(19, 46),
		self::BRACKET_1_32_FINALS_20 => array(51, 14),
		self::BRACKET_1_32_FINALS_21 => array(11, 54),
		self::BRACKET_1_32_FINALS_22 => array(43, 22),
		self::BRACKET_1_32_FINALS_23 => array(27, 38),
		self::BRACKET_1_32_FINALS_24 => array(59, 6),
		self::BRACKET_1_32_FINALS_25 => array(7, 58),
		self::BRACKET_1_32_FINALS_26 => array(39, 26),
		self::BRACKET_1_32_FINALS_27 => array(23, 42),
		self::BRACKET_1_32_FINALS_28 => array(55, 10),
		self::BRACKET_1_32_FINALS_29 => array(15, 50),
		self::BRACKET_1_32_FINALS_30 => array(47, 18),
		self::BRACKET_1_32_FINALS_31 => array(31, 34),
		self::BRACKET_1_32_FINALS_32 => array(63, 2));

  const RANK_OFFSET = array(
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2   => 4,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4   => 8,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8   => 16,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16  => 32,
    Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32  => 64);

  public static function has_1_32_finals( $elimination_bracket ) {
    return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32;
  }

	public static function has_1_16_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32
      || $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16;
	}

	public static function has_1_8_finals( $elimination_bracket ) {
    return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32
      || $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8;
	}	

	public static function has_1_4_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4;
	}

	public static function has_1_2_finals( $elimination_bracket ) {
		return $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16
      || $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_8
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_4
			|| $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_2;
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
		if ( $elimination_bracket === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32 ) {
			return array_merge(
				self::BRACKET_RESULT_TYPES_FINALS,
				self::BRACKET_RESULT_TYPES_1_2_FINALS,
				self::BRACKET_RESULT_TYPES_1_4_FINALS,
				self::BRACKET_RESULT_TYPES_1_8_FINALS,
				self::BRACKET_RESULT_TYPES_1_16_FINALS,
        self::BRACKET_RESULT_TYPES_1_32_FINALS);
		}
		return array();
	}

	public static function is_1_32_finals( $result_type ) {
		return in_array( $result_type, self::BRACKET_RESULT_TYPES_1_32_FINALS );
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

  public static function get_next_round_result( $result_type ) {
		if ( array_key_exists( $result_type, self::NEXT_ROUND_RESULT_TYPES ) ) {
			return self::NEXT_ROUND_RESULT_TYPES[$result_type];
		}
		// unknown result type
		return array();
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

    if ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_32 ) {
			$this->insert_elimination_bracket_results( self::BRACKET_RESULT_TYPES_1_32_FINALS, $tournament_id, $stage, $rank_offset );
		}
		elseif ( $elimination_rounds === Ekc_Drop_Down_Helper::ELIMINATION_BRACKET_1_16 ) {
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



