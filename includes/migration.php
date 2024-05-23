<?php

/**
 * Migrations of persisted data (typically stored in the database).
 * Changes to the database schema are covered by Ekc_Database_Setup.
 */
class Ekc_Migration {

	const PLUGIN_VERSION_OPTION = 'ekc_plugin_version';

	public function migrate() {
		$plugin_installed_version = get_option(self::PLUGIN_VERSION_OPTION);
		if ( ! $plugin_installed_version ) {
			$plugin_installed_version = '0.0.0';
		}

		if ( version_compare( $plugin_installed_version, '2.2.0', '<' ) ) {
			$this->migrate_2_2_0_roles_and_capabilities();
		}
		
		if ( $plugin_installed_version != EKC_PLUGIN_VERSION ) {
			update_option(self::PLUGIN_VERSION_OPTION, EKC_PLUGIN_VERSION);
		}
	}

	private function migrate_2_2_0_roles_and_capabilities() {
		$helper = new Ekc_Role_Helper();
		$helper->init_roles_and_capabilities();
	}
}
