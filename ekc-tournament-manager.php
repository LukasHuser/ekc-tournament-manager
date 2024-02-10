<?php

/*
Plugin Name:  EKC Tournament Manager
Plugin URI:   https://kubb.live/ekc-tournament-manager
Description:  Manage Swiss system style tournaments, handle registrations of teams and players.
			  Developed for the EKC European Kubb Championships.
Version:      2.1.1
Author:       Lukas Huser, EKA European Kubb Association
Author URI:   https://kubbeurope.com/about-eka
License:      GNU General Public License GPL v3 or later
License URI:  http://www.gnu.org/licenses/gpl-3.0.html


EKC Tournament Manager
Copyright (C) 2018-2023 Lukas Huser, EKA European Kubb Associaton

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see https://www.gnu.org/licenses/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

define( 'EKC_PLUGIN_VERSION', '2.1.1' );
define( 'EKC_DATABASE_VERSION', '2.1.0' );

// Load database setup class
require_once plugin_dir_path( __FILE__ ) . 'includes/database-setup.php';

function ekc_activate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
	Ekc_Activator::activate();
}

function ekc_deactivate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
	Ekc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_plugin' );

// Load plugin main class
require plugin_dir_path( __FILE__ ) . 'includes/plugin.php';

/**
 * Begins execution of the plugin.
 * Since everything is registered via hooks, starting the plugin at this point does
 * not affect the page life cycle.
 */
function ekc_run_plugin() {

	$plugin = new Ekc_Tournament_Manager();
	$plugin->run();

}
ekc_run_plugin();
