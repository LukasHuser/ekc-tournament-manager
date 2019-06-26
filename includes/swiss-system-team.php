<?php

/**
 * Team data object used for Swiss System calculation
 */
class Ekc_Swiss_System_Team {

	private $team_id;
	private $score;
	private $opponent_score;
	private $seeding_score;
	private $initial_score;
	private $virtual_rank;

	public function get_team_id() {
		return $this->team_id;
	}

	public function set_team_id($team_id) {
		$this->team_id = $team_id;
	}

	public function get_score() {
		return $this->score;
	}

	public function set_score($score) {
		$this->score = $score;
	}

	public function get_opponent_score() {
		return $this->opponent_score;
	}

	public function set_opponent_score($opponent_score) {
		$this->opponent_score = $opponent_score;
	}

	public function get_seeding_score() {
		return $this->seeding_score;
	}

	public function set_seeding_score($seeding_score) {
		$this->seeding_score = $seeding_score;
	}

	public function get_initial_score() {
		return $this->initial_score;
	}

	public function set_initial_score($initial_score) {
		$this->initial_score = $initial_score;
	}

	public function get_virtual_rank() {
		return $this->virtual_rank;
	}

	public function set_virtual_rank($virtual_rank) {
		$this->virtual_rank = $virtual_rank;
	}

	public function get_total_score() {
		return floatval( $this->score ) + floatval( $this->initial_score );
	}
}

