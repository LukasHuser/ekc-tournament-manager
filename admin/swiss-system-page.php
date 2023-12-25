<?php

/**
 * Admin page which allows administraton of Swiss System tournament
 */
class Ekc_Swiss_System_Admin_Page {

  public function intercept_redirect() {
    $page = ( isset($_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    if ( ! $page === 'ekc-swiss' ) {
      return;
    }

    $action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    if ( ! $action ) {
      $action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
    }
    if ( $action === 'swiss-system-start-timer'
      || $action === 'delete-round'
      || $action === 'swiss-system-store-round'
      || $action === 'swiss-system-new-round') {
        $this->create_swiss_system_page();
      }
  }

	public function create_swiss_system_page() {
	
    $admin_helper = new Ekc_Admin_Helper();
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    $tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    $team_id = ( isset($_GET['teamid'] ) ) ? sanitize_key( wp_unslash( $_GET['teamid'] ) ) : null;
    $tournament_round = ( isset($_GET['round'] ) ) ? sanitize_key( wp_unslash( $_GET['round'] ) ) : null;
		if ( $action === 'swiss-system' ) {
			$this->show_swiss_system( $tournament_id, $tournament_round );
    }
    elseif ( $action === 'swiss-system-start-timer' ) {
      $this->start_timer( $tournament_id, $tournament_round );
      $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
    }
    elseif ( $action === 'swiss-system-ranking' ) {
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'remove-team' ) {
      $this->remove_team_from_tournament( $team_id );
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'random-seed' ) {
      $this->random_seed( $tournament_id );
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'delete-round' ) {
      $this->delete_round( $tournament_id );
      $admin_helper->swiss_system_redirect( $tournament_id, null );
    }
		else {
			// handle POST
      $db = new Ekc_Database_Access();
      $tournament_id = ( isset($_POST['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) : null;	
      $tournament = $db->get_tournament_by_id( $tournament_id );
      $tournament_round = ( isset($_POST['tournamentround'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentround'] ) ) : null;
      $result_id = ( isset($_POST['resultid'] ) ) ? sanitize_key( wp_unslash( $_POST['resultid'] ) ) : null;
      $action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
      if ( $action === 'swiss-system-store-round' ) {
        $existing_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, '', $tournament_round );
        $this->update_results( $existing_results );
        if ( $tournament->is_auto_backup_enabled() ) {
          $helper = new Ekc_Backup_Helper();
          $helper->store_backup( $tournament_id );
        }
        $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'ekc_admin_swiss_system_store_result' ) {
        if ( !check_ajax_referer( 'ekc_admin_swiss_system_store_result', 'nonce' ) ) {
          _e('<span class="dashicons dashicons-no"></span>');
          wp_die();
        }
        $existing_result = $db->get_tournament_result_by_id( $result_id );
        $this->update_results( array( $existing_result ) );
        _e('<span class="dashicons dashicons-yes"></span>');
        wp_die();
      }
      elseif ( $action === 'swiss-system-new-round' ) {
        $existing_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, '', $tournament_round );
        if ( ! $existing_results ) {
          Ekc_Swiss_System_Helper::calculate_and_store_next_round( $tournament, $tournament_round );
        }
        $admin_helper->swiss_system_redirect( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'swiss-system-store-ranking' ) {
        $this->store_ranking( $tournament_id );
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
    $result = new Ekc_Result();
    if ( isset($_POST['pitch-' . $result_id] ) ) {
      $result->set_pitch( sanitize_text_field( wp_unslash( $_POST['pitch-' . $result_id] ) ) );
    }
    if ( isset($_POST['team1-' . $result_id] ) ) {
      $result->set_team1_id( Ekc_Type_Helper::opt_intval( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['team1-' . $result_id] ) ) ) ) );
    }
    if ( isset($_POST['team1-placeholder-' . $result_id] ) ) {
      $result->set_team1_placeholder( sanitize_text_field( wp_unslash( $_POST['team1-placeholder-' . $result_id] ) ) );
    }
    if ( isset($_POST['team1-score-' . $result_id] ) ) {
      $result->set_team1_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['team1-score-' . $result_id] ) ) ) );
    }
    if ( isset($_POST['team2-' . $result_id] ) ) {
      $result->set_team2_id( Ekc_Type_Helper::opt_intval( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['team2-' . $result_id] ) ) ) ) );
    }
    if ( isset($_POST['team2-score-' . $result_id] ) ) {
      $result->set_team2_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['team2-score-' . $result_id] ) ) ) ); 
    }
    if ( isset($_POST['team2-placeholder-' . $result_id] ) ) {
      $result->set_team2_placeholder( sanitize_text_field( wp_unslash( $_POST['team2-placeholder-' . $result_id] ) ) );
    }
    return $result;
  }


  public function show_swiss_system( $tournament_id, $tournament_round, $show_ranking = false ) {
    $db = new Ekc_Database_Access();
    $current_round = $db->get_current_swiss_system_round( $tournament_id );
    if ( empty( $tournament_round ) or $tournament_round > $current_round ) {
      $tournament_round = $current_round;
    }
    if ( ! $tournament_round ) {
      $tournament_round = 1;
    }
    $results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, null, $tournament_round );
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $max_number_of_rounds = $tournament->get_swiss_system_rounds();
    if ( $max_number_of_rounds > 0 and $tournament->get_swiss_system_additional_rounds() > 0) {
      $max_number_of_rounds = $max_number_of_rounds + $tournament->get_swiss_system_additional_rounds();
    } 

