<?php

/**
 * Page to list, generate and send shareable links for each team.
 * A shareable link points to a team-specific page, showing all matches and results for a team,
 * and also allows to report the result for the current round.
 */
class Ekc_Shareable_links_Admin_Page {

	public function create_shareable_links_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$team_id = ( isset($_GET['teamid'] ) ) ? sanitize_key( wp_unslash( $_GET['teamid'] ) ) : null;
    $tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    if ( isset($_POST['tournamentid'] ) ) {
      $tournament_id = intval( sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) );
    }
    
    if ( $action === 'generate' ) {
      $this->generate_shareable_link( $team_id );
    }
    elseif ( $action === 'send' ) {
      $this->send_shareable_link_by_mail( $tournament_id, $team_id );
    }
    elseif ( $action === 'generateall' ) {
      $this->generate_all_shareable_links( $tournament_id );
    }
    elseif ( $action === 'sendall' ) {
      $this->send_all_shareable_links_by_mail( $tournament_id );
    }
    else {
      // handle POST
      if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $this->handle_post( $tournament_id );
      }
    }

    $db = new Ekc_Database_Access();
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $this->show_wp_header( $tournament );
    $this->show_shareable_links_content( $tournament );
    $this->show_shareable_links_table( $tournament_id );
    $this->close_wp_content();
  }

  private function handle_post( $tournament_id ) {
    $url_prefix = '';
    $email_content = '';
		if ( isset($_POST['urlprefix'] ) ) {
			$url_prefix = sanitize_text_field( wp_unslash( $_POST['urlprefix'] ) );
    }
    if ( isset($_POST['emailcontent'] ) ) {
			$email_content = sanitize_textarea_field( wp_unslash( $_POST['emailcontent'] ) );
    }
    if ( $tournament_id ) {
      $helper = new Ekc_Shareable_Links_Helper();
      $helper->store_shareable_links_content( $tournament_id, $url_prefix, $email_content );
    }
  }

  private function show_wp_header( $tournament ) {
    ?>
    <div class="wrap">
    
      <h1 class="wp-heading-inline"><?php esc_html_e( $tournament->get_name() ) ?> shareable links</h1>
      <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;action=generateall" class="page-title-action"><?php _e( 'Generate all shareable links' ) ?></a>
      <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;action=sendall" class="page-title-action"><?php _e( 'Send all shareable links' ) ?></a>
    
      <hr class="wp-header-end">
    <?php 
  }

  /**
   * Close the css class 'wrap' div from wordpress
   */
  private function close_wp_content() {
    ?>
    </div><!-- .wrap -->
    <?php
  }

  private function show_shareable_links_content( $tournament ) {
    $url_prefix = $tournament->get_shareable_link_url_prefix();
    $email_content = $tournament->get_shareable_link_email_text();

    ?>
    <p>For each team, a unique shareable link is generated and sent by e-mail to the registered e-mail address.<br/>
       The link will point to a personalized page for the given team, showing all matches and results, and allows entering the result for the current round.</p>
    <p>Note: make sure that sending e-mails is correctly configured for your wordpress installation.<br/>
       We recommend to use an SMTP server, configured through a plugin such as <a href="https://wordpress.org/plugins/wp-mail-smtp/">WP Mail SMTP</a>.</p>
      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
        <fieldset>
          <div class="ekc-control-group">
            <label for="urlprefix">URL prefix</label>
            <input id="urlprefix" name="urlprefix" type="text" placeholder="http://mydomain.com/mytournament/team" size="40" maxlength="500" value="<?php esc_html_e( $url_prefix ) ?>" />
          </div>
          <div class="ekc-control-group">
            <p>E-mail content supports the following placholders:<br/>
              ${team} will be replaced by the team name.<br/>
              ${url} will be replaced by the full shareable link url.<br/>
              You can use html in this e-mail text.</p>
            <textarea id="emailcontent" name="emailcontent" cols="50" rows="5"><?php esc_html_e( $email_content ) ?></textarea>
          </div>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary">Save</button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
          </div>
        </fieldset>
      </form>
    <?php
      }
  
	public function show_shareable_links_table( $tournament_id ) {
		$links_table = new Ekc_Shareable_Links_Table( $tournament_id );
    ?>
    <form id="links-filter" method="get" >
    <input id="page" name="page" type="hidden" value="<?php esc_html_e( $_REQUEST['page'] ) ?>" />
    <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament_id ) ?>" />
    <?php
	$links_table->prepare_items();
	$links_table->display();
	}		

	public function generate_shareable_link( $team_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->generate_shareable_link( $team_id );
  }

  public function generate_all_shareable_links( $tournament_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->generate_all_shareable_links( $tournament_id );
  }

  public function send_shareable_link_by_mail( $tournament_id, $team_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->send_shareable_link_by_mail( $tournament_id, $team_id );
  }

  public function send_all_shareable_links_by_mail( $tournament_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->send_all_shareable_links_by_mail( $tournament_id );
  }
}
