<?php

/**
 * Extensions for Elementor (Pro) Modules
 */
class Ekc_Module_Helper {

	public function elementor_forms_module_extension() {
		if ( ! defined( 'ELEMENTOR_PRO_PATH' ) || ! class_exists( 'ElementorPro\Modules\Forms\Module' ) || ! class_exists( 'ElementorPro\Plugin' ) ) {
			return;
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/modules/elementor/forms/actions/team-registration-action.php';
		$forms_module = \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms');
		if ( $forms_module ) {
			$forms_module->add_form_action( 'ekc-team', new Ekc_Form_Action_Team_Registration() );
		}
	}
}
