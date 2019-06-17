<?php

/**
 * Team data object used for Swiss System calculation
 */
class Ekc_Swiss_System_Team {

	private $team_id;
	private $score;
	private $opponent_score;
	private $seeding_score;
	private $is_excluded_top_team;

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

	public function is_excluded_top_team() {
		return $this->is_excluded_top_team;
	}

	public function set_excluded_top_team($is_excluded_top_team) {
		$this->is_excluded_top_team = $is_excluded_top_team;
	}
}

