<?php

/**
 * The core plugin class.
 *
 * Define internationalization, admin-specific hooks, and public-facing site hooks.
 * Maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Ekc_Tournament_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Initialization
	 */
	public function __construct() {
		$this->version = EKC_PLUGIN_VERSION;
		$this->plugin_name = 'EKC Tournament Manager';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/loader.php';
		$this->loader = new Ekc_Loader();

		$this->register_migration();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->load_model();
		$this->load_libs();
		$this->add_shortcodes();
		$this->add_elementor_widgets();
		$this->elementor_module_extensions();
		$this->contact_form_7_integration();
	}

	private function register_migration() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database-setup.php';
		$database = new Ekc_Database_Setup();
		$this->loader->add_action( 'plugins_loaded', $database, 'check_database_version' );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/migration.php';
		$migration = new Ekc_Migration();
		$this->loader->add_action( 'plugins_loaded', $migration, 'migrate');
	}

	private function set_locale() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/i18n.php';
		$plugin_i18n = new Ekc_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {		
		if ( ! class_exists( 'WP_List_Table' ) ) {
    			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/tournaments-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/tournaments-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/teams-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/teams-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/elimination-bracket-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/swiss-system-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/tournaments-backup-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/tournaments-backup-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/shareable-links-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/shareable-links-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/result-log-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/result-log-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/check-in-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/check-in-table.php';

		$plugin_admin = new Ekc_Tournament_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_filter( 'mce_external_plugins', $plugin_admin, 'tinymce_external_plugins' );
		$this->loader->add_filter( 'map_meta_cap', $plugin_admin, 'filter_map_meta_cap', 10, 4 );

		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'export_teams_as_csv' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'download_backup_file' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_tournament_menu' );

		// duplicate page
		$this->loader->add_action('admin_action_ekc_duplicate_page', $plugin_admin, 'ekc_duplicate_page' );
        $this->loader->add_filter('page_row_actions', $plugin_admin, 'ekc_duplicate_page_link', 10, 2 );

		// Admin ajax calls
		// use wp_ajax prefix (but not wp_ajax_nopriv prefix) to allow the REST call to work for logged-in users but not for non-logged-in users
		$this->loader->add_action( 'wp_ajax_ekc_admin_swiss_system_store_result', $plugin_admin, 'create_swiss_system_page' );
		$this->loader->add_action( 'wp_ajax_ekc_admin_bracket_advance', $plugin_admin, 'create_bracket_page' );


		// for the redirect pattern to work, we need to write http headers before any output is written
		// we use the admin_init hook 
		$this->loader->add_action( 'admin_init', $plugin_admin, 'intercept_swiss_system_redirect' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'intercept_elimination_bracket_redirect' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'intercept_shareable_links_redirect' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'intercept_check_in_redirect' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/public.php';
		$plugin_public = new Ekc_Tournament_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		// Public ajax calls
		// use both wp_ajax and wp_ajax_nopriv prefixes to allow the REST call to work for logged-in users as well as non-logged-in users
		$this->loader->add_action( 'wp_ajax_ekc_public_swiss_system_store_result', $plugin_public, 'shareable_link_handle_post' );
		$this->loader->add_action( 'wp_ajax_nopriv_ekc_public_swiss_system_store_result', $plugin_public, 'shareable_link_handle_post' );
		$this->loader->add_action( 'wp_ajax_ekc_public_team_check_in', $plugin_public, 'shareable_link_handle_post' );
		$this->loader->add_action( 'wp_ajax_nopriv_ekc_public_team_check_in', $plugin_public, 'shareable_link_handle_post' );
	}

	private function add_shortcodes() {
		$shortcodes = new Ekc_Shortcode_Helper();
		$shortcodes->add_ekc_shortcodes();
	}

	private function add_elementor_widgets() {	
		$widget_helper = new Ekc_Widget_Helper();
		$this->loader->add_action( 'elementor/widgets/widgets_registered', $widget_helper, 'register_elementor_widgets' );
	}

	private function elementor_module_extensions() {	
		$module_helper = new Ekc_Module_Helper();
		$this->loader->add_action( 'elementor_pro/init', $module_helper, 'elementor_forms_module_extension' );
	}

	private function contact_form_7_integration() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/modules/contact-form-7/contact-form-7-support.php';

		$contact_form_7_support = new Ekc_Contact_Form_7_Support();
		$this->loader->add_filter( 'shortcode_atts_wpcf7', $contact_form_7_support, 'custom_shortcode_atts_wpcf7_filter', 10, 3 );
		$this->loader->add_filter( 'wpcf7_map_meta_cap', $contact_form_7_support, 'filter_wpcf7_map_meta_cap' );
		$this->loader->add_action( 'wpcf7_submit', $contact_form_7_support, 'wpcf7_submit', 10, 2 );
	}

	private function load_model() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/type-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/role-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/nonce-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/validation-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database-access.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/drop-down-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcode-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widget-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/module-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shareable-links-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/csv-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/model/tournament.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/model/team.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/model/player.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/model/result.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elimination-bracket-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/swiss-system-team.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/swiss-system-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/nation-trophy-helper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/nation-trophy-rank-description.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/backup/tournament-backup.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/backup/backup-helper.php';
	}

	private function load_libs() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/JsonMapper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/JsonMapper/Exception.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/blossom.php';
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
