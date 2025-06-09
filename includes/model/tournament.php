<?php

/**
 * Tournament data object 
 */
class Ekc_Tournament implements JsonSerializable {

	private $tournament_id;
	private $code_name;
	private $name;
	private $owner_user_id;
	private $date;
	private $max_teams;
	private $team_size;
	private $is_wait_list_enabled;
	private $is_player_names_required;
	private $is_auto_backup_enabled;
	private $is_check_in_enabled;
	private $tournament_system;
	private $elimination_rounds;
	private $elimination_silver_rounds;
	private $elimination_max_points_per_round;
	private $swiss_system_rounds;
	private $swiss_system_max_points_per_round;
	private $swiss_system_bye_points;
	private $swiss_system_virtual_result_points;
	private $swiss_system_additional_rounds;
	private $swiss_system_slide_match_rounds;
	private $swiss_system_round_time;
	private $swiss_system_tiebreak_time;
	private $swiss_system_start_pitch;
	private $swiss_system_pitch_limit;
	private $shareable_link_email_text;
	private $shareable_link_url_prefix;
	private $shareable_link_sender_email;

	public function get_tournament_id() {
		return $this->tournament_id;
	}

	public function set_tournament_id(int $tournament_id) {
		$this->tournament_id = $tournament_id;
	}

	public function get_code_name() {
		return $this->code_name;
	}

	public function set_code_name(string $code_name) {
		$this->code_name = $code_name;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name(string $name) {
		$this->name = $name;
	}

	public function get_owner_user_id() {
		return $this->owner_user_id;
	}

	public function set_owner_user_id(?int $owner_user_id) {
		$this->owner_user_id = $owner_user_id;
	}

	public function get_date() {
		return $this->date;
	}

	public function set_date(string $date) {
		$this->date = $date;
	}

	public function get_max_teams() {
		return $this->max_teams;
	}

	public function set_max_teams(?int $max_teams) {
		$this->max_teams = $max_teams;
	}

	public function get_team_size() {
		return $this->team_size;
	}

	public function set_team_size(string $team_size) {
		$this->team_size = $team_size;
	}

	public function is_wait_list_enabled() {
		return $this->is_wait_list_enabled;
	}

	public function set_wait_list_enabled(bool $is_wait_list_enabled) {
		$this->is_wait_list_enabled = $is_wait_list_enabled;
	}

	public function is_player_names_required() {
		return $this->is_player_names_required;
	}

	public function set_player_names_required(bool $is_player_names_required) {
		$this->is_player_names_required = $is_player_names_required;
	}

	public function is_auto_backup_enabled() {
		return $this->is_auto_backup_enabled;
	}

	public function set_auto_backup_enabled(bool $is_auto_backup_enabled) {
		$this->is_auto_backup_enabled = $is_auto_backup_enabled;
	}

	public function is_check_in_enabled() {
		return $this->is_check_in_enabled;
	}

	public function set_check_in_enabled(bool $is_check_in_enabled) {
		$this->is_check_in_enabled = $is_check_in_enabled;
	}

	public function get_tournament_system() {
		return $this->tournament_system;
	}

	public function set_tournament_system(string $tournament_system) {
		$this->tournament_system = $tournament_system;
	}
	
	public function get_elimination_rounds() {
		return $this->elimination_rounds;
	}
	
	public function set_elimination_rounds(string $elimination_rounds) {
		$this->elimination_rounds = $elimination_rounds;
	}

	public function get_elimination_silver_rounds() {
		return $this->elimination_silver_rounds;
	}
	
	public function set_elimination_silver_rounds(string $elimination_silver_rounds) {
		$this->elimination_silver_rounds = $elimination_silver_rounds;
	}
	
	public function get_elimination_max_points_per_round() {
		return $this->elimination_max_points_per_round;
	}

	public function set_elimination_max_points_per_round(?int $elimination_max_points_per_round) {
		$this->elimination_max_points_per_round = $elimination_max_points_per_round;
	}

	public function get_swiss_system_rounds() {
		return $this->swiss_system_rounds;
	}

	public function set_swiss_system_rounds(?int $swiss_system_rounds) {
		$this->swiss_system_rounds = $swiss_system_rounds;
	}

	public function get_swiss_system_max_points_per_round() {
		return $this->swiss_system_max_points_per_round;
	}

	public function set_swiss_system_max_points_per_round(?int $swiss_system_max_points_per_round) {
		$this->swiss_system_max_points_per_round = $swiss_system_max_points_per_round;
	}

	public function get_swiss_system_bye_points() {
		return $this->swiss_system_bye_points;
	}

	public function set_swiss_system_bye_points(?int $swiss_system_bye_points) {
		$this->swiss_system_bye_points = $swiss_system_bye_points;
	}

	public function get_swiss_system_virtual_result_points() {
		return $this->swiss_system_virtual_result_points;
	}

	public function set_swiss_system_virtual_result_points(?int $swiss_system_virtual_result_points) {
		$this->swiss_system_virtual_result_points = $swiss_system_virtual_result_points;
	}

	public function get_swiss_system_additional_rounds() {
		return $this->swiss_system_additional_rounds;
	}

	public function set_swiss_system_additional_rounds(?int $swiss_system_additional_rounds) {
		$this->swiss_system_additional_rounds = $swiss_system_additional_rounds;
	}

	public function get_swiss_system_slide_match_rounds() {
		return $this->swiss_system_slide_match_rounds;
	}

	public function set_swiss_system_slide_match_rounds(?int $swiss_system_slide_match_rounds) {
		$this->swiss_system_slide_match_rounds = $swiss_system_slide_match_rounds;
	}

	public function get_swiss_system_round_time() {
		return $this->swiss_system_round_time;
	}

	public function set_swiss_system_round_time(?int $swiss_system_round_time) {
		$this->swiss_system_round_time = $swiss_system_round_time;
	}

	public function get_swiss_system_tiebreak_time() {
		return $this->swiss_system_tiebreak_time;
	}

	public function set_swiss_system_tiebreak_time(?int $swiss_system_tiebreak_time) {
		$this->swiss_system_tiebreak_time = $swiss_system_tiebreak_time;
	}
	
	public function get_swiss_system_start_pitch() {
		return $this->swiss_system_start_pitch;
	}

	public function set_swiss_system_start_pitch(?int $swiss_system_start_pitch) {
		$this->swiss_system_start_pitch = $swiss_system_start_pitch;
	}
	
	public function get_swiss_system_pitch_limit() {
		return $this->swiss_system_pitch_limit;
	}

	public function set_swiss_system_pitch_limit(?int $swiss_system_pitch_limit) {
		$this->swiss_system_pitch_limit = $swiss_system_pitch_limit;
	}

	public function get_shareable_link_email_text() {
		return $this->shareable_link_email_text;
	}

	public function set_shareable_link_email_text(?string $shareable_link_email_text) {
		$this->shareable_link_email_text = $shareable_link_email_text;
	}

	public function get_shareable_link_url_prefix() {
		return $this->shareable_link_url_prefix;
	}

	public function set_shareable_link_url_prefix(?string $shareable_link_url_prefix) {
		$this->shareable_link_url_prefix = $shareable_link_url_prefix;
	}

	public function get_shareable_link_sender_email() {
		return $this->shareable_link_sender_email;
	}

	public function set_shareable_link_sender_email(?string $shareable_link_sender_email) {
		$this->shareable_link_sender_email = $shareable_link_sender_email;
	}

	public function jsonSerialize(){
        return get_object_vars( $this );
    }
}

