<?php

/**
 * The admin-specific functionality of the plugin.
 *
 */
class Ekc_Tournament_Manager_Admin {

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
		wp_enqueue_style( 'jquery-ui-theme', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'css/jquery-confirm.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'selectmenu-flags', plugin_dir_url( __FILE__ ) . 'css/flag-icon.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/jquery-confirm.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'jquery-ui-selectmenu', 'jquery-ui-autocomplete' ), $this->version, false );
	}

	/**
	 * Callback for filter 'mce_external_plugins'.
	 * Returns a map with TinyMCE plugin name to plugin url.
	 */
	public function tinymce_external_plugins( $plugins ) {
		$plugins['ekc-emoticons'] = plugin_dir_url( __FILE__ ) . 'tinymce/plugins/ekc-emoticons/plugin.js';
		return $plugins;
	}

	public function add_tournament_menu() {
	    add_menu_page(
	        'Tournaments',
	        'EKC Tournaments',
	        'manage_options',
	        'ekc-tournaments',
	        array( $this, 'create_tournaments_page' ),
			// EKA logo as base64 encoded svg image
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgIHhtbG5zOmNjPSJodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9ucyMiCiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIgogICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiCiAgIHhtbG5zOmlua3NjYXBlPSJodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy9uYW1lc3BhY2VzL2lua3NjYXBlIgogICB3aWR0aD0iNDhtbSIKICAgaGVpZ2h0PSI0OG1tIgogICB2aWV3Qm94PSIwIDAgNDggNDgiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2Zzg1NiIKICAgaW5rc2NhcGU6dmVyc2lvbj0iMC45Mi4zICgyNDA1NTQ2LCAyMDE4LTAzLTExKSIKICAgc29kaXBvZGk6ZG9jbmFtZT0iZWthX2xvZ29fYmxhY2suc3ZnIj4KICA8ZGVmcwogICAgIGlkPSJkZWZzODUwIiAvPgogIDxzb2RpcG9kaTpuYW1lZHZpZXcKICAgICBpZD0iYmFzZSIKICAgICBwYWdlY29sb3I9IiNmZmZmZmYiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgYm9yZGVyb3BhY2l0eT0iMS4wIgogICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwLjAiCiAgICAgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIKICAgICBpbmtzY2FwZTp6b29tPSIxLjA4MTk5NjUiCiAgICAgaW5rc2NhcGU6Y3g9Ii0yOTEuMzY4OTYiCiAgICAgaW5rc2NhcGU6Y3k9Ii01MC4xMDcwNjciCiAgICAgaW5rc2NhcGU6ZG9jdW1lbnQtdW5pdHM9Im1tIgogICAgIGlua3NjYXBlOmN1cnJlbnQtbGF5ZXI9ImcxMiIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgZml0LW1hcmdpbi10b3A9IjEiCiAgICAgZml0LW1hcmdpbi1yaWdodD0iMSIKICAgICBmaXQtbWFyZ2luLWJvdHRvbT0iMSIKICAgICBmaXQtbWFyZ2luLWxlZnQ9IjEiCiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIyNTYwIgogICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9IjE0MTIiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LW1heGltaXplZD0iMSIgLz4KICA8bWV0YWRhdGEKICAgICBpZD0ibWV0YWRhdGE4NTMiPgogICAgPHJkZjpSREY+CiAgICAgIDxjYzpXb3JrCiAgICAgICAgIHJkZjphYm91dD0iIj4KICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL3N2Zyt4bWw8L2RjOmZvcm1hdD4KICAgICAgICA8ZGM6dHlwZQogICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+CiAgICAgICAgPGRjOnRpdGxlPjwvZGM6dGl0bGU+CiAgICAgIDwvY2M6V29yaz4KICAgIDwvcmRmOlJERj4KICA8L21ldGFkYXRhPgogIDxnCiAgICAgaW5rc2NhcGU6bGFiZWw9IkxheWVyIDEiCiAgICAgaW5rc2NhcGU6Z3JvdXBtb2RlPSJsYXllciIKICAgICBpZD0ibGF5ZXIxIgogICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMy45MzIyMjMsLTAuMDMxMjIzMjEpIj4KICAgIDxnCiAgICAgICB0cmFuc2Zvcm09Im1hdHJpeCgwLjI2NDU4MzMzLDAsMCwwLjI2NDU4MzMzLC0xNC4xMDIwNTgsLTEzLjc4NTY3MikiCiAgICAgICBpZD0iZzEyIj4KICAgICAgPHBvbHlnb24KICAgICAgICAgcG9pbnRzPSIyMjkuNiwxNTIuODkgMjQ5LjkzLDIxNC45OCAxOTcuMjEsMTc2LjQzIDE0NC4yMSwyMTQuOTggMTU0Ljc2LDE4My44IDE2NS4xOCwxNTIuOTcgMTExLjg4LDExNS4xNCAxNzcuNzEsMTE1LjE0IDIxNy4yMywxMTUuMTQgMjgxLjQ1LDExNS4xNCAiCiAgICAgICAgIGlkPSJwb2x5Z29uNiIKICAgICAgICAgc3R5bGU9ImZpbGw6IzAwMDAwMCIgLz4KICAgICAgPHBvbHlnb24KICAgICAgICAgY2xhc3M9ImNscy0xIgogICAgICAgICBwb2ludHM9IjE5Ny4yMSwxNzYuNDMgMTQ0LjIxLDIxNC45OCAxNTQuNzYsMTgzLjggMjQ5LjEzLDExNS4xNCAyODEuNDUsMTE1LjE0ICIKICAgICAgICAgaWQ9InBvbHlnb244IgogICAgICAgICBzdHlsZT0iZmlsbDojMDAwMDAwIiAvPgogICAgICA8cG9seWdvbgogICAgICAgICBjbGFzcz0iY2xzLTIiCiAgICAgICAgIHBvaW50cz0iMTYyLjQ1LDEwMi4yMiAxNzcuMzYsMTAyLjIyIDIxNi4zMywxMDIuMjQgMjMwLjkxLDEwMi4yNCAyMzAuOTEsNzEuMTcgMjEwLjQ3LDgzLjkxIDE5Ni40Niw3MC44OCAxODEuNTMsODMuOTEgMTYyLjQzLDcxLjIyICIKICAgICAgICAgaWQ9InBvbHlnb24xMCIKICAgICAgICAgc3R5bGU9ImZpbGw6IzAwMDAwMCIgLz4KICAgIDwvZz4KICA8L2c+Cjwvc3ZnPgo=');

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

	public function intercept_swiss_system_redirect() {
		$swiss_system_page = new Ekc_Swiss_System_Admin_Page();
		return $swiss_system_page->intercept_redirect();
	}

	public function intercept_elimination_bracket_redirect() {
		$elimination_bracket_page = new Ekc_Elimination_Bracket_Admin_Page();
		return $elimination_bracket_page->intercept_redirect();
	}
}
