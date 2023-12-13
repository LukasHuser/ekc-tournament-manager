<?php

/**
 * Nation trophy rank description data object 
 */
class Ekc_Nation_Trophy_Rank_Description {

	private $team_id;
	private $description;
	private $score;


	public function get_team_id() {
		return $this->team_id;
	}

	public function set_team_id(int $team_id) {
		$this->team_id = $team_id;
	}

	public function get_description() {
		return $this->description;
	}

	public function set_description(string $description) {
		$this->description = $description;
	}

	public function get_score() {
		return $this->score;
	}

	public function set_score(int $score) {
		$this->score = $score;
	}
}