?>
  <div class="wrap">
    <h2><?php esc_html_e( $tournament->get_name() ) ?> Swiss System</h2>
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
    elseif ( count( $results ) > 0) { // is the tournament already started?
      if ( intval( $current_round ) === intval( $tournament_round ) and $max_number_of_rounds > $tournament_round ) {
        // show a button to start the next round (if this is not the very last round)
        $this->show_start_round_button( $tournament, $tournament_round + 1 );
      }
      // now show the results of the requested tournament round
      $this->show_swiss_round( $tournament, $results, $tournament_round );
    }
    elseif ( intval( $tournament_round ) === 1) {
      $this->show_start_round_button( $tournament, $tournament_round );
    }
?>
  </div><!-- .wrap -->
<?php
  }

  private function show_round_links( $tournament, $current_round ) {
    for ( $round = 1; $round <= $current_round; $round++ ) {
      $display_round = 'round ' . $round;
      if ( $round > $tournament->get_swiss_system_rounds() ) {
        $display_round = 'additional round ' . ($round - $tournament->get_swiss_system_rounds() );
      }
      ?>
      <a href="?page=ekc-swiss&amp;action=swiss-system&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;round=<?php esc_html_e( $round ) ?>"><?php esc_html_e( $display_round ) ?></a> &nbsp;
      <?php
    }
  }

  private function show_delete_round_link( $tournament, $current_round ) {
    ?>
    <span class="delete" style="text-align:right; display:block" >
    <a href="?page=ekc-swiss&amp;action=delete-round&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>">delete round <?php esc_html_e( $current_round ) ?></a> &nbsp;
    </span>
    <?php
  }

  private function show_ranking_link( $tournament_id ) {
    ?>
    <a href="?page=ekc-swiss&amp;action=swiss-system-ranking&amp;tournamentid=<?php esc_html_e( $tournament_id ) ?>">ranking</a> &nbsp;
    <?php    
  }

  private function show_start_round_button( $tournament, $next_round ) {
    $button_disabled = '';
    $button_label = 'Start round ' . $next_round;
    if ( $next_round > $tournament->get_swiss_system_rounds() ) {
      $button_label = 'Start additional round ' . ($next_round - $tournament->get_swiss_system_rounds() );
    }
    if ( intval( $next_round ) === 1 ) {
      $button_label = 'Start tournament!';
      ?><p>When starting the tournament, all <i>active</i> players/teams are considered.
           Make sure that all players/teams on the waiting list are set to inactive!</p>
        <p>If possible, always try to run a Swiss System tournament with an even number of players/teams.
           If the number of players/teams is odd, an additional player/team "BYE" is automatically added.</p> <?php
      
      if ( $tournament->get_swiss_system_pitch_limit() ) {
        $helper = new Ekc_Swiss_System_Helper();
        if ( $helper->is_pitch_limit_mode( $tournament ) ) {
          if ( $helper->is_pitch_limit_valid( $tournament ) ) {
            ?><p>Note: The number of players/teams exceeds the number of available pitches for this tournament. 
            Additional "BYEs" will be added to match the number of players/teams.</p><?php
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
<form class="ekc-form confirm" id="swiss-system-new-round-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
  <fieldset>
    <div class="ekc-controls">
        <button type="submit" <?php esc_html_e( $button_disabled ) ?> class="ekc-button ekc-button-primary button"><?php esc_html_e( $button_label ) ?></button>
        <p id="swiss-system-new-round-form-validation-text"></p>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php esc_html_e( $next_round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-new-round" />
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_timer( $tournament, $current_round ) {
    if ( ! $tournament->get_swiss_system_round_time() > 0 && ! $tournament->get_swiss_system_tiebreak_time() > 0) {
      return;
    }

    $db = new Ekc_Database_Access();
    $round_start_time = $db->get_tournament_round_start( $tournament->get_tournament_id(), $current_round );

    if ( $round_start_time ) {
      $now = new DateTime();
      ?><p>round <?php _e( $current_round ) ?> started at <?php _e( $round_start_time ) ?>.<?php    

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
        ?> <strong><?php _e( $time_left ) ?></strong> minutes left for round.<?php
      }
      if ( ! $is_round_finished && $tournament->get_swiss_system_tiebreak_time() > 0 ) {
        $tiebreak_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $round_start_time );
        $tiebreak_date->add(new DateInterval('PT' . ($tournament->get_swiss_system_tiebreak_time()) . 'M')); // add minutes
        if ( $tiebreak_date > $now ) {
          $time_until_tiebreak = intval( $now->diff( $tiebreak_date )->format('%i') ) + 1; // i for minutes, +1 for 'rounding up'
          ?> <strong>&nbsp;<?php _e( $time_until_tiebreak ) ?></strong> minutes until tie break.<?php
        }
        else {
          $time_since_tiebreak = intval( $tiebreak_date->diff( $now )->format('%i') ); // i for minutes
          if ( $time_since_tiebreak < 30 ) {
            ?> <strong>&nbsp;<?php _e( $time_since_tiebreak ) ?></strong> minutes since tie break.<?php
          }
        }
      }
      ?>&nbsp;<a href="?page=ekc-swiss&amp;action=swiss-system-start-timer&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;round=<?php esc_html_e( $current_round ) ?>">reset timer</a> &nbsp;
      </p>
      <?php
    }
    else {
      ?>
      <p>timer for round <?php _e( $current_round ) ?> not started yet.
      &nbsp;<a href="?page=ekc-swiss&amp;action=swiss-system-start-timer&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;round=<?php esc_html_e( $current_round ) ?>">start timer</a> &nbsp;
      </p>
      <?php
    }
  }

  private function show_swiss_round( $tournament, $results_for_round, $round ) {
    $teams = Ekc_Drop_Down_Helper::teams_drop_down_data( $tournament->get_tournament_id() );
?>
<form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8" data-nonce="<?php esc_html_e( wp_create_nonce( 'ekc_admin_swiss_system_store_result' ) ) ?>">
  <fieldset>
    <legend><h3>Results for round <?php esc_html_e( $round ) ?></h3></legend>
    <table>
      <thead>
        <tr><td>Pitch</td><td>Teams</td><td>Result</td></tr>
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
        <button type="submit" class="ekc-button ekc-button-primary button">Save all results for round <?php esc_html_e( $round ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php esc_html_e( $round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store-round" />
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_result( $result, $teams, $max_points_per_round ) {
    $is_result_missing = ( is_null( $result->get_team1_score() ) || is_null( $result->get_team2_score() ) );
?>
<tr>
  <td><div class="ekc-control-group"><input id="pitch-<?php esc_html_e( $result->get_result_id() ) ?>" name="pitch-<?php esc_html_e( $result->get_result_id() ) ?>" type="text" maxlength="20" size="5" value="<?php esc_html_e( $result->get_pitch() ) ?>" /></div></td>
  <td>
    <div class="ekc-control-group"><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team1_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
      Ekc_Drop_Down_Helper::teams_drop_down("team1-" . $result->get_result_id(), $team_id, $team ) ?>
    <input id="team1-placeholder-<?php esc_html_e( $result->get_result_id() ) ?>" name="team1-placeholder-<?php esc_html_e( $result->get_result_id() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team1_placeholder() ) ?>" /></div>
  </td>
  <td><div class="ekc-control-group<?php $is_result_missing ? _e( ' ekc-result-missing' ) : _e( '' ) ?> " data-resultid="<?php esc_html_e( $result->get_result_id() ) ?>"><input id="team1-score-<?php esc_html_e( $result->get_result_id() ) ?>" name="team1-score-<?php esc_html_e( $result->get_result_id() ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" value="<?php esc_html_e( $result->get_team1_score() ) ?>" /></div></td>
  <td><a class="ekc-post-result" href="javascript:void(0);" data-resultid="<?php esc_html_e( $result->get_result_id() ) ?>">Save result</a><span id="post-result-<?php esc_html_e( $result->get_result_id() ) ?>"></span></td> <!-- see admin.js for onClick handler -->
</tr>
<tr>
  <td></td>
  <td>
    <div class="ekc-control-group"><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team2_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
      Ekc_Drop_Down_Helper::teams_drop_down("team2-" . $result->get_result_id(), $team_id, $team ) ?>
    <input id="team2-placeholder-<?php esc_html_e( $result->get_result_id() ) ?>" name="team2-placeholder-<?php esc_html_e( $result->get_result_id() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team2_placeholder() ) ?>" /></div>
  </td>
  <td><div class="ekc-control-group<?php $is_result_missing ? _e( ' ekc-result-missing' ) : _e( '' ) ?>"><input id="team2-score-<?php esc_html_e( $result->get_result_id() ) ?>" name="team2-score-<?php esc_html_e( $result->get_result_id() ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" value="<?php esc_html_e( $result->get_team2_score() ) ?>" /></div></td>
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
    ?>
    <p><a href="?page=ekc-swiss&amp;action=random-seed&amp;tournamentid=<?php esc_html_e( $tournament_id ) ?>">Generate random seeding scores</a></p>
    <?php
  }

  private function show_swiss_system_ranking( $tournament_id ) {
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
<form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
  <fieldset>
    <legend><h3>Current ranking</h3></legend>
    <table>
      <thead>
        <tr><th>Rank</th><th><?php $is_single_player ? _e('Player') : _e('Team') ?></th><th>Total score</th><th>Opponent score</th><th>Seeding score</th><th>Initial score</th><th>Virtual rank</th><th></th></tr>
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
        <button type="submit" class="ekc-button ekc-button-primary button">Save data</button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store-ranking" />
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_ranking_row( $ranking, $team, $rank ) {
?>
<tr>
  <td><?php _e( $rank ) ?></td>
  <td><?php esc_html_e( $team->get_name() ) ?></td>
  <td><?php _e( $ranking->get_total_score() ) ?></td>
  <td><?php _e( $ranking->get_opponent_score() ) ?></td>
  <td><div class="ekc-control-group"><input id="seeding-score-<?php esc_html_e( $team->get_team_id() ) ?>" name="seeding-score-<?php esc_html_e( $team->get_team_id() ) ?>" type="number" step="any" value="<?php esc_html_e( $team->get_seeding_score() ) ?>" /></div></td>
  <td><div class="ekc-control-group"><input id="initial-score-<?php esc_html_e( $team->get_team_id() ) ?>" name="initial-score-<?php esc_html_e( $team->get_team_id() ) ?>" type="number" step="any" value="<?php esc_html_e( $team->get_initial_score() ) ?>" /></div></td>
  <td><div class="ekc-control-group"><input id="virtual-rank-<?php esc_html_e( $team->get_team_id() ) ?>" name="virtual-rank-<?php esc_html_e( $team->get_team_id() ) ?>" type="number" step="any" value="<?php esc_html_e( $team->get_virtual_rank() ) ?>" /></div></td>
  <td><a href="?page=ekc-swiss&amp;action=remove-team&amp;tournamentid=<?php esc_html_e( $team->get_tournament_id() ) ?>&amp;teamid=<?php esc_html_e( $team->get_team_id() ) ?>">Remove from tournament</a></td>
</tr>
<?php
  }

  private function store_ranking( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $teams = $db->get_active_teams( $tournament_id );
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
    $initial_score_id = 'initial-score-' . $team_id;
    $seeding_score_id = 'seeding-score-' . $team_id;
    $virtual_rank_id = 'virtual-rank-' . $team_id;
    if ( ! isset($_POST[$initial_score_id]) && ! isset($_POST[$seeding_score_id]) && ! isset($_POST[$virtual_rank_id]) ) {
      return null;
    }

    $team = new Ekc_Team();
    if ( isset($_POST[$initial_score_id] ) ) {
      $team->set_initial_score( Ekc_Type_Helper::opt_floatval( sanitize_text_field( wp_unslash( $_POST[$initial_score_id] ) ) ) );
    }
    if ( isset($_POST[$seeding_score_id] ) ) {
      $team->set_seeding_score( Ekc_Type_Helper::opt_floatval( sanitize_text_field( wp_unslash( $_POST[$seeding_score_id] ) ) ) );
    }
    if ( isset($_POST[$virtual_rank_id] ) ) {
      $team->set_virtual_rank( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST[$virtual_rank_id] ) ) ) );
    }
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
    $teams = $db->get_active_teams( $tournament_id );
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

