<?php

/**
 * helper functions for php type handling
 */
class Ekc_Type_Helper {

	public static function opt_intval( $value ) {
		if ( is_null( $value ) or $value === '' ) {
			return null;
		}
		return intval( $value );
	} 

	public static function opt_floatval( $value ) {
		if ( is_null( $value ) or $value === '' ) {
			return null;
		}
		return floatval( $value );
	}
}
