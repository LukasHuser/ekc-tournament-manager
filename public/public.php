<?php

/**
 * The public-facing functionality of the plugin.
 */
class Ekc_Tournament_Manager_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'selectmenu-flags', plugin_dir_url( __FILE__ ) . 'css/flag-icon.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'ekc-bracket', plugin_dir_url( __FILE__ ) . 'css/bracket.ekc.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ekc_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		/* we only register (not enqueue) the refresh script, it will be enqueued by the shortcode which actually requires the script */
		wp_register_script( 'ekc-refresh', plugin_dir_url( __FILE__ ) . 'js/refresh.js', array( 'jquery' ), $this->version, false );
	}

	public function shareable_link_handle_post() {
		$helper = new Ekc_Shortcode_Helper();
		$helper->shortcode_shareable_link_handle_post();
	}
}
