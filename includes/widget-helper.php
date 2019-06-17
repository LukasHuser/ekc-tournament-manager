<?php

/**
 * Registers Wordpress and Elementor Widgets
 */
class Ekc_Widget_Helper {

	public function register_elementor_widgets() {
		if ( ! defined( 'ELEMENTOR_PATH' ) || ! class_exists( 'Elementor\Widget_Base' ) || ! class_exists( 'Elementor\Plugin' ) ) {
			return;
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widgets/elementor/counter.php';
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Ekc_Widget_Counter() );
	}
}
