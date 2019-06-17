<?php

/**
 * Tournament result data object 
 */
class Ekc_Result implements JsonSerializable {

	private $result_id;
	private $tournament_id;
	private $team1_id;
	private $team2_id;
	private $team1_placeholder;
	private $team2_placeholder;
	private $team1_score;
	private $team2_score;
	private $pitch;
	private $stage;
	private $tournament_round;
	private $result_type;
	private $is_virtual_result;

	public function get_result_id() {
		return $this->result_id;
	}

	public function set_result_id(int $result_id) {
		$this->result_id = $result_id;
	}

	public function get_tournament_id() {
		return $this->tournament_id;
	}

	public function set_tournament_id(int $tournament_id) {
		$this->tournament_id = $tournament_id;
	}

	public function get_team1_id() {
		return $this->team1_id;
	}

	public function set_team1_id(?int $team1_id) {
		$this->team1_id = $team1_id;
	}

	public function get_team2_id() {
		return $this->team2_id;
	}

	public function set_team2_id(?int $team2_id) {
		$this->team2_id = $team2_id;
	}

	public function get_team1_placeholder() {
		return $this->team1_placeholder;
	}

	public function set_team1_placeholder(string $team1_placeholder) {
		$this->team1_placeholder = $team1_placeholder;
	}

	public function get_team2_placeholder() {
		return $this->team2_placeholder;
	}

	public function set_team2_placeholder(string $team2_placeholder) {
		$this->team2_placeholder = $team2_placeholder;
	}

	public function get_team1_score() {
		return $this->team1_score;
	}

	public function set_team1_score(?int $team1_score) {
		$this->team1_score = $team1_score;
	}

	public function get_team2_score() {
		return $this->team2_score;
	}

	public function set_team2_score(?int $team2_score) {
		$this->team2_score = $team2_score;
	}

	public function get_pitch() {
		return $this->pitch;
	}

	public function set_pitch(string $pitch) {
		$this->pitch = $pitch;
	}

	public function get_stage() {
		return $this->stage;
	}

	public function set_stage(string $stage) {
		$this->stage = $stage;
	}

	public function get_tournament_round() {
		return $this->tournament_round;
	}

	public function set_tournament_round(?int $tournament_round) {
		$this->tournament_round = $tournament_round;
	}

	public function get_result_type() {
		return $this->result_type;
	}

	public function set_result_type(string $result_type) {
		$this->result_type = $result_type;
	}

	public function is_virtual_result() {
		return $this->is_virtual_result;
	}

	public function set_virtual_result(bool $is_virtual_result) {
		$this->is_virtual_result = $is_virtual_result;
	}

	public function jsonSerialize(){
        return get_object_vars( $this );
    }
}

