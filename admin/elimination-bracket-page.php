<?php

/**
 * Admin page which allows administraton of an elimination bracket
 * for a given tournament.
 */
class Ekc_Elimination_Bracket_Admin_Page {

	public function create_elimination_bracket_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
		if ( $action === 'elimination-bracket' ) {
			$this->show_elimination_bracket( $tournament_id );
		}
		else {
			// handle POST
      $tournament_id = ( isset($_POST['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) : null;	
			$action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
      if ( $action === 'elimination-bracket-store' ) {
        $db = new Ekc_Database_Access();
        $results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO, '', null );
        $tournament = $db->get_tournament_by_id( $tournament_id );
        
        foreach ( Ekc_Elimination_Bracket_Helper::get_result_types( $tournament->get_elimination_rounds() ) as $result_type ) {
          $this->insert_or_update_result( $db, $results, $result_type );
        }
        if ( $tournament->is_auto_backup_enabled() ) {
          $helper = new Ekc_Backup_Helper();
          $helper->store_backup( $tournament_id );
        }
      }
			$this->show_elimination_bracket( $tournament_id );
		}
  }
  
  private function insert_or_update_result ( $db, $results, $result_type ) {
    $existing_result = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, $result_type );
    $post_result = $this->extract_result( $result_type );
    if ( $existing_result ) {
      // update existing result
      $post_result->set_result_id( $existing_result->get_result_id() );
    }
    $db->insert_or_update_tournament_result( $post_result );
  }

  private function extract_result( $result_type ) {
    $result = new Ekc_Result();
    $result->set_stage( Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO );
    $result->set_result_type( $result_type );
    $result->set_tournament_id( sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) );
    
    if ( isset($_POST['pitch-' . $result_type] ) ) {
      $result->set_pitch( sanitize_text_field( wp_unslash( $_POST['pitch-' . $result_type] ) ) );
    }
    if ( isset($_POST['team1-' . $result_type] ) ) {
      $result->set_team1_id( Ekc_Type_Helper::opt_intval( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['team1-' . $result_type] ) ) ) ) );
    }
    if ( isset($_POST['team1-placeholder-' . $result_type] ) ) {
      $result->set_team1_placeholder( sanitize_text_field( wp_unslash( $_POST['team1-placeholder-' . $result_type] ) ) );
    }
    if ( isset($_POST['team1-score-' . $result_type] ) ) {
      $result->set_team1_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['team1-score-' . $result_type] ) ) ) );
    }
    if ( isset($_POST['team2-' . $result_type] ) ) {
      $result->set_team2_id( Ekc_Type_Helper::opt_intval( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['team2-' . $result_type] ) ) ) ) );
    }
    if ( isset($_POST['team2-score-' . $result_type] ) ) {
      $result->set_team2_score( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['team2-score-' . $result_type] ) ) ) );
    }
    if ( isset($_POST['team2-placeholder-' . $result_type] ) ) {
      $result->set_team2_placeholder( sanitize_text_field( wp_unslash( $_POST['team2-placeholder-' . $result_type] ) ) );
    }
    return $result;
  }


  public function show_elimination_bracket( $tournament_id ) {
		$db = new Ekc_Database_Access();
    $results = $db->get_tournament_results( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO, '', null );
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $teams = Ekc_Drop_Down_Helper::teams_drop_down_data( $tournament_id );

?>
  <div class="wrap">
    <h2><?php esc_html_e( $tournament->get_name() ) ?> elimination bracket</h2>
    <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
      <fieldset>
        <div class="columns">
<?php
    if (Ekc_Elimination_Bracket_Helper::has_1_16_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/16 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_16_FINALS, $teams );
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_8_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/8 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS, $teams );
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_4_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/4 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS, $teams );
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_2_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "Semifinals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS, $teams );
    }
    $this->show_column( "Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS, $teams );
?>

        </div>
        <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary">Save results</button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="elimination-bracket-store" />
        </div>
      </fieldset>
    </form>
  </div><!-- .wrap -->
<?php
  }

  private function show_column( $column_name, $results, $result_types, $teams ) {
    ?>
    <h3><?php esc_html_e( $column_name ) ?></h3>
    <table>
      <thead>
        <tr><td>Pitch</td><td>Teams</td><td>Result</td></tr>
      </thead>
      <tbody>
    <?php
        foreach ( $result_types as $result_type ) {
          $this->show_result_if_exists( $results, $result_type, $teams );
        }
    ?>
      </tbody>
      </table>
    <?php
  }

  private function show_result_if_exists( $results, $result_type, $teams ) {
    $result = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, $result_type);
    if ( $result ) {
      $this->show_result( $result, $teams );
    }
    else {
      $this->empty_result( $result_type );
    }
  }

  private function show_result( $result, $teams ) {
?>
<tr>
  <td><div class="ekc-control-group"><input id="pitch-<?php esc_html_e( $result->get_result_type() ) ?>" name="pitch-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="20" size="5" value="<?php esc_html_e( $result->get_pitch() ) ?>" /></div></td>
  <td>
    <div class="ekc-control-group"><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team1_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
        Ekc_Drop_Down_Helper::teams_drop_down("team1-" . $result->get_result_type(), $team_id, $team ) ?>
        <input id="team1-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" name="team1-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team1_placeholder() ) ?>" /> </div>
  </td>
  <td><div class="ekc-control-group"><input id="team1-score-<?php esc_html_e( $result->get_result_type() ) ?>" name="team1-score-<?php esc_html_e( $result->get_result_type() ) ?>" type="number" step="any" value="<?php esc_html_e( $result->get_team1_score() ) ?>" /></div></td>
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
        Ekc_Drop_Down_Helper::teams_drop_down("team2-" . $result->get_result_type(), $team_id, $team ) ?>
        <input id="team2-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" name="team2-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team2_placeholder() ) ?>" /></div>
  </td>
  <td><div class="ekc-control-group"><input id="team2-score-<?php esc_html_e( $result->get_result_type() ) ?>" name="team2-score-<?php esc_html_e( $result->get_result_type() ) ?>" type="number" step="any" value="<?php esc_html_e( $result->get_team2_score() ) ?>" /></div></td>
</tr>

<?php
  }

  private function empty_result( $result_type ) {
    ?>
    <tr>
      <td><div class="ekc-control-group"><input id="pitch-<?php esc_html_e( $result_type ) ?>" name="pitch-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="20" size="5" /></div></td>
      <td>
        <div class="ekc-control-group"><?php Ekc_Drop_Down_Helper::teams_drop_down("team1-" . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="team1-placeholder-<?php esc_html_e( $result_type ) ?>" name="team1-placeholder-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" /></div>
      </td>
      <td><div class="ekc-control-group"><input id="team1-score-<?php esc_html_e( $result_type ) ?>" name="team1-score-<?php esc_html_e( $result_type ) ?>" type="number" step="any" /></div></td>
    </tr>
    <tr>
      <td></td>
      <td>
        <div class="ekc-control-group"><?php Ekc_Drop_Down_Helper::teams_drop_down("team2-" . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="team2-placeholder-<?php esc_html_e( $result_type ) ?>" name="team2-placeholder-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" /></div>
      </td>
      <td><div class="ekc-control-group"><input id="team2-score-<?php esc_html_e( $result_type ) ?>" name="team2-score-<?php esc_html_e( $result_type ) ?>" type="number" step="any" /></div></td>
    </tr>
    
    <?php
  }
}

