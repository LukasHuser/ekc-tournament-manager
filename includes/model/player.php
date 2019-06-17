<?php

/**
 * Player data object 
 */
class Ekc_Player implements JsonSerializable {

	private $player_id;
	private $last_name;
	private $first_name;
	private $country;
	private $is_active;
	private $is_captain;


	public function get_player_id() {
		return $this->player_id;
	}

	public function set_player_id(int $player_id) {
		$this->player_id = $player_id;
	}

	public function get_last_name() {
		return $this->last_name;
	}

	public function set_last_name(string $last_name) {
		$this->last_name = $last_name;
	}

	public function get_first_name() {
		return $this->first_name;
	}

	public function set_first_name(string $first_name) {
		$this->first_name = $first_name;
	}

	public function get_country() {
		return $this->country;
	}

	public function set_country(string $country) {
		$this->country = $country;
	}

	public function is_active() {
		return $this->is_active;
	}

	public function set_active(bool $is_active) {
		$this->is_active = $is_active;
	}

	public function is_captain() {
		return $this->is_captain;
	}

	public function set_captain(bool $is_captain) {
		$this->is_captain = $is_captain;
	}

	public function jsonSerialize(){
        return get_object_vars( $this );
    }
}

