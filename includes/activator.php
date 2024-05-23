<?php

/**
 * Fired during plugin activation
 */
class Ekc_Activator {

	public static function activate() {
		// setup database
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database-setup.php';
		$database = new Ekc_Database_Setup();
		$database->setup_database();

		// init custom roles and capabilities
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/role-helper.php';
		$helper = new Ekc_Role_Helper(); 
		$helper->init_roles_and_capabilities();

		// set plugin version option (do not run migrations on activation)
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/migration.php';
		update_option(Ekc_Migration::PLUGIN_VERSION_OPTION, EKC_PLUGIN_VERSION);
	}
}
