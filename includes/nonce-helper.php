<?php

/**
 * Helper functions for Wordpress Nonces.
 */
class Ekc_Nonce_Helper {

	const NONCE_PREFIX = 'ekc-';
	const NONCE_NAME = 'ekc-nonce';

	public function nonce_text( $action, $object_name1 = null, $object_id1 = null, $object_name2 = null, $object_id2 = null ) {
		$nonce_text = self::NONCE_PREFIX . $action;
		if ( $object_name1 && $object_id1 ) {
			$nonce_text .= '-' . $object_name1 . '-' . $object_id1;
		}
		if ( $object_name2 && $object_id2 ) {
			$nonce_text .= '-' . $object_name2 . '-' . $object_id2;
		}
		return $nonce_text;
	}

	public function nonce_url( $url, $nonce_text ) {
		return wp_nonce_url( $url, $nonce_text, self::NONCE_NAME );
	}

	public function nonce_field( $nonce_text ) {
		wp_nonce_field( $nonce_text, self::NONCE_NAME );
	}

	public function validate_nonce( $nonce_text ) {
		$nonce = $this->read_nonce_request();
		return wp_verify_nonce( $nonce, $nonce_text );
	}

	public function read_nonce_request() {
		return isset( $_REQUEST[ self::NONCE_NAME ] ) ? trim( wp_unslash( $_REQUEST[ self::NONCE_NAME ] ) ) : null;
	}
}
