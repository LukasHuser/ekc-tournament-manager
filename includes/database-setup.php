<?php

/**
 * Database schema setup
 */
class Ekc_Database_Setup {

	const DATABASE_VERSION_OPTION = 'ekc_database_version';

	public function check_database_version() {
		$database_installed_version = get_option(self::DATABASE_VERSION_OPTION);
		
		if ( $database_installed_version != EKC_DATABASE_VERSION ) {
			$this->setup_database();
		}
	}

	public function setup_database() {
		global $wpdb;

		$tournament_table = $wpdb->prefix . "ekc_tournament";
		$tournament_round_table = $wpdb->prefix . "ekc_tournament_round";
		$team_table       = $wpdb->prefix . "ekc_team";
		$player_table     = $wpdb->prefix . "ekc_player";
		$result_table     = $wpdb->prefix . "ekc_result";


		$charset_collate = $wpdb->get_charset_collate();

		$tournament_table_sql = 
			"CREATE TABLE $tournament_table (
			tournament_id integer(10) NOT NULL AUTO_INCREMENT,
			code_name varchar(50) NOT NULL,			
			name varchar(500) NOT NULL,
			tournament_date date,
			max_teams integer(10),
			team_size varchar(20),
			is_wait_list_enabled bit(1),
			is_player_names_required bit(1),
			is_auto_backup_enabled bit(1),
			tournament_system varchar(20),
			elimination_rounds varchar(20),
			elimination_max_points_per_round integer(10),
			swiss_system_rounds integer(10),
			swiss_system_max_points_per_round integer(10),
			swiss_system_additional_rounds integer(10),
			swiss_system_slide_match_rounds integer(10),
			swiss_system_round_time integer(10),
			swiss_system_tiebreak_time integer(10),
			swiss_system_start_pitch integer(10),
			swiss_system_pitch_limit integer(10),
			shareable_link_url_prefix varchar(500),
			shareable_link_email_text varchar(5000),
			shareable_link_sender_email varchar(500),
			PRIMARY KEY  (tournament_id),
			UNIQUE KEY uc_code_name (code_name)
		) $charset_collate;";

		$tournament_round_table_sql = 
			"CREATE TABLE $tournament_round_table (
			tournament_id integer(10) NOT NULL,
			tournament_round integer(10) NOT NULL,
			round_start_time timestamp,
			PRIMARY KEY  (tournament_id, tournament_round)
		) $charset_collate;";

		$team_table_sql = 
			"CREATE TABLE $team_table (
			team_id integer(10) NOT NULL AUTO_INCREMENT,
			tournament_id integer(10) NOT NULL,
			name varchar(500) NOT NULL,
			country varchar(20),
			club varchar(500),
			is_active bit(1),
			email varchar(500),
			phone varchar(50),
			registration_date datetime,
			camping_count integer(10), 
			breakfast_count integer(10),
			is_registration_fee_paid bit(1),
			is_on_wait_list bit(1),
			registration_order double precision,
			seeding_score double precision,
			initial_score double precision,
			virtual_rank integer(10),
			shareable_link_id varchar(50),
			PRIMARY KEY  (team_id),
			KEY i_tournament_id (tournament_id)
		) $charset_collate;";

		$player_table_sql = 
			"CREATE TABLE $player_table (
			player_id integer(10) NOT NULL AUTO_INCREMENT,
			team_id integer(10) NOT NULL,
			last_name varchar(500) NOT NULL,
			first_name varchar(500),
			country varchar(20),
			is_active bit(1),
			is_captain bit(1),		
			PRIMARY KEY  (player_id),
			KEY i_team_id (team_id)
		) $charset_collate;";

		$result_table_sql = 
			"CREATE TABLE $result_table (
			result_id integer(10) NOT NULL AUTO_INCREMENT,
			tournament_id integer(10) NOT NULL,
			team1_id integer(10),
			team2_id integer(10),
			team1_placeholder varchar(500),
			team2_placeholder varchar(500),
			team1_score double precision,
			team2_score double precision,
			pitch varchar(20),
			stage varchar(20),
			tournament_round integer(10),
			result_type varchar(20),
			is_virtual_result bit(1),
			PRIMARY KEY  (result_id),
			KEY i_tournament_id (tournament_id),
			KEY i_team1_id (team1_id),
			KEY i_team2_id (team2_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $tournament_table_sql );
		dbDelta( $tournament_round_table_sql );
		dbDelta( $team_table_sql );
		dbDelta( $player_table_sql );
		dbDelta( $result_table_sql );

		update_option(self::DATABASE_VERSION_OPTION, EKC_DATABASE_VERSION);
	}
}
