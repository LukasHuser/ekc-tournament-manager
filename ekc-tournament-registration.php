<?php

/*
Plugin Name:  EKC Tournament Registration
Plugin URI:   
Description:  Allows registering teams for an EKC Kubb tournament and displaying registered teams.
			  While this plugin started as a simple team registration form, it now also features
			  displaying of live results for elimination bracket tournaments and full management
			  of a swiss system style tournament (with a few special rules for the EKC 1vs1 tournament).
Version:      1.2.6
Author:       EKA European Kubb Association
Author URI:   https://kubbeurope.com
License:      Apache License, Version 2.0
License URI:  http://www.apache.org/licenses/LICENSE-2.0

This plugin is licensed to you under the
Apache License, Version 2.0 (the "License"); 
you may not use this file except in compliance
with the License. You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing,
software distributed under the License is distributed on an
"AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
KIND, either express or implied.  See the License for the
specific language governing permissions and limitations
under the License.
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

define( 'EKC_PLUGIN_VERSION', '1.2.6' );
define( 'EKC_DATABASE_VERSION', '1.2.6' );

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

	$plugin = new Ekc_Tournament_Registration();
	$plugin->run();

}
ekc_run_plugin();
