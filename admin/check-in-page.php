<?php

/**
 * Admin page which allows managing team check-in
 */
class Ekc_Check_In_Page {

  public function intercept_redirect() {
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    if ( $page !== 'ekc-check-in' ) {
      return;
    }
    
    $action = $validation_helper->validate_get_text( 'action' );
    if ( ! $action ) {
      $action = $validation_helper->validate_post_text( 'action' );
    }
    if ( $action === 'activate-check-in'
      || $action === 'inactivate-check-in'
      || $action === 'activate-team'
      || $action === 'inactivate-team'
      || $action === 'check-in-team'
      || $action === 'check-out-team'
      || $action === 'feepaid'
      || $action === 'feenotpaid'
      || $action === 'onwaitlist'
      || $action === 'offwaitlist'
      || $action === 'generate-link'
      || $action === 'send-link') {
        $this->create_check_in_page();
      }
  }

	public function create_check_in_page() {

    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $admin_helper = new Ekc_Admin_Helper();
    $action = $validation_helper->validate_get_text( 'action' );
    $tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    $team_id = $validation_helper->validate_get_key( 'teamid' );
    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
    
    if ( $action === 'check-in' ) {
			$this->show_check_in( $tournament_id );
    }
    elseif ( $action === 'activate-check-in' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->set_check_in_active( $tournament_id, true );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
    elseif ( $action === 'inactivate-check-in' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->set_check_in_active( $tournament_id, false );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
    elseif ( $action === 'activate-team' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_active( $team_id, true );
      }
      $admin_helper->check_in_redirect( $tournament_id );
		}
		elseif ( $action === 'inactivate-team' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_active( $team_id, false );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
    elseif ( $action === 'check-in-team' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_checked_in( $team_id, true );
      }
      $admin_helper->check_in_redirect( $tournament_id );
		}
		elseif ( $action === 'check-out-team' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_checked_in( $team_id, false );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
    elseif ( $action === 'feepaid' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_registration_fee_paid( $team_id, true );
      }
      $admin_helper->check_in_redirect( $tournament_id );
		}
		elseif ( $action === 'feenotpaid' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_registration_fee_paid( $team_id, false );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
		elseif ( $action === 'onwaitlist' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_on_wait_list( $team_id, true );
      }
      $admin_helper->check_in_redirect( $tournament_id );
		}
		elseif ( $action === 'offwaitlist' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_on_wait_list( $team_id, false );
      }
      $admin_helper->check_in_redirect( $tournament_id );
		}
    elseif ( $action === 'generate-link' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->generate_shareable_link( $team_id );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
    elseif ( $action === 'send-link' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->send_shareable_link_by_mail( $tournament_id, $team_id );
      }
      $admin_helper->check_in_redirect( $tournament_id );
    }
  }
  
