<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function delete_database() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/database-setup.php';
	$database = new Ekc_Database_Setup();
	$database->delete_database();
}

function delete_roles_and_capabilities() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/role-helper.php';
	$helper = new Ekc_Role_Helper();
	$helper->delete_roles_and_capabilities();
}

function delete_uploaded_files() {
	$upload_dir = wp_upload_dir();
	$upload_basedir = $upload_dir['basedir']; // /path/to/wp-content/uploads
	$ekc_upload_dir = $upload_basedir . '/ekc-tournament-manager';
	if ( file_exists( $ekc_upload_dir ) ) {
		require_once ( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once ( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		$fileSystem = new WP_Filesystem_Direct( false );
		$fileSystem->rmdir( $ekc_upload_dir, true );
	}
}

function delete_options() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/migration.php';
	delete_option(Ekc_Migration::PLUGIN_VERSION_OPTION);
}

delete_database();
delete_roles_and_capabilities();
delete_uploaded_files();
delete_options();