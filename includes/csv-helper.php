<?php

/**
 * Helper class for csv export and import of teams
 */
class Ekc_Csv_Helper {

	const TEAM_ID = 'team_id';
	const NAME = 'name';
	const ACTIVE = 'active';
	const COUNTRY = 'country';
	const EMAIL = 'email';
	const PHONE = 'phone';
	const CLUB = 'club';
	const ORDER = 'order';
	const REGISTRATION_FEE_PAID = 'registration_fee_paid';
	const WAIT_LIST = 'wait_list';
	const SEEDING_SCORE = 'seeding_score';
	const PLAYER_1_FIRST_NAME = 'player1_first_name';
	const PLAYER_1_LAST_NAME = 'player1_last_name';
	const PLAYER_2_FIRST_NAME = 'player2_first_name';
	const PLAYER_2_LAST_NAME = 'player2_last_name';
	const PLAYER_3_FIRST_NAME = 'player3_first_name';
	const PLAYER_3_LAST_NAME = 'player3_last_name';
	const PLAYER_4_FIRST_NAME = 'player4_first_name';
	const PLAYER_4_LAST_NAME = 'player4_last_name';
	const PLAYER_5_FIRST_NAME = 'player5_first_name';
	const PLAYER_5_LAST_NAME = 'player5_last_name';
	const PLAYER_6_FIRST_NAME = 'player6_first_name';
	const PLAYER_6_LAST_NAME = 'player6_last_name';


