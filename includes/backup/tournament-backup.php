<?php

/**
 * Tournament Backup Data Object
 * Complete data structure of a single tournament used
 * for import/export and backup of a tournament
 */
class Ekc_Tournament_Backup implements JsonSerializable {

	public const DATA_MODEL_VERSION = 2;

	private $version = Ekc_Tournament_Backup::DATA_MODEL_VERSION;
	private $export_date;
	private $tournament;
	private $teams = array();
	private $results = array();

	
	public function get_export_date() {
		return $this->export_date;
	}

	public function set_export_date(string $export_date) {
		$this->export_date = $export_date;
	}

	public function get_tournament() {
		return $this->tournament;
	}

	public function set_tournament(Ekc_Tournament $tournament) {
		$this->tournament = $tournament;
	}

	/**
 	 * @param Ekc_Team[] $teams
 	 */
	public function set_teams($teams) {
		$this->teams = $teams;
	}

	public function get_teams() {
		return $this->teams;
	}

	/**
 	 * @param Ekc_Result[] $results
 	 */
	public function set_results($results) {
		$this->results = $results;
	}

	public function get_results() {
		return $this->results;
	}

	public function set_version(int $version) {
		$this->version = $version;
	}

	public function get_version() {
		return $this->version;
	}

	public function jsonSerialize(){
        return get_object_vars( $this );
    }
}