  public function show_check_in( $tournament_id ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $db = new Ekc_Database_Access();
    $tournament = $db->get_tournament_by_id( $tournament_id );

    if ( ! $tournament || ! $tournament->is_check_in_enabled() ) {
      return;
    }
?>
  <div class="wrap">
    <h1 class="wp-heading-inline"><?php printf( '%s %s', esc_html( $tournament->get_name() ), esc_html__( 'Check-in', 'ekc-tournament-manager' ) ) ?></h1>
    <hr class="wp-header-end">
<?php
              
    // show activate / inactivate check-in link
    if ( ! $db->is_check_in_active( $tournament->get_tournament_id() ) ) {
      $activate_check_in_url = sprintf( '?page=ekc-check-in&action=activate-check-in&tournamentid=%s', $tournament->get_tournament_id() );
      $activate_check_in_url = $nonce_helper->nonce_url( $activate_check_in_url, $nonce_helper->nonce_text( 'activate-check-in', 'tournament', $tournament->get_tournament_id() ) );
      ?>
      <p><?php esc_html_e( 'Self-check-in on shareable link pages is not active.', 'ekc-tournament-manager' ) ?>
      &nbsp; <a href="<?php echo esc_url( $activate_check_in_url ) ?>"><?php esc_html_e( 'activate', 'ekc-tournament-manager' ) ?></a></p>
      <?php
    }
    else {
      $inactivate_check_in_url = sprintf( '?page=ekc-check-in&action=inactivate-check-in&tournamentid=%s', $tournament->get_tournament_id() );
      $inactivate_check_in_url = $nonce_helper->nonce_url( $inactivate_check_in_url, $nonce_helper->nonce_text( 'inactivate-check-in', 'tournament', $tournament->get_tournament_id() ) );
      $reload_page_url = sprintf( '?page=ekc-check-in&action=check-in&tournamentid=%s', $tournament->get_tournament_id() );
      ?>
      <p><?php esc_html_e( 'Self-check-in on shareable link pages is active.', 'ekc-tournament-manager' ) ?>
      &nbsp; <a href="<?php echo esc_url( $inactivate_check_in_url ) ?>"><?php esc_html_e( 'inactivate', 'ekc-tournament-manager' ) ?></a></p>
      <p><a href="<?php echo esc_url( $reload_page_url ) ?>"><?php esc_html_e( 'reload page', 'ekc-tournament-manager' ) ?></a></p>
      <?php
    }

    // show check-in table
    [$total_teams, $active_teams, $checked_in_teams, $registration_fee_paid_teams] = $db->get_check_in_summary( $tournament_id );
    ?>
    <p><strong><?php esc_html_e( 'Check-in summary:', 'ekc-tournament-manager' ) ?></strong><br>
    <?php echo sprintf( /* translators: 1: total teams 2: active teams 3: checked in teams 4: registration fee paid */ esc_html__( '%1$d total, %2$d active, %3$d checked in, %4$d registration fee paid', 'ekc-tournament-manager' ), esc_html( $total_teams ), esc_html( $active_teams ), esc_html( $checked_in_teams ), esc_html( $registration_fee_paid_teams ) ) ?>
    </p>
    <?php

    $this->show_check_in_table( $tournament->get_tournament_id() );

?>
  </div><!-- .wrap -->
<?php
  }

	private function show_check_in_table( $tournament_id ) {
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$check_in_table = new Ekc_Check_In_Table( $tournament_id );
    
    ?>
    <form id="check-in-filter" method="get" >
    <input id="page" name="page" type="hidden" value="<?php echo esc_attr( $page ) ?>" />
    <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament_id ) ?>" />
    <input id="action" name="action" type="hidden" value="check-in" />
    <?php
	  
    $check_in_table->prepare_items();
	  $check_in_table->display();
	}		

  private function set_check_in_active( $tournament_id, $is_check_in_active ) {
    $db = new Ekc_Database_Access();
    $db->set_check_in_active( $tournament_id, $is_check_in_active );
  }

  private function set_team_active( $team_id, $is_team_active ) {
		$db = new Ekc_Database_Access();
		$db->set_team_active( $team_id, $is_team_active );
	}

  private function set_team_checked_in( $team_id, $is_team_checked_in ) {
    $db = new Ekc_Database_Access();
		$db->set_team_checked_in( $team_id, $is_team_checked_in );
	}

  private function set_registration_fee_paid( $team_id, $is_registration_fee_paid ) {
		$db = new Ekc_Database_Access();
		$db->set_registration_fee_paid( $team_id, $is_registration_fee_paid );
	}

	private function set_team_on_wait_list( $team_id, $is_on_wait_list ) {
		$db = new Ekc_Database_Access();
		$db->set_team_on_wait_list( $team_id, $is_on_wait_list );
  }

	private function generate_shareable_link( $team_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->generate_shareable_link( $team_id );
  }

  private function send_shareable_link_by_mail( $tournament_id, $team_id ) {
		$helper = new Ekc_Shareable_Links_Helper();
		$helper->send_shareable_link_by_mail( $tournament_id, $team_id );
  }
}

