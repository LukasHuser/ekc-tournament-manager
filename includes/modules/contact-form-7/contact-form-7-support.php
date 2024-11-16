<?php

/**
 * Support for Contact Form 7 plugin
 */
class Ekc_Contact_Form_7_Support {

	/**
	 * Support for custom shortcode attribute:
	 * ekc-tournament
	 * ekc-active
	 * ekc-waitlist
	 */
	public function custom_shortcode_atts_wpcf7_filter( $out, $pairs, $atts ) {
	  $tournament_code_name_attr = 'ekc-tournament';
	  if ( isset( $atts[$tournament_code_name_attr] ) ) {
		$out[$tournament_code_name_attr] = $atts[$tournament_code_name_attr];
	  }
	 
	  $active_attr = 'ekc-active';
	  if ( isset( $atts[$active_attr] ) ) {
		$out[$active_attr] = $atts[$active_attr];
	  }

	  $waitlist_attr = 'ekc-waitlist';
	  if ( isset( $atts[$waitlist_attr] ) ) {
		$out[$waitlist_attr] = $atts[$waitlist_attr];
	  }

	  return $out;
	}

	/**
	 * WPCF7_ADMIN_READ_WRITE_CAPABILITY is defined as 'publish_pages' (which is assigned to the EKC roles)
	 * WPCF7_ADMIN_READ_CAPABILITY is defined as 'edit_posts' (which is not assigned to the EKC roles)
	 */
	public function filter_wpcf7_map_meta_cap( $meta_caps ) {
		$meta_caps['wpcf7_read_contact_form'] = WPCF7_ADMIN_READ_WRITE_CAPABILITY;
		$meta_caps['wpcf7_read_contact_forms'] = WPCF7_ADMIN_READ_WRITE_CAPABILITY;
		return $meta_caps;
	}

	/**
	 * Callback to the wpcf7_submit action hook.
	 * Stores a team for a given tournament based on the provided form data.
	 */
	public function wpcf7_submit( $contact_form, $result ) {
	  if ( $contact_form->in_demo_mode() ) {
	    return;
	  }
	  if ( empty( $result['posted_data_hash'] ) ) {
	    return;
	  }
	
	  $submission = WPCF7_Submission::get_instance();
	  $tournament_code_name = $submission->get_posted_string( 'ekc-tournament' ) ;
	  if ( !$tournament_code_name ) {
		  return;
	  }
	  $db = new Ekc_Database_Access();
	  $tournament = $db->get_tournament_by_code_name( $tournament_code_name );
	  if ( !$tournament ) {
		  return;
	  }
	  
	  $team_active = filter_var( $submission->get_posted_string( 'ekc-active' ), FILTER_VALIDATE_BOOLEAN );
	  $waiting_list = filter_var( $submission->get_posted_string( 'ekc-waitlist' ), FILTER_VALIDATE_BOOLEAN );

	  // 1vs1 tournament
	  $first_name = $submission->get_posted_string( 'ekc-firstname' );
	  $last_name = $submission->get_posted_string( 'ekc-lastname' );
	  
	  // 3vs3 tournament
	  $team_name = $submission->get_posted_string( 'ekc-teamname' );
	  $first_name1 = $submission->get_posted_string( 'ekc-firstname1' );
	  $last_name1 = $submission->get_posted_string( 'ekc-lastname1' );
	  $first_name2 = $submission->get_posted_string( 'ekc-firstname2' );
	  $last_name2 = $submission->get_posted_string( 'ekc-lastname2' );
	  $first_name3 = $submission->get_posted_string( 'ekc-firstname3' );
	  $last_name3 = $submission->get_posted_string( 'ekc-lastname3' );
	  $first_name4 = $submission->get_posted_string( 'ekc-firstname4' );
	  $last_name4 = $submission->get_posted_string( 'ekc-lastname4' );
	  $first_name5 = $submission->get_posted_string( 'ekc-firstname5' );
	  $last_name5 = $submission->get_posted_string( 'ekc-lastname5' );
	  $first_name6 = $submission->get_posted_string( 'ekc-firstname6' );
	  $last_name6 = $submission->get_posted_string( 'ekc-lastname6' );

	  // common fields
	  $email = $submission->get_posted_string( 'ekc-email' );
	  $phone = $submission->get_posted_string( 'ekc-phone' );
	  $country = $submission->get_posted_string( 'ekc-country' );
	  $club = $submission->get_posted_string( 'ekc-club' );

	  $team = new Ekc_Team();
	  $team->set_tournament_id( $tournament->get_tournament_id() );
	  $team->set_registration_date( wp_date("Y-m-d H:i:s") ); // current date
	  $team->set_active( $team_active );
	  $team->set_on_wait_list( $waiting_list );
	  $team->set_email( $email );
	  $team->set_phone( $phone );
	  $team->set_country( $country );
	  $team->set_club( $club );

	  $players = array();
	  if ( $team_name ) {
		  $team->set_name( $team_name );
		  $players[] = $this->create_player( $first_name1, $last_name1, $country, true );
		  $players[] = $this->create_player( $first_name2, $last_name2, $country, false );
		  $players[] = $this->create_player( $first_name3, $last_name3, $country, false );
		  if ( $last_name4 ) {
			  $players[] = $this->create_player( $first_name4, $last_name4, $country, false );
		  }
		  if ( $last_name5 ) {
			  $players[] = $this->create_player( $first_name5, $last_name5, $country, false );
		  }
		  if ( $last_name6 ) {
			  $players[] = $this->create_player( $first_name6, $last_name6, $country, false );
		  }
	  }
	  else {
		  $team->set_name( $first_name . ' ' . $last_name );
		  $players[] = $this->create_player( $first_name, $last_name, $country, true );
	  }

	  $team->set_players( $players );
	  $db->insert_team( $team );
	}

	private function create_player( $first_name, $last_name, $country, $is_captain ) {
		$player = new Ekc_Player();
		$player->set_active( true );
		$player->set_first_name( $first_name );
		$player->set_last_name( $last_name );
		$player->set_country( $country );
		$player->set_captain( $is_captain );
		return $player;
	}
}
