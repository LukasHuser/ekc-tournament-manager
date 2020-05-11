<?php

/**
 * Admin page which allows administraton of Swiss System tournament
 */
class Ekc_Swiss_System_Admin_Page {

	public function create_swiss_system_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    $tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    $team_id = ( isset($_GET['teamid'] ) ) ? sanitize_key( wp_unslash( $_GET['teamid'] ) ) : null;
    $tournament_round = ( isset($_GET['round'] ) ) ? sanitize_key( wp_unslash( $_GET['round'] ) ) : null;
		if ( $action === 'swiss-system' ) {
			$this->show_swiss_system( $tournament_id, $tournament_round );
    }
    elseif ( $action === 'swiss-system-ranking' ) {
      $this->show_swiss_system( $tournament_id, null, true );
    }
    elseif ( $action === 'remove-team' ) {
      $this->remove_team_from_tournament( $team_id );
      $this->show_swiss_system( $tournament_id, null, true );
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
        $this->show_swiss_system( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'swiss-system-store-result' ) {
        $existing_result = $db->get_tournament_result_by_id( $result_id );
        $this->update_results( array( $existing_result ) );
      }
      elseif ( $action === 'swiss-system-new-round' ) {
        Ekc_Swiss_System_Helper::calculate_and_store_next_round( $tournament, $tournament_round );
        $this->show_swiss_system( $tournament_id, $tournament_round );
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
    for ( $round = 1; $round <= $current_round; $round++ ) {
      $this->show_round_link( $tournament, $round );
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

  private function show_round_link( $tournament, $round ) {
    $display_round = 'round ' . $round;
    if ( $round > $tournament->get_swiss_system_rounds() ) {
      $display_round = 'additional round ' . ($round - $tournament->get_swiss_system_rounds() );
    }
    ?>
    <a href="?page=ekc-swiss&amp;action=swiss-system&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>&amp;round=<?php esc_html_e( $round ) ?>"><?php esc_html_e( $display_round ) ?></a> &nbsp;
    <?php
  }

  private function show_ranking_link( $tournament_id ) {
    ?>
    <a href="?page=ekc-swiss&amp;action=swiss-system-ranking&amp;tournamentid=<?php esc_html_e( $tournament_id ) ?>">ranking</a> &nbsp;
    <?php    
  }

  private function show_start_round_button( $tournament, $next_round ) {
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
    }
?>
<form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
  <fieldset>
    <div class="ekc-controls">
        <button type="submit" class="ekc-button ekc-button-primary"><?php esc_html_e( $button_label ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php esc_html_e( $next_round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-new-round" />
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_swiss_round( $tournament, $results_for_round, $round ) {
    $teams = Ekc_Drop_Down_Helper::teams_drop_down_data( $tournament->get_tournament_id() );
?>
<form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
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
          $this->show_result( $result, $teams );
        }
    ?>
      </tbody>
    </table>
    <div class="ekc-controls">
        <button type="submit" class="ekc-button ekc-button-primary">Save all results for round <?php esc_html_e( $round ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php esc_html_e( $round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store-round" />
    </div>
  </fieldset>
</form>
    <?php
  }

  private function show_result( $result, $teams ) {
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
  <td><div class="ekc-control-group<?php $is_result_missing ? _e( ' ekc-result-missing' ) : _e( '' ) ?>"><input id="team1-score-<?php esc_html_e( $result->get_result_id() ) ?>" name="team1-score-<?php esc_html_e( $result->get_result_id() ) ?>" type="number" step="any" value="<?php esc_html_e( $result->get_team1_score() ) ?>" /></div></td>
  <td><a class="ekc-post-result" href="javascript:void(0);" data-resultid="<?php esc_html_e( $result->get_result_id() ) ?>">Save result</a></td> <!-- see admin.js for onClick handler -->
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
  <td><div class="ekc-control-group<?php $is_result_missing ? _e( ' ekc-result-missing' ) : _e( '' ) ?>"><input id="team2-score-<?php esc_html_e( $result->get_result_id() ) ?>" name="team2-score-<?php esc_html_e( $result->get_result_id() ) ?>" type="number" step="any" value="<?php esc_html_e( $result->get_team2_score() ) ?>" /></div></td>
  <td></td>
</tr>

<?php
  }

  private function show_swiss_system_ranking( $tournament_id ) {
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
        <button type="submit" class="ekc-button ekc-button-primary">Save data</button>
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
}

