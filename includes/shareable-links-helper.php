<?php

/**
 * Helper class for shareable links for each team.
 */
class Ekc_Shareable_Links_Helper {

	public function store_shareable_links_content( $tournament_id, $url_prefix, $email_content ) {
		$tournament = new Ekc_Tournament();
		$tournament->set_tournament_id( $tournament_id );
		$tournament->set_shareable_link_url_prefix( $url_prefix );
		$tournament->set_shareable_link_email_text( $email_content );
		
		$db = new Ekc_Database_Access();
		$db->update_shareable_link_data( $tournament );
	}

	public function generate_all_shareable_links( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$teams = $db->get_active_team_ids( $tournament_id );
		foreach ( $teams as $team_id ) {
			$this->generate_shareable_link( $team_id ); 
		}
	}

	public function generate_shareable_link( $team_id ) {
		$db = new Ekc_Database_Access();
		$link_id = $this->create_shareable_link_id( $team_id );
		$db->update_shareable_link_id( $team_id, $link_id );
	}

	public function send_all_shareable_links_by_mail( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
		$teams = $db->get_active_teams( $tournament_id );
		
		foreach ( $teams as $team ) {
			$this->send_shareable_link( $tournament, $team ); 
		}
	}

	public function send_shareable_link_by_mail( $tournament_id, $team_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
		$team = $db->get_team_by_id( $team_id );

		$this->send_shareable_link( $tournament, $team );
	}
	
	private function send_shareable_link( $tournament, $team ) {
		$url = $this->create_shareable_link_url( $tournament->get_shareable_link_url_prefix(), $team->get_shareable_link_id() );
		$email_content = $this->replace_placeholder( $tournament->get_shareable_link_email_text(), $team->get_name(), $url );

		// convert new lines to html <br>
		$email_content = nl2br( $email_content );
		if ( $team->get_email() ) {
			$this->send_mail( $team->get_email(), $tournament->get_name(), $email_content );
		}
	} 

	private function replace_placeholder( $email_content, $team_name, $url ) {
		$email_content_replaced = str_replace( '${team}', $team_name, $email_content );
		$email_content_replaced = str_replace( '${url}', $url, $email_content_replaced );
		return $email_content_replaced;
	}

	/**
	 * We use wp_mail() to send emails
	 * 1) if no smtp server is configured, wp_mail uses php mail() function
	 *    and relies on the hosting platform for correct configuration
	 * 2) WP Plugins such as 'WP Mail SMTP' allow to configure an smtp server
	 *    for wp_mail function. This is the recommended option.
	 *    https://wordpress.org/plugins/wp-mail-smtp/
	 */
	private function send_mail( $recipient_email_address, $subject, $email_content ) {
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$html_email_content = '<html>' . $email_content . '</html>';
		
		wp_mail( $recipient_email_address, $subject, $html_email_content, $headers );
	}

	public function create_shareable_link_url( $url_prefix, $link_id ) {
		return $url_prefix . 'linkid=' . $link_id;
	}

	private function create_shareable_link_id( $team_id ) {
		return $team_id . '-' . $this->random_str(20);
	}

/**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
 private function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
  }
}

