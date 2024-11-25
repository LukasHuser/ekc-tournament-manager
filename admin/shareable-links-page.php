<?php

/**
 * Page to list, generate and send shareable links for each team.
 * A shareable link points to a team-specific page, showing all matches and results for a team,
 * and also allows to report the result for the current round.
 */
class Ekc_Shareable_links_Admin_Page {

  public function intercept_redirect() {
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );

    if ( $page !== 'ekc-links' ) {
      return;
    }

    $action = $validation_helper->validate_get_text( 'action' );
    if ( ! $action ) {
      $action = $validation_helper->validate_post_text( 'action' );
    }
    if ( $action === 'generate'
      || $action === 'send'
      || $action === 'generateall'
      || $action === 'sendall'
      || $action === 'shareable-links-store') {
        $this->create_shareable_links_page();
      }
  }

	public function create_shareable_links_page() {
	
    $admin_helper = new Ekc_Admin_Helper();
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $action = $validation_helper->validate_get_text( 'action' );
		$team_id = $validation_helper->validate_get_key( 'teamid' );
    $tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    if ( ! $tournament_id ) {
      $tournament_id = $validation_helper->validate_post_key( 'tournamentid' );
    }
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

    if ( $action === 'shareable-links' ) {
      $this->show_shareable_links( $tournament_id );
    }
    if ( $action === 'generate' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->generate_shareable_link( $team_id );
      }
      $admin_helper->shareable_links_redirect( $tournament_id );
    }
    elseif ( $action === 'send' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->send_shareable_link_by_mail( $tournament_id, $team_id );
      }
      $admin_helper->shareable_links_redirect( $tournament_id );
    }
    elseif ( $action === 'generateall' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->generate_all_shareable_links( $tournament_id );
      }
      $admin_helper->shareable_links_redirect( $tournament_id );
    }
    elseif ( $action === 'sendall' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->send_all_shareable_links_by_mail( $tournament_id );
      }
      $admin_helper->shareable_links_redirect( $tournament_id );
    }
    else {
      // handle POST
      if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $this->handle_post( $tournament_id );
        $admin_helper->shareable_links_redirect( $tournament_id );
      }
    }
  }

  private function handle_post( $tournament_id ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $action = $validation_helper->validate_post_text( 'action' );

    if ( $action !== 'shareable-links-store'
      || ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
      return;
    }

    $url_prefix = '';
    $email_content = '';
    $sender_email = '';
		if ( isset($_POST['urlprefix'] ) ) {
			$url_prefix = sanitize_url( wp_unslash( $_POST['urlprefix'] ) );
    }
    if ( isset($_POST['emailcontent'] ) ) {
			$email_content = wp_kses_post( wp_unslash( $_POST['emailcontent'] ) );
    }
    if ( isset($_POST['senderemail'] ) ) {
      // Supported formats: plain e-mail address (fullname@mail.tld) or mailbox address (Full Name <fullname@mail.tld>).
      // Parse an validate e-mail address separately.
			$sender_email_raw = trim( wp_unslash( $_POST['senderemail'] ) );
      $mailbox_parts = explode('<', $sender_email_raw );
      if ( count( $mailbox_parts ) > 1 ) {
        $sender_email = trim( $mailbox_parts[0] ) . ' <' . sanitize_email( $mailbox_parts[1] ) . '>';
      }
      else {
        $sender_email = sanitize_email( $sender_email_raw );
      }
    }
    if ( $tournament_id ) {
      $helper = new Ekc_Shareable_Links_Helper();
      $helper->store_shareable_links_content( $tournament_id, $url_prefix, $email_content, $sender_email );
    }
  }

  public function show_shareable_links( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $this->show_wp_header( $tournament );
    $this->show_shareable_links_content( $tournament );
    $this->show_shareable_links_table( $tournament_id );
    $this->close_wp_content();
  }

  private function show_wp_header( $tournament ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    $tournament_id = $tournament->get_tournament_id();

    $generateall_url = sprintf( '?page=%s&action=%s&tournamentid=%s', $page, 'generateall', $tournament_id );
    $generateall_url = $nonce_helper->nonce_url( $generateall_url, $nonce_helper->nonce_text( 'generateall', 'tournament', $tournament_id ) );

    $sendall_url = sprintf( '?page=%s&action=%s&tournamentid=%s', $page, 'sendall', $tournament_id );
    $sendall_url = $nonce_helper->nonce_url( $sendall_url, $nonce_helper->nonce_text( 'sendall', 'tournament', $tournament_id ) );

    ?>
    <div class="wrap">
    
      <h1 class="wp-heading-inline"><?php printf( '%s %s', esc_html( $tournament->get_name() ), esc_html__( 'Shareable Links', 'ekc-tournament-manager' ) ) ?></h1>
      <a href="<?php echo esc_url( $generateall_url ) ?>" class="page-title-action"><?php esc_html_e( 'Generate all shareable links', 'ekc-tournament-manager' ) ?></a>
      <a href="<?php echo esc_url( $sendall_url ) ?>" class="page-title-action"><?php esc_html_e( 'Send all shareable links', 'ekc-tournament-manager' ) ?></a>
    
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
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );

    $url_prefix = $tournament->get_shareable_link_url_prefix();
    $email_content = $tournament->get_shareable_link_email_text();
    $sender_email = $tournament->get_shareable_link_sender_email();

    ?>
    <p>For each team, a unique shareable link is generated and sent by e-mail to the registered e-mail address.<br/>
       The link will point to a personalized page for the given team, showing all matches and results, and allows entering the result for the current round.</p>
    <p>Note: make sure that sending e-mails is correctly configured for your wordpress installation.<br/>
       We recommend to use an SMTP server, configured through a plugin such as <a href="https://wordpress.org/plugins/wp-mail-smtp/">WP Mail SMTP</a>.</p>
      <form class="ekc-form" method="post" action="<?php echo esc_url( '?page=' . $page ) ?>" accept-charset="utf-8">
        <fieldset>
          <div class="ekc-control-group">
            <label for="urlprefix">URL prefix</label>
            <input id="urlprefix" name="urlprefix" type="text" placeholder="https://mydomain.com/mytournament/team" size="40" maxlength="500" value="<?php echo esc_url( $url_prefix ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="senderemail">Sender e-mail</label>
            <div><input id="senderemail" name="senderemail" type="text" placeholder="Full Name &lt;name@example.com&gt;" size="40" maxlength="500" value="<?php echo esc_html( $sender_email ) ?>" />
                 <p>Format: &quot;name@example.com&quot; or &quot;Full Name &lt;name@example.com&gt;&quot;</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="emailcontent">E-mail content</label>
            <p>E-mail content supports the following placeholders:<br/>
              <b>${team}</b> will be replaced by the team name.<br/>
              <b>${url}</b> will be replaced by the full shareable link url.
            </p>
          </div><div>
            <?php
                $editor_settings = array(
                  'tinymce' => array(
                    'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,alignjustify,|,forecolor,backcolor,bullist,numlist,removeformat,|,link,unlink,image,emoticons,undo,redo',
                    'toolbar2' => '',
                  ),
                  'wpautop' => false,
                  'textarea_rows' => 15,
                  'quicktags' => true,
                  'media_buttons' => false,
                );
                wp_editor( $email_content, 'emailcontent', $editor_settings );
            ?>
          </div>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary"><?php esc_html_e( 'Save', 'ekc-tournament-manager' ) ?></button>
            <input id="action" name="action" type="hidden" value="shareable-links-store" />
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
            <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( 'shareable-links-store', 'tournament', $tournament->get_tournament_id() ) ) ?>
          </div>
        </fieldset>
      </form>
    <?php
  }
  
	public function show_shareable_links_table( $tournament_id ) {
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$links_table = new Ekc_Shareable_Links_Table( $tournament_id );
    ?>
    <form id="links-filter" method="get" >
    <input id="page" name="page" type="hidden" value="<?php echo esc_attr( $page ) ?>" />
    <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament_id ) ?>" />
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
