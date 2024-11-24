<?php

/**
 * Admin page which allows administraton of Swiss System tournament
 */
class Ekc_Swiss_System_Admin_Page {

  public function intercept_redirect() {
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    if ( $page !== 'ekc-swiss' ) {
      return;
    }
    
    $action = $validation_helper->validate_get_text( 'action' );
    if ( ! $action ) {
      $action = $validation_helper->validate_post_text( 'action' );
    }
    if ( $action === 'swiss-system-start-timer'
      || $action === 'delete-round'
      || $action === 'swiss-system-store-round'
      || $action === 'swiss-system-new-round') {
        $this->create_swiss_system_page();
      }
  }

	public function create_swiss_system_page() {

    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $admin_helper = new Ekc_Admin_Helper();
    $action = $validation_helper->validate_get_text( 'action' );
    $tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    $team_id = $validation_helper->validate_get_key( 'teamid' );
    $tournament_round = $validation_helper->validate_get_integer( 'round' );
    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
    
    if ( $action === 'swiss-system' ) {
			$this->show_swiss_system( $tournament_id, $tournament_round );
    }
    elseif ( $action === 'swiss-system-start-timer' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->start_timer( $tournament_id, $tournament_round );
      }
      $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
    }
    elseif ( $action === 'swiss-system-ranking' ) {
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'remove-team' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->remove_team_from_tournament( $team_id );
      }
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'random-seed' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->random_seed( $tournament_id );
      }
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'delete-round' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->delete_round( $tournament_id );
      }
      $admin_helper->swiss_system_redirect( $tournament_id, null );
    }
		else {
			// handle POST
      $db = new Ekc_Database_Access();
      $tournament_id = $validation_helper->validate_post_key( 'tournamentid' );
      if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
        return;
      }
      $tournament = $db->get_tournament_by_id( $tournament_id );
      $tournament_round = $validation_helper->validate_post_integer( 'tournamentround' );
      $result_id = $validation_helper->validate_post_key( 'resultid' );
      $action = $validation_helper->validate_post_text( 'action' );
      if ( $action === 'swiss-system-store-round' ) {
        if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
          $existing_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, '', $tournament_round );
          $this->update_results( $existing_results );
          if ( $tournament->is_auto_backup_enabled() ) {
            $helper = new Ekc_Backup_Helper();
            $helper->store_backup( $tournament_id );
          }
        }
        $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'ekc_admin_swiss_system_store_result' ) {
        // asynchronous ajax request
        if ( ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'result', $result_id ) ) ) {
          ?><span class="dashicons dashicons-no"></span><?php
          wp_die();
        }
        $existing_result = $db->get_tournament_result_by_id( $result_id );
        $this->update_results( array( $existing_result ) );
        ?><span class="dashicons dashicons-yes"></span><?php
        wp_die();
      }
      elseif ( $action === 'swiss-system-new-round' ) {
        if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
          $existing_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, '', $tournament_round );
          if ( ! $existing_results ) {
            Ekc_Swiss_System_Helper::calculate_and_store_next_round( $tournament, $tournament_round );
          }
        }
        $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'swiss-system-store-ranking' ) {
        if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
          $this->store_ranking( $tournament_id );
        }
        $this->show_swiss_system( $tournament_id, null, true );
      }
    }
  }
  
  private function update_results( $existing_results ) {
    $db = new Ekc_Database_Access();
    foreach ( $existing_results as $existing_result ) {
      $post_result = $this->extract_result( $existing_result->get_result_id() );
      $existing_result->set_pitch( $post_result->get_pitch() );
      $existing_result->set_team1_id( $post_result->get_team1_id() );
      $existing_result->set_team2_id( $post_result->get_team2_id() );
      $existing_result->set_team1_placeholder( $post_result->get_team1_placeholder() );
      $existing_result->set_team2_placeholder( $post_result->get_team2_placeholder() );
      $existing_result->set_team1_score( $post_result->get_team1_score() );
      $existing_result->set_team2_score( $post_result->get_team2_score() );
      $db->update_tournament_result( $existing_result );
    }
  }

  private function extract_result( $result_id ) {
    $validation_helper = new Ekc_Validation_Helper();
    $result = new Ekc_Result();
    $result->set_pitch( $validation_helper->validate_post_text( 'pitch-' . $result_id ) );
    $result->set_team1_id( $validation_helper->validate_post_dropdown_key( 'team1-' . $result_id ) );
    $result->set_team1_placeholder( $validation_helper->validate_post_text( 'team1-placeholder-' . $result_id ) );
    $result->set_team1_score( $validation_helper->validate_post_integer( 'team1-score-' . $result_id ) );
    $result->set_team2_id( $validation_helper->validate_post_dropdown_key( 'team2-' . $result_id ) );
    $result->set_team2_score( $validation_helper->validate_post_integer( 'team2-score-' . $result_id ) ); 
    $result->set_team2_placeholder( $validation_helper->validate_post_text( 'team2-placeholder-' . $result_id ) );
    return $result;
  }

  public function show_swiss_system( $tournament_id, $tournament_round, $show_ranking = false ) {
    $db = new Ekc_Database_Access();
    $current_round = $db->get_current_swiss_system_round( $tournament_id );
    if ( empty( $tournament_round ) || $tournament_round > $current_round ) {
      $tournament_round = $current_round;
    }
    if ( ! $tournament_round ) {
      $tournament_round = 1;
    }
    $results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, $tournament_round );
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $max_number_of_rounds = $tournament->get_swiss_system_rounds();
    if ( $max_number_of_rounds > 0 && $tournament->get_swiss_system_additional_rounds() > 0) {
      $max_number_of_rounds = $max_number_of_rounds + $tournament->get_swiss_system_additional_rounds();
    } 

