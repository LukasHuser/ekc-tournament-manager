<?php

/**
 * Fired during plugin activation
 */
class Ekc_Activator {

	public static function activate() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database-setup.php';
		$database = new Ekc_Database_Setup();
		$database->setup_database();
	}
}
