<?php

/**
 * Generic helper methods for backend
 */
class Ekc_Admin_Helper {

	/**
	 * Redirect to same url and enforce a GET reload to avoid re-sending form data through POST 
	 * return HTTP 303 See Other
	 * see https://en.wikipedia.org/wiki/HTTP_303
	 */
	public function post_redirect_get() {
		wp_safe_redirect($_SERVER['REQUEST_URI'], 303);
		// exit script - do not write any output
		exit;
	}

	/**
	 * Strips a given url parameter from the request url and enforces a GET reload to the original url,
     * but without the stripped parameter.
     * Similar to a post-redirect-get pattern, this allows to avoid re-sending and re-executing actions
     * which are triggered by a GET url parameter. 
     *  
     * return HTTP 303 See Other
	 * see https://en.wikipedia.org/wiki/HTTP_303
	 */
	public function get_redirect_get( $param ) {
        $redirect_url = $_SERVER['REQUEST_URI'];

        if ( isset( $_GET[$param] ) ) {
            $redirect_url = $this->strip_param_from_url( $redirect_url, $param );
            
            wp_safe_redirect( $redirect_url, 303 );
			
			// exit script - do not write any output
			exit;
        }
	}

    private function strip_param_from_url( $url, $param ) {
        $base_url = strtok( $url, '?' );
        $parsed_url = parse_url( $url );
        if ( array_key_exists( 'query', $parsed_url ) ) {
            $query = $parsed_url['query'];
            parse_str( $query, $parameters ); // Convert Parameters into array
            unset( $parameters[$param] );
            $new_query = http_build_query( $parameters );
            return $base_url . '?' . $new_query;
        }
        return $url;            
    }

    /**
	 * Redirect to same url and enforce a GET reload to avoid re-sending form data through POST,
     * or re-executing an action trough GET parameter 'action'. 
     *
     * Tournament id and round are added as get parameters (if provided). 
	 * 
     * return HTTP 303 See Other
	 * see https://en.wikipedia.org/wiki/HTTP_303
	 */
	public function swiss_system_redirect( $tournament_id, $tournament_round ) {
        $this-redirect_internal( 'swiss-system', $tournament_id, $tournament_round );
	}

    /**
	 * Redirect to same url and enforce a GET reload to avoid re-sending form data through POST,
     * or re-executing an action trough GET parameter 'action'. 
     *
     * Tournament id is added as get parameters (if provided).
	 * 
     * return HTTP 303 See Other
	 * see https://en.wikipedia.org/wiki/HTTP_303
	 */
	public function elimination_bracket_redirect( $tournament_id ) {
        $this->redirect_internal( 'elimination-bracket', $tournament_id, null );
	}

    private function redirect_internal( $action, $tournament_id, $tournament_round ) {
        $redirect_url = $_SERVER['REQUEST_URI'];
        $redirect_url = $this->add_or_replace_url_parameter( $redirect_url, 'action', $action );
        if ( $tournament_id ) {
            $redirect_url = $this->add_or_replace_url_parameter( $redirect_url, 'tournamentid', $tournament_id );
        }
        if ( $tournament_round ) {
            $redirect_url = $this->add_or_replace_url_parameter( $redirect_url, 'round', $tournament_round );
        }

        wp_safe_redirect( $redirect_url, 303);
		// exit script - do not write any output
		exit;
    }

    private function add_or_replace_url_parameter( $url, $param, $param_value ) {
        $base_url = strtok( $url, '?' );
        $parsed_url = parse_url( $url );
        if ( array_key_exists( 'query', $parsed_url ) ) {
            $query = $parsed_url['query'];
            parse_str( $query, $parameters ); // Convert Parameters into array
            $parameters[$param] = $param_value;
            $new_query = http_build_query( $parameters );
            return $base_url . '?' . $new_query;
        }
        else {
            return $base_url . '?' . $param . '=' . $param_value;
        }            
    }
}