?>
  <div class="wrap">
    <h1 class="wp-heading-inline"><?php printf( '%s %s', esc_html( $tournament->get_name() ), esc_html__( 'Swiss System' ) ) ?></h1>
    <hr class="wp-header-end">

<?php
    // show a link to the current ranking, also allows to change initial score and virtual rank
    $this->show_ranking_link( $tournament_id );

    // show a link to the results of each round that has already been played
    $this->show_round_links( $tournament, $current_round );

    if ( intval( $current_round ) === intval( $tournament_round ) ) {
      $this->show_delete_round_link( $tournament, $current_round );
    }

    if ( ! $show_ranking && $current_round > 0 ) {
      $this->show_timer( $tournament, $current_round );
    }

    if ( $show_ranking ) {
      $this->show_swiss_system_ranking( $tournament_id );
    }
    elseif ( count( $results ) > 0 ) { // is the tournament already started?
      if ( intval( $current_round ) === intval( $tournament_round ) && $max_number_of_rounds > $tournament_round ) {
        // show a button to start the next round (if this is not the very last round)
        $this->show_start_round_button( $tournament, $tournament_round + 1 );
      }
      // now show the results of the requested tournament round
      $this->show_swiss_round( $tournament, $results, $tournament_round );
    }
    elseif ( intval( $tournament_round ) === 1 ) {
      $this->show_start_round_button( $tournament, $tournament_round );
    }
?>
  </div><!-- .wrap -->
