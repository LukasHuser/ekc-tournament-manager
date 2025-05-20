<?php

/**
 * Helper functions for input validation.
 */
class Ekc_Validation_Helper {

	// --------------------------------------------------------------------------------- //
	// Generic validation functions
	// ----------------------------------------------------------------------------------//

	public function validate_text( $text ) {
		// do not use sanitize_text_field here, it is more like an escaping function
		return trim( wp_unslash( $text ) );
	}

	public function validate_key( $text ) {
		return intval( sanitize_key( wp_unslash( $text ) ) );
	}

	public function validate_boolean( $text ) {
		return filter_var( wp_unslash( $text ), FILTER_VALIDATE_BOOLEAN );
	}

	public function validate_integer( $text ) {
		return Ekc_Type_Helper::opt_intval( $this->validate_text( $text ) );
	}

	public function validate_float( $text ) {
		return Ekc_Type_Helper::opt_floatval( $this->validate_text( $text ) );
	}

	// --------------------------------------------------------------------------------- //
	// Validate GET variables
	// ----------------------------------------------------------------------------------//

	public function validate_get_text( $field ) {
		return isset( $_GET[ $field ] ) ? $this->validate_text( $_GET[ $field ] )  : '';
	}

	public function validate_get_key( $field ) {
		return isset( $_GET[ $field ] ) ? $this->validate_key( $_GET[ $field ] ) : null;
	}

	public function validate_get_boolean( $field ) {
		return isset( $_GET[ $field ] ) ? $this->validate_boolean( $_GET[ $field ] ) : false;
	}

	public function validate_get_integer( $field ) {
		return isset( $_GET[ $field ] ) ? $this->validate_integer( $_GET[ $field ] ) : null;
	}

	// --------------------------------------------------------------------------------- //
	// Validate POST variables
	// ----------------------------------------------------------------------------------//

	public function validate_post_text( $field ) {
		return isset( $_POST[ $field ] ) ? $this->validate_text( $_POST[ $field ] )  : '';
	}

	public function validate_post_key( $field ) {
		return isset( $_POST[ $field ] ) ? $this->validate_key( $_POST[ $field ] ) : null;
	}

	public function validate_post_boolean( $field ) {
		return isset( $_POST[ $field ] ) ? $this->validate_boolean( $_POST[ $field ] ) : false;
	}

	public function validate_post_dropdown_text( $field ) {
		return Ekc_Drop_Down_Helper::empty_if_none( $this->validate_post_text( $field ) );
	}
	
	public function validate_post_dropdown_key( $field ) {
		return Ekc_Type_Helper::opt_intval( $this->validate_post_dropdown_text( $field ) );
	}

	public function validate_post_integer( $field ) {
		return isset( $_POST[ $field ] ) ? $this->validate_integer( $_POST[ $field ] ) : null;
	}

	public function validate_post_float( $field ) {
		return isset( $_POST[ $field ] ) ? $this->validate_float( $_POST[ $field ] ) : null;
	}

	// --------------------------------------------------------------------------------- //
	// Special validation functions
	// ----------------------------------------------------------------------------------//

	public function validate_server_request_uri() {
		return isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_url( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) )  : null;
	}
}
