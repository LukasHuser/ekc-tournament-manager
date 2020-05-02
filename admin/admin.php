<?php

/**
 * The admin-specific functionality of the plugin.
 *
 */
class Ekc_Tournament_Registration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialization
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'jquery-ui-theme', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'selectmenu-flags', plugin_dir_url( __FILE__ ) . 'css/flag-icon.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'jquery-ui-selectmenu', 'jquery-ui-autocomplete' ), $this->version, false );

	}

	public function add_tournament_menu() {
	    add_menu_page(
	        'Tournaments',
	        'EKC Tournaments',
	        'manage_options',
	        'ekc-tournaments',
	        array( $this, 'create_tournaments_page' ),
		'dashicons-shield' );

	    add_submenu_page(
	        NULL,
			'Teams',
	        'EKC Teams',
	        'manage_options',
	        'ekc-teams',
			array( $this, 'create_teams_page' ) );

	    add_submenu_page(
	        NULL,
			'Elimination Bracket',
	        'EKC Elimination Bracket',
	        'manage_options',
	        'ekc-bracket',
			array( $this, 'create_bracket_page' ) );
			
	    add_submenu_page(
	        NULL,
			'Swiss System',
	        'EKC Swiss System',
	        'manage_options',
	        'ekc-swiss',
			array( $this, 'create_swiss_system_page' ) );

	    add_submenu_page(
	        NULL,
			'Shareable Links',
	        'EKC Shareable Links',
	        'manage_options',
	        'ekc-links',
	        array( $this, 'create_shareable_links_page' ) );

	    add_submenu_page(
	        NULL,
			'Backup',
	        'EKC Tournament Backup',
	        'manage_options',
	        'ekc-backup',
	        array( $this, 'create_tournaments_backup_page' ) );
	}

	public function create_tournaments_page() {
		$tournaments_page = new Ekc_Tournaments_Admin_Page();
		return $tournaments_page->create_tournaments_page();
	}

	public function create_teams_page() {
		$teams_page = new Ekc_Teams_Admin_Page();
		return $teams_page->create_teams_page();
	}

	public function create_bracket_page() {
		$bracket_page = new Ekc_Elimination_Bracket_Admin_Page();
		return $bracket_page->create_elimination_bracket_page();
	}

	public function create_swiss_system_page() {
		$swiss_system_page = new Ekc_Swiss_System_Admin_Page();
		return $swiss_system_page->create_swiss_system_page();
	}

	public function create_shareable_links_page() {
		$links_page = new Ekc_Shareable_Links_Admin_Page();
		return $links_page->create_shareable_links_page();
	}

	public function create_tournaments_backup_page() {
		$backup_page = new Ekc_Tournaments_Backup_Page();
		return $backup_page->create_tournaments_backup_page();
	}

	public function export_teams_as_csv() {
		$teams_page = new Ekc_Teams_Admin_Page();
		return $teams_page->export_teams_as_csv();
	}

	public function download_backup_file() {
		$backup_page = new Ekc_Tournaments_Backup_Page();
		return $backup_page->download_backup_file();
	}
}