<?php
  }

  private function show_round_links( $tournament, $current_round ) {
    for ( $round = 1; $round <= $current_round; $round++ ) {
      $display_round = sprintf( /* translators: %s: tournament round number */ __( 'round %s' ), $round );
      if ( $round > $tournament->get_swiss_system_rounds() ) {
        $display_round = sprintf( /* translators: %s: tournament additional round number */ __( 'additional round %s' ), $round - $tournament->get_swiss_system_rounds() );
      }
      $round_url = sprintf( '?page=ekc-swiss&action=swiss-system&tournamentid=%s&round=%s', $tournament->get_tournament_id(), $round );
      ?>
      <a href="<?php echo esc_url( $round_url ) ?>"><?php echo esc_html( $display_round ) ?></a> &nbsp;
      <?php
    }
  }

  private function show_delete_round_link( $tournament, $current_round ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $delete_url = sprintf( '?page=ekc-swiss&action=delete-round&tournamentid=%s', $tournament->get_tournament_id() );
    $delete_url = $nonce_helper->nonce_url( $delete_url, $nonce_helper->nonce_text( 'delete-round', 'tournament', $tournament->get_tournament_id() ) );
    ?>
    <span class="delete ekc-page-delete-link" >
    <a href="<?php echo esc_url( $delete_url ) ?>"><?php printf( /* translators: %s: tournament round number */ esc_html__( 'delete round %s' ), esc_html( $current_round ) ) ?></a>
    </span>
    <?php
  }

  private function show_ranking_link( $tournament_id ) {
    $ranking_url = sprintf( '?page=ekc-swiss&action=swiss-system-ranking&tournamentid=%s', $tournament_id );
    ?>
    <a href="<?php echo esc_url( $ranking_url ) ?>"><?php esc_html_e( 'ranking' ) ?></a> &nbsp;
    <?php    
  }

  private function show_start_round_button( $tournament, $next_round ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' ); 

    $button_disabled = '';
    $button_label = sprintf( /* translators: %s: tournament round number */ __( 'Start round %s' ), $next_round );
    if ( $next_round > $tournament->get_swiss_system_rounds() ) {
      $button_label = sprintf( /* translators: %s: tournament additional round number */ __( 'Start additional round %s' ), $next_round - $tournament->get_swiss_system_rounds() );
    }
    if ( intval( $next_round ) === 1 ) {
      $button_label = __( 'Start tournament!' );
      ?><p>When starting the tournament, all <i>active</i> players/teams are considered.
           Make sure that all players/teams on the waiting list are set to inactive!</p>
        <p>If possible, always try to run a Swiss System tournament with an even number of players/teams.
           If the number of players/teams is odd, an additional player/team &quot;BYE&quot; is automatically added.</p> <?php
      
      if ( $tournament->get_swiss_system_pitch_limit() ) {
        $helper = new Ekc_Swiss_System_Helper();
        if ( $helper->is_pitch_limit_mode( $tournament ) ) {
          if ( $helper->is_pitch_limit_valid( $tournament ) ) {
            ?><p>Note: The number of players/teams exceeds the number of available pitches for this tournament. 
            Additional &quot;BYEs&quot; will be added to match the number of players/teams.</p><?php
          }
          else {
            $button_disabled = 'disabled';
            ?><p><strong>Warning:</strong> The number of players/teams exceeds the number of available pitches for this tournament. 
            Please reduce the number of players/teams or the number of rounds, or increase the number of available pitches.</p><?php
          }
        } 
      } 
    }
?>
<!-- onsubmit handler for validation defined in admin.js -->
<form class="ekc-form confirm" id="swiss-system-new-round-form" method="post" action="<?php echo esc_url( '?page=' . $page ) ?>" accept-charset="utf-8">
  <fieldset>
    <div class="ekc-controls">
        <button type="submit" <?php echo esc_attr( $button_disabled ) ?> class="ekc-button ekc-button-primary button"><?php echo esc_html( $button_label ) ?></button>
        <p id="swiss-system-new-round-form-validation-text"></p>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php echo esc_attr( $next_round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-new-round" />
        <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( 'swiss-system-new-round', 'tournament', $tournament->get_tournament_id() ) ) ?>
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_timer( $tournament, $current_round ) {
    if ( ! $tournament->get_swiss_system_round_time() > 0 && ! $tournament->get_swiss_system_tiebreak_time() > 0) {
      return;
    }

    $nonce_helper = new Ekc_Nonce_Helper();
    $db = new Ekc_Database_Access();
    $round_start_time = $db->get_tournament_round_start( $tournament->get_tournament_id(), $current_round );
    $timer_url = sprintf( '?page=ekc-swiss&action=swiss-system-start-timer&tournamentid=%s&round=%s', $tournament->get_tournament_id(), $current_round );
    $timer_url = $nonce_helper->nonce_url( $timer_url, $nonce_helper->nonce_text( 'swiss-system-start-timer', 'tournament', $tournament->get_tournament_id() ) );

    if ( $round_start_time ) {
      $now = new DateTime();
      ?><p><?php
      printf( /* translators: 1: tournament round number 2: round start time */ esc_html__( 'round %1$s started at %2$s.' ), esc_html( $current_round ), esc_html( $round_start_time ) );

      $is_round_finished = false;
      if ( $tournament->get_swiss_system_round_time() > 0 ) {
        $round_end_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
        $round_end_date->add(new DateInterval('PT' . ($tournament->get_swiss_system_round_time()) . 'M')); // add minutes
        $time_left = 0;
        if ( $round_end_date > $now ) {
          $time_left = intval( $now->diff( $round_end_date )->format('%i') ) + 1; // i for minutes, +1 for 'rounding up' 
        }
        else {
          $is_round_finished = true;
        }
        printf( /* translators: %s: tournament round number */ esc_html__( ' %s minutes left for round.' ), esc_html( $time_left ) );
      }
      if ( ! $is_round_finished && $tournament->get_swiss_system_tiebreak_time() > 0 ) {
        $tiebreak_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
        $tiebreak_date->add(new DateInterval('PT' . ($tournament->get_swiss_system_tiebreak_time()) . 'M')); // add minutes
        if ( $tiebreak_date > $now ) {
          $time_until_tiebreak = intval( $now->diff( $tiebreak_date )->format('%i') ) + 1; // i for minutes, +1 for 'rounding up'
          printf( /* translators: %s: tournament round number */ esc_html__( ' %s minutes until tie break.' ), esc_html( $time_until_tiebreak ) );
        }
        else {
          $time_since_tiebreak = intval( $tiebreak_date->diff( $now )->format('%i') ); // i for minutes
          if ( $time_since_tiebreak < 30 ) {
            printf( /* translators: %s: tournament round number */ esc_html__( ' %s minutes since tie break.' ), esc_html( $time_since_tiebreak ) );
          }
        }
      }

      ?>&nbsp;<a href="<?php echo esc_url( $timer_url ) ?>"><?php esc_html_e( 'reset timer' ) ?></a> &nbsp;
      </p>
      <?php
    }
    else {
      ?>
      <p><?php printf( /* translators: %s: tournament round number */ esc_html__( 'timer for round %s not started yet.' ),  esc_html( $current_round ) ) ?>
      &nbsp;<a href="<?php echo esc_url( $timer_url ) ?>"><?php esc_html_e( 'start timer' ) ?></a> &nbsp;
      </p>
      <?php
    }
  }

  private function show_swiss_round( $tournament, $results_for_round, $round ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' ); 
    $teams = Ekc_Drop_Down_Helper::teams_drop_down_data( $tournament->get_tournament_id() );
?>
<form class="ekc-form" method="post" action="<?php echo esc_url( '?page=' . $page ) ?>" accept-charset="utf-8">
  <fieldset>
    <legend><h3><?php printf( /* translators: %s: tournament round number */ esc_html__( 'Results for round %s' ), esc_html( $round ) ) ?></h3></legend>
    <table>
      <thead>
        <tr><td><?php esc_html_e( 'Pitch' ) ?></td><td><?php esc_html_e( 'Teams' ) ?></td><td><?php esc_html_e( 'Result' ) ?></td></tr>
      </thead>
      <tbody>
    <?php
        usort( $results_for_round, array($this, "compare_results") );
        foreach ( $results_for_round as $result ) {
          $this->show_result( $result, $teams, $tournament->get_swiss_system_max_points_per_round() );
        }
    ?>
      </tbody>
    </table>
    <div class="ekc-controls">
        <button type="submit" class="ekc-button ekc-button-primary button"><?php printf( /* translators: %s: tournament round number */ esc_html__( 'Save all results for round %s' ), esc_html( $round ) ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php echo esc_attr( $round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store-round" />
        <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( 'swiss-system-store-round', 'tournament', $tournament->get_tournament_id() ) ) ?>
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_result( $result, $teams, $max_points_per_round ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $is_result_missing = is_null( $result->get_team1_score() ) || is_null( $result->get_team2_score() );
    $result_id = $result->get_result_id();
?>
<tr>
  <td><input id="<?php echo esc_attr( 'pitch-' . $result_id ) ?>" name="<?php echo esc_attr( 'pitch-' . $result_id ) ?>" type="text" maxlength="20" size="5" value="<?php echo esc_attr( $result->get_pitch() ) ?>" /></td>
  <td><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team1_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
      Ekc_Drop_Down_Helper::teams_drop_down( 'team1-' . $result_id, $team_id, $team ) ?>
    <input id="<?php echo esc_attr( 'team1-placeholder-' . $result_id ) ?>" name="<?php echo esc_attr( 'team1-placeholder-' . $result_id ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder' ) ?>" value="<?php echo esc_attr( $result->get_team1_placeholder() ) ?>" />
  </td>
  <td><div <?php if ( $is_result_missing ) echo 'class="ekc-result-missing"'; ?> data-resultid="<?php echo esc_attr( $result_id ) ?>"><input id="<?php echo esc_attr( 'team1-score-' . $result_id ) ?>" name="<?php echo esc_attr( 'team1-score-' . $result_id ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" value="<?php echo esc_attr( $result->get_team1_score() ) ?>" /></div></td>
  <td><a class="ekc-post-result" href="javascript:void(0);" data-resultid="<?php echo esc_attr( $result_id ) ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $nonce_helper->nonce_text( 'ekc_admin_swiss_system_store_result', 'result', $result_id  ) ) ) ?>"><?php esc_html_e( 'Save result' ) ?></a><span id="<?php echo esc_attr( 'post-result-' . $result_id ) ?>"></span></td> <!-- see admin.js for onClick handler -->
</tr>
<tr>
  <td></td>
  <td><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team2_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
      Ekc_Drop_Down_Helper::teams_drop_down( 'team2-' . $result_id, $team_id, $team ) ?>
    <input id="<?php echo esc_attr( 'team2-placeholder-' . $result_id ) ?>" name="<?php echo esc_attr( 'team2-placeholder-' . $result_id ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder' ) ?>" value="<?php echo esc_attr( $result->get_team2_placeholder() ) ?>" />
  </td>
  <td><div <?php if ( $is_result_missing ) echo 'class="ekc-result-missing"'; ?>><input id="<?php echo esc_attr( 'team2-score-' . $result_id ) ?>" name="<?php echo esc_attr( 'team2-score-' . $result_id ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" value="<?php echo esc_attr( $result->get_team2_score() ) ?>" /></div></td>
  <td></td>
</tr>

<?php
  }

  private function show_random_seed_link( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $current_round = $db->get_current_swiss_system_round( $tournament_id );
    if ( $current_round > 0) {
      return;
    }
    $nonce_helper = new Ekc_Nonce_Helper();
    $random_seed_url = sprintf( '?page=ekc-swiss&action=random-seed&tournamentid=%s', $tournament_id );
    $random_seed_url = $nonce_helper->nonce_url( $random_seed_url, $nonce_helper->nonce_text( 'random-seed', 'tournament', $tournament_id ) );
    ?>
    <p><a href="<?php echo esc_url( $random_seed_url ) ?>"><?php esc_html_e( 'Generate random seeding scores' ) ?></a></p>
    <?php
  }

  private function show_swiss_system_ranking( $tournament_id ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );

    $this->show_random_seed_link( $tournament_id );
    $db = new Ekc_Database_Access();
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $is_single_player = Ekc_Drop_Down_Helper::TEAM_SIZE_1 === $tournament->get_team_size();
    $counter = 1;
    $current_ranking = $db->get_current_swiss_system_ranking( $tournament_id );
    if ( ! $current_ranking ) {
      $current_ranking = $db->get_initial_swiss_system_ranking( $tournament_id );
    }


?>
<form class="ekc-form" method="post" action="<?php echo esc_url( '?page=' . $page ) ?>" accept-charset="utf-8">
  <fieldset>
    <legend><h3><?php esc_html_e( 'Current ranking' ) ?></h3></legend>
    <table>
      <thead>
        <tr>
          <th><?php esc_html_e( 'Rank' ) ?></th>
          <th><?php $is_single_player ? esc_html_e( 'Player' ) : esc_html_e( 'Team' ) ?></th>
          <th><?php esc_html_e( 'Total score' ) ?></th>
          <th><?php esc_html_e( 'Opponent score' ) ?></th>
          <th><?php esc_html_e( 'Seeding score' ) ?></th>
          <th><?php esc_html_e( 'Initial score' ) ?></th>
          <th><?php esc_html_e( 'Virtual rank' ) ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
    <?php
        foreach ( $current_ranking as $ranking ) {
          $team = $db->get_team_by_id( $ranking->get_team_id() );
          $this->show_ranking_row( $ranking, $team, $counter );
          $counter++;
        }
    ?>
      </tbody>
    </table>
    <div class="ekc-controls">
        <button type="submit" class="ekc-button ekc-button-primary button"><?php esc_html_e( 'Save data' ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store-ranking" />
        <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( 'swiss-system-store-ranking', 'tournament', $tournament->get_tournament_id() ) ) ?>
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_ranking_row( $ranking, $team, $rank ) {
    $team_id = $team->get_team_id();
?>
<tr>
  <td><?php echo esc_html( $rank ) ?></td>
  <td><?php echo esc_html( $team->get_name() ) ?></td>
  <td><?php echo esc_html( $ranking->get_total_score() ) ?></td>
  <td><?php echo esc_html( $ranking->get_opponent_score() ) ?></td>
  <td><input id="<?php echo esc_attr( 'seeding-score-' . $team_id ) ?>" name="<?php echo esc_attr( 'seeding-score-' . $team_id ) ?>" type="number" step="any" value="<?php echo esc_attr( $team->get_seeding_score() ) ?>" /></td>
  <td><input id="<?php echo esc_attr( 'initial-score-' . $team_id ) ?>" name="<?php echo esc_attr( 'initial-score-' . $team_id ) ?>" type="number" step="any" value="<?php echo esc_attr( $team->get_initial_score() ) ?>" /></td>
  <td><input id="<?php echo esc_attr( 'virtual-rank-' . $team_id ) ?>" name="<?php echo esc_attr( 'virtual-rank-' . $team_id ) ?>" type="number" step="any" value="<?php echo esc_attr( $team->get_virtual_rank() ) ?>" /></td>
  <?php
    $nonce_helper = new Ekc_Nonce_Helper();
    $remove_url = sprintf( '?page=ekc-swiss&action=remove-team&tournamentid=%s&teamid=%s', $team->get_tournament_id(), $team_id );
    $remove_url = $nonce_helper->nonce_url( $remove_url, $nonce_helper->nonce_text( 'remove-team', 'team', $team_id ) );
  ?>
  <td><a href="<?php echo esc_url( $remove_url ) ?>"><?php esc_html_e( 'Remove from tournament' ) ?></a></td>
</tr>
<?php
  }

  private function store_ranking( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $teams = $db->get_active_teams( $tournament_id, 0, 'asc', false ); 
    foreach( $teams as $team ) {
      $extracted_team = $this->extract_team( $team->get_team_id() );
      if ( $extracted_team 
          && ($extracted_team->get_initial_score() !== $team->get_initial_score()
              || $extracted_team->get_seeding_score() !== $team->get_seeding_score()
              || $extracted_team->get_virtual_rank() !== $team->get_virtual_rank() ) ) {
          $team->set_virtual_rank( $extracted_team->get_virtual_rank() );
          $team->set_initial_score( $extracted_team->get_initial_score() );
          $team->set_seeding_score( $extracted_team->get_seeding_score() );
          $db->update_team( $team );
      }
    }
  }

  private function extract_team( $team_id ) {
    $validation_helper = new Ekc_Validation_Helper(); 
    $initial_score_id = 'initial-score-' . $team_id;
    $seeding_score_id = 'seeding-score-' . $team_id;
    $virtual_rank_id = 'virtual-rank-' . $team_id;
    $team = new Ekc_Team();
    $team->set_initial_score( $validation_helper->validate_post_float( $initial_score_id ) );
    $team->set_seeding_score( $validation_helper->validate_post_float( $seeding_score_id ) );
    $team->set_virtual_rank( $validation_helper->validate_post_integer( $virtual_rank_id ) );
    return $team;
  }

  private function remove_team_from_tournament( $team_id ) {
    $db = new Ekc_Database_Access();
    $db->set_team_active( $team_id, false );
    $db->delete_results_for_team( $team_id );
  }

  private function compare_results($result1, $result2) {
    return $result1->get_result_id() - $result2->get_result_id();
  }

  private function start_timer( $tournament_id, $tournament_round ) {
    $db = new Ekc_Database_Access();
    $db->store_tournament_round_start( $tournament_id, $tournament_round );
  }

  private function random_seed( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $teams = $db->get_active_teams( $tournament_id, 0, 'asc', false ); 
    $random_seed = range(1, count( $teams ) );
    shuffle( $random_seed );
    $i = 0;
    foreach( $teams as $team ) {
      $team->set_seeding_score( $random_seed[$i] );
      $db->update_team( $team );
      $i++;
    }
  }

  private function delete_round( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $current_round = $db->get_current_swiss_system_round( $tournament_id );
    $db->delete_tournament_round_start( $tournament_id, $current_round );
    $db->delete_results_for_round( $tournament_id, $current_round );
  }
}