	public function import_teams( $tournament_id, $csv_data ) {
    $csv_rows = preg_split( "/\r\n|\n|\r/", trim( $csv_data ) );
    $row_count = count( $csv_rows );
    if ( $row_count < 2 ) {
      return esc_html__( 'CSV data must contain at least 1 header row and 1 data row.', 'ekc-tournament-manager' );
    }
		$header_row = str_getcsv( $csv_rows[0], ';', '"', '' );
		if ( ! $this->validate_header_row( $header_row ) ) {	
      return esc_html__( 'CSV data contains an invalid header row.', 'ekc-tournament-manager' );
    }

    $validation_helper = new Ekc_Validation_Helper();
    $db = new Ekc_Database_Access();
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $current_date = wp_date( 'Y-m-d H:i:s' ); 
    $header_size = count( $header_row );

    $insert_count = 0;
    $update_count = 0;
    for ( $i = 1; $i < $row_count; $i++ ) {
      if ( strlen( trim( $csv_rows[$i] ) ) === 0 ) {
        // ignore empty rows
        continue; 
      }

      $team_data = array();
      $row = str_getcsv( $csv_rows[$i], ';', '"', '' );
      $row_size = min( count( $row ), $header_size );
      for ( $j = 0; $j < $row_size; $j++ ) {
        $team_data[$header_row[$j]] = $row[$j];
      }
      
      $team = null;
      $team_id = null;
      if ( array_key_exists( self::TEAM_ID, $team_data ) ) {
        $team_id = $validation_helper->validate_integer( $team_data[self::TEAM_ID] );
      }
      if ( $team_id ) {
        $existing_team = $db->get_team_by_id( $team_id );
        if ( $existing_team && $existing_team->get_tournament_id() === $tournament_id ) {
          $team = $existing_team;
        }
      }
      else {
        // if no team_id is provided, create a new team
        $team = new Ekc_Team();
        $team->set_tournament_id( $tournament_id );
        $team->set_registration_date( $current_date );
      }

      // ignore this row, if a team_id was provided, but does not match the tournament 
      if ( ! $team ) {
        continue;
      }

      if ( array_key_exists( self::NAME, $team_data ) ) {
        $team->set_name( $validation_helper->validate_text( $team_data[self::NAME] ) );
      }
      if ( array_key_exists( self::COUNTRY, $team_data ) ) {
        $team->set_country( $validation_helper->validate_text( $team_data[self::COUNTRY] ) );
      }
      if ( array_key_exists( self::CLUB, $team_data ) ) {
        $team->set_club( $validation_helper->validate_text( $team_data[self::CLUB] ) );
      }
      if ( array_key_exists( self::ACTIVE, $team_data ) ) {
        $team->set_active( $validation_helper->validate_boolean( $team_data[self::ACTIVE] ) );
      }
      if ( array_key_exists( self::EMAIL, $team_data ) ) {
        $team->set_email( $validation_helper->validate_text( $team_data[self::EMAIL] ) );
      }
      if ( array_key_exists( self::PHONE, $team_data ) ) {
        $team->set_phone( $validation_helper->validate_text( $team_data[self::PHONE] ) );
      }
      if ( array_key_exists( self::REGISTRATION_FEE_PAID, $team_data ) ) {
        $team->set_registration_fee_paid( $validation_helper->validate_boolean( $team_data[self::REGISTRATION_FEE_PAID] ) );
      }
      if ( array_key_exists( self::WAIT_LIST, $team_data ) ) {
        $team->set_on_wait_list( $validation_helper->validate_boolean( $team_data[self::WAIT_LIST] ) );
      }
      if ( array_key_exists( self::ORDER, $team_data ) ) {
        $team->set_registration_order( $validation_helper->validate_float( $team_data[self::ORDER] ) );
      }
      if ( array_key_exists( self::SEEDING_SCORE, $team_data ) ) {
        $team->set_seeding_score( $validation_helper->validate_float( $team_data[self::SEEDING_SCORE] ) );
      }

      // players
      $players = array();
      $this->add_player( $players, $team_data, self::PLAYER_1_FIRST_NAME, self::PLAYER_1_LAST_NAME, $team->get_country(), true );
      $this->add_player( $players, $team_data, self::PLAYER_2_FIRST_NAME, self::PLAYER_2_LAST_NAME, $team->get_country(), false );
      $this->add_player( $players, $team_data, self::PLAYER_3_FIRST_NAME, self::PLAYER_3_LAST_NAME, $team->get_country(), false );
      $this->add_player( $players, $team_data, self::PLAYER_4_FIRST_NAME, self::PLAYER_4_LAST_NAME, $team->get_country(), false );
      $this->add_player( $players, $team_data, self::PLAYER_5_FIRST_NAME, self::PLAYER_5_LAST_NAME, $team->get_country(), false );
      $this->add_player( $players, $team_data, self::PLAYER_6_FIRST_NAME, self::PLAYER_6_LAST_NAME, $team->get_country(), false );
      if ( count( $players ) > 0 ) {
        $team->set_players( $players );
      }

      if ( $team->get_team_id() ) {
        $update_count++;
        $db->update_team( $team );
      }
      else {
        $insert_count++;
        $db->insert_team( $team );
      }
    }

    return sprintf(
      /* translators: 1: number of imported rows 2: number of inserted teams 3: number of updated teams */ 
      esc_html__( 'CSV import: Imported %1$s rows. Created %2$s new teams, updated %3$s existing teams.', 'ekc-tournament-manager' ),
      $insert_count + $update_count ,$insert_count, $update_count );
	}

	private function validate_header_row( $header_row ) {
		if ( $header_row ) {
			foreach( $header_row as $header ) {
				if ( $header === self::TEAM_ID
				  || $header === self::NAME
				  || $header === self::PLAYER_1_FIRST_NAME ) {
					return true;
				}
			}
		}
		return false;
	}

  /**
	 * $players: passed by reference, populated by this function
   */
  private function add_player( &$players, $team_data, $first_name_key, $last_name_key, $country, $is_captain ) {
    $validation_helper = new Ekc_Validation_Helper();
    $first_name = null;
    $last_name = null;
    if ( array_key_exists( $first_name_key, $team_data ) ) {
      $first_name = $validation_helper->validate_text( $team_data[$first_name_key] );
    }
    if ( array_key_exists( $last_name_key, $team_data ) ) {
      $last_name = $validation_helper->validate_text( $team_data[$last_name_key] );
    }

    if ( $first_name  || $last_name ) {
      $player = new Ekc_Player();
      $player->set_first_name( $first_name );
      $player->set_last_name( $last_name );
      $player->set_country( $country );
      $player->set_captain( $is_captain );
      $player->set_active( true );
      $players[] = $player;
    }
	}
}

