<?php

/**
 * Team data object 
 */
class Ekc_Team implements JsonSerializable {

	const TEAM_ID_BYE = 999999999;

	private $team_id;
	private $tournament_id;
	private $name;
	private $country;
	private $club;
	private $is_active;
	private $email;
	private $phone;
	private $registration_date;
	private $camping_count;
	private $breakfast_count;
	private $is_registration_fee_paid;
	private $is_on_wait_list;
	private $registration_order;
	private $seeding_score;
	private $initial_score;
	private $virtual_rank;
	private $shareable_link_id;
	private $players = array();


	public function get_team_id() {
		return $this->team_id;
	}

	public function set_team_id(int $team_id) {
		$this->team_id = $team_id;
	}

	public function get_tournament_id() {
		return $this->tournament_id;
	}

	public function set_tournament_id(int $tournament_id) {
		$this->tournament_id = $tournament_id;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name(string $name) {
		$this->name = $name;
	}

	public function get_country() {
		return $this->country;
	}

	public function set_country(string $country) {
		$this->country = $country;
	}

	public function get_club() {
		return $this->club;
	}

	public function set_club(string $club) {
		$this->club = $club;
	}

	public function is_active() {
		return $this->is_active;
	}

	public function set_active(bool $is_active) {
		$this->is_active = $is_active;
	}

	public function get_email() {
		return $this->email;
	}

	public function set_email(string $email) {
		$this->email = $email;
	}

	public function get_phone() {
		return $this->phone;
	}

	public function set_phone(string $phone) {
		$this->phone = $phone;
	}

	public function get_registration_date() {
		return $this->registration_date;
	}

	public function set_registration_date(string $registration_date) {
		$this->registration_date = $registration_date;
	}

	public function get_camping_count() {
		return $this->camping_count;
	}

	public function set_camping_count(?int $camping_count) {
		$this->camping_count = $camping_count;
	}

	public function get_breakfast_count() {
		return $this->breakfast_count;
	}

	public function set_breakfast_count(?int $breakfast_count) {
		$this->breakfast_count = $breakfast_count;
	}

	public function is_registration_fee_paid() {
		return $this->is_registration_fee_paid;
	}

	public function set_registration_fee_paid(bool $is_registration_fee_paid) {
		$this->is_registration_fee_paid = $is_registration_fee_paid;
	}
	
	public function is_on_wait_list() {
		return $this->is_on_wait_list;
	}

	public function set_on_wait_list(bool $is_on_wait_list) {
		$this->is_on_wait_list = $is_on_wait_list;
	}

	public function get_registration_order() {
		return $this->registration_order;
	}

	public function set_registration_order(?float $registration_order) {
		$this->registration_order = $registration_order;
	}

	public function get_seeding_score() {
		return $this->seeding_score;
	}

	public function set_seeding_score(?float $seeding_score) {
		$this->seeding_score = $seeding_score;
	}

	public function get_initial_score() {
		return $this->initial_score;
	}

	public function set_initial_score(?float $initial_score) {
		$this->initial_score = $initial_score;
	}

	public function get_virtual_rank() {
		return $this->virtual_rank;
	}

	public function set_virtual_rank(?int $virtual_rank) {
		$this->virtual_rank = $virtual_rank;
	}

	public function get_shareable_link_id() {
		return $this->shareable_link_id;
	}

	public function set_shareable_link_id(?string $shareable_link_id) {
		$this->shareable_link_id = $shareable_link_id;
	}

	public function get_players() {
		return $this->players;
	}

	/**
 	 * @param Ekc_Player[] $players
 	 */
	public function set_players($players) {
		$this->players = $players;
	}

	public function get_player($index) {
		if (count($this->players) > $index) {
			return $this->players[$index];
		}
		return false;
	}

	public function jsonSerialize(){
        return get_object_vars( $this );
    }
}

