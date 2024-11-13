<?php

/**
 * Helper functions for input validation.
 */
class Ekc_Validation_Helper {

	public function validate_get_text( $field ) {
		return isset( $_GET[ $field ] ) ? sanitize_text_field( wp_unslash( $_GET[ $field ] ) ) : '';
	}

	public function validate_get_key( $field ) {
		return isset( $_GET[ $field ] ) ? intval( sanitize_key( wp_unslash( $_GET[ $field ] ) ) ) : null;
	}

	public function validate_get_boolean( $field ) {
		return isset( $_GET[ $field ] ) ? filter_var( wp_unslash( $_GET[ $field ] ), FILTER_VALIDATE_BOOLEAN ) : false;
	}

	public function validate_get_integer( $field ) {
		return Ekc_Type_Helper::opt_intval( $this->validate_get_text( $field ) );
	}

	public function validate_post_text( $field ) {
		return isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
	}

	public function validate_post_key( $field ) {
		return isset( $_POST[ $field ] ) ? intval( sanitize_key( wp_unslash( $_POST[ $field ] ) ) ) : null;
	}

	public function validate_post_boolean( $field ) {
		return isset( $_POST[ $field ] ) ? filter_var( wp_unslash( $_POST[ $field ] ), FILTER_VALIDATE_BOOLEAN ) : false;
	}

	public function validate_post_dropdown_text( $field ) {
		return Ekc_Drop_Down_Helper::empty_if_none( $this->validate_post_text( $field ) );
	}
	
	public function validate_post_dropdown_key( $field ) {
		return Ekc_Type_Helper::opt_intval( $this->validate_post_dropdown_text( $field ) );
	}

	public function validate_post_integer( $field ) {
		return Ekc_Type_Helper::opt_intval( $this->validate_post_text( $field ) );
	}

	public function validate_post_float( $field ) {
		return Ekc_Type_Helper::opt_floatval( $this->validate_post_text( $field ) );
	}

	public function validate_server_request_uri() {
		return isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_url( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) )  : null;
	}
}
