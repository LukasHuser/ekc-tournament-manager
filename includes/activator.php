<?php

/**
 * Fired during plugin activation
 */
class Ekc_Activator {

	public static function activate() {
		$database = new Ekc_Database_Setup();
		$database->setup_database();
	}
}
