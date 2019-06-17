<?php

/**
 * Admin page which allows administraton of Swiss System tournament
 */
class Ekc_Swiss_System_Admin_Page {

	public function create_swiss_system_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    $tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    $tournament_round = ( isset($_GET['round'] ) ) ? sanitize_key( wp_unslash( $_GET['round'] ) ) : null;
		if ( $action === 'swiss-system' ) {
			$this->show_swiss_system( $tournament_id, $tournament_round );
		}
		else {
			// handle POST
      $db = new Ekc_Database_Access();
      $tournament_id = ( isset($_POST['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) : null;	
      $tournament = $db->get_tournament_by_id( $tournament_id );
      $tournament_round = ( isset($_POST['tournamentround'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentround'] ) ) : null;
      $action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
      if ( $action === 'swiss-system-store' ) {
        $existing_results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_SWISS, '', $tournament_round );
        $this->update_results( $existing_results );
        if ( $tournament->is_auto_backup_enabled() ) {
          $helper = new Ekc_Backup_Helper();
          $helper->store_backup( $tournament_id );
        }
        $this->show_swiss_system( $tournament_id, $tournament_round );
      }
      elseif ( $action === 'swiss-system-new-round' ) {
        Ekc_Swiss_System_Helper::calculate_and_store_next_round( $tournament, $tournament_round );
        $this->show_swiss_system( $tournament_id, $tournament_round );
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


  public function show_swiss_system( $tournament_id, $tournament_round ) {
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
    <h2><?php esc_html_e( $tournament->get_name() ) ?> Swiss rounds</h2>
<?php

    // show a link to the results of each round that has already been played
    for ( $round = 1; $round <= $current_round; $round++ ) {
      $this->show_round_link( $tournament_id, $round );
    }

    if ( count( $results ) > 0) { // is the tournament already started?
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

  private function show_round_link( $tournament_id, $round ) {
    ?>
    <a href="?page=ekc-swiss&amp;action=swiss-system&amp;tournamentid=<?php esc_html_e( $tournament_id ) ?>&amp;round=<?php esc_html_e( $round ) ?>">round <?php esc_html_e( $round ) ?></a> &nbsp;
    <?php
  }

  private function show_start_round_button( $tournament, $next_round ) {
    $button_label = 'Start round ' . $next_round;
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
        <button type="submit" class="ekc-button ekc-button-primary">Save results for round <?php esc_html_e( $round ) ?></button>
        <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
        <input id="tournamentround" name="tournamentround" type="hidden" value="<?php esc_html_e( $round ) ?>" />
        <input id="action" name="action" type="hidden" value="swiss-system-store" />
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
</tr>

<?php
  }

  private function compare_results($result1, $result2) {
    return $result1->get_result_id() - $result2->get_result_id();
  }
}

