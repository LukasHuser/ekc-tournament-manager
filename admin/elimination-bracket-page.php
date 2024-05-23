<?php

/**
 * Admin page which allows administraton of an elimination bracket
 * for a given tournament.
 */
class Ekc_Elimination_Bracket_Admin_Page {

  public function intercept_redirect() {
    $page = ( isset($_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    if ( $page !== 'ekc-bracket' ) {
      return;
    }

    $action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    if ( ! $action ) {
      $action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
    }
    if ( $action === 'elimination-bracket-store'
      || $action === 'swiss-ranking'
      || $action === 'delete' ) {
        $this->create_elimination_bracket_page();
    }
  }

	public function create_elimination_bracket_page() {

    $admin_helper = new Ekc_Admin_Helper();
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

		if ( $action === 'elimination-bracket' ) {
			$this->show_elimination_bracket( $tournament_id );
		}
    elseif ( $action === 'swiss-ranking' ) {
      $helper = new Ekc_Elimination_Bracket_Helper();
      $helper->elimination_bracket_from_swiss_system_ranking( $tournament_id );
      $admin_helper->elimination_bracket_redirect( $tournament_id );
    }
    elseif ( $action === 'delete' ) {
      $this->delete_results( $tournament_id );
      $admin_helper->elimination_bracket_redirect( $tournament_id );
    }
		else {
			// handle POST
      $tournament_id = ( isset($_POST['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) : null;
      if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
        return;
      }

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
        $admin_helper->elimination_bracket_redirect( $tournament_id );
      }
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
    $max_points_per_round = $tournament->get_elimination_max_points_per_round();

?>
  <div class="wrap">
    
    <h1 class="wp-heading-inline"><?php esc_html_e( $tournament->get_name() . ' ' ); _e('Elimination Bracket') ?></h1>
    <hr class="wp-header-end">

    <?php 
    $this->show_swiss_system_ranking_link( $tournament, $results );
    $this->show_delete_results_link( $tournament );
    ?>

    <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
      <fieldset>
        <div class="columns">
<?php 
    $show_rank_numbers = true; 
    if (Ekc_Elimination_Bracket_Helper::has_1_16_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/16 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_16_FINALS, $teams, $max_points_per_round, $show_rank_numbers );
      $show_rank_numbers = false;
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_8_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/8 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS, $teams, $max_points_per_round, $show_rank_numbers );
      $show_rank_numbers = false;
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_4_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "1/4 Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS, $teams, $max_points_per_round, $show_rank_numbers );
    }
    // never show rank numbers for semifinals and finals
    if (Ekc_Elimination_Bracket_Helper::has_1_2_finals( $tournament->get_elimination_rounds() ) ) {
      $this->show_column( "Semifinals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS, $teams, $max_points_per_round, false );
    }
    $this->show_column( "Finals", $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS, $teams, $max_points_per_round, false );
?>

        </div>
        <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary"><?php _e('Save results') ?></button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="elimination-bracket-store" />
        </div>
      </fieldset>
    </form>
  </div><!-- .wrap -->
<?php
  }

  private function show_swiss_system_ranking_link( $tournament, $results ) {
    if ( count( $results ) === 0 && $tournament->get_tournament_system() === Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM_SWISS_KO ) {
      ?>
      <p><a href="?page=ekc-bracket&amp;action=swiss-ranking&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>"><?php _e( 'populate elimination bracket from swiss system ranking' ) ?></a></p>
      <?php
    }
  }

  private function show_delete_results_link( $tournament ) {
    ?>
    <span class="delete ekc-page-delete-link" >
    <a href="?page=ekc-bracket&amp;action=delete&amp;tournamentid=<?php esc_html_e( $tournament->get_tournament_id() ) ?>"><?php _e( 'delete results' ) ?></a>
    </span>
    <?php
  }

  private function show_column( $column_name, $results, $result_types, $teams, $max_points_per_round, $show_rank_numbers ) {
    ?>
    <h3><?php esc_html_e( $column_name ) ?></h3>
    <table>
      <thead>
        <tr><td><?php _e('Pitch') ?></td><td><?php _e('Teams') ?></td><td><?php _e('Result') ?></td></tr>
      </thead>
      <tbody>
    <?php
        foreach ( $result_types as $result_type ) {
          $this->show_result_if_exists( $results, $result_type, $teams, $max_points_per_round, $show_rank_numbers );
        }
    ?>
      </tbody>
      </table>
    <?php
  }

  private function show_result_if_exists( $results, $result_type, $teams, $max_points_per_round, $show_rank_numbers ) {
    $result = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, $result_type);
    $rank_numbers = [];
    if ( $show_rank_numbers ) {
      $rank_numbers = Ekc_Elimination_Bracket_Helper::get_rank_numbers_for_result_type( $result_type );
    }
    if ( $result ) {
      $this->show_result( $result, $teams, $max_points_per_round, $rank_numbers );
    }
    else {
      $this->empty_result( $result_type, $max_points_per_round, $rank_numbers );
    }
  }

  private function show_result( $result, $teams, $max_points_per_round, $rank_numbers ) {
?>
<tr>
  <td><input id="pitch-<?php esc_html_e( $result->get_result_type() ) ?>" name="pitch-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="20" size="5" value="<?php esc_html_e( $result->get_pitch() ) ?>" /></td>
  <td><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team1_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
        Ekc_Drop_Down_Helper::teams_drop_down("team1-" . $result->get_result_type(), $team_id, $team ) ?>
        <input id="team1-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" name="team1-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team1_placeholder() ) ?>" />
  </td>
  <td><input id="team1-score-<?php esc_html_e( $result->get_result_type() ) ?>" name="team1-score-<?php esc_html_e( $result->get_result_type() ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" value="<?php esc_html_e( $result->get_team1_score() ) ?>" />
    <?php 
        if ( count( $rank_numbers ) > 0 ) { ?>
          <span>(<?php esc_html_e( $rank_numbers[0] ) ?>)</span><?php
        } ?>
  </td>
</tr>
<tr>
  <td></td>
  <td><?php
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team2_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
        Ekc_Drop_Down_Helper::teams_drop_down("team2-" . $result->get_result_type(), $team_id, $team ) ?>
        <input id="team2-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" name="team2-placeholder-<?php esc_html_e( $result->get_result_type() ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" value="<?php esc_html_e( $result->get_team2_placeholder() ) ?>" />
  </td>
  <td><input id="team2-score-<?php esc_html_e( $result->get_result_type() ) ?>" name="team2-score-<?php esc_html_e( $result->get_result_type() ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" value="<?php esc_html_e( $result->get_team2_score() ) ?>" />
    <?php 
        if ( count( $rank_numbers ) > 0 ) { ?>
          <span>(<?php esc_html_e( $rank_numbers[1] ) ?>)</span><?php
        } ?>
  </td>
</tr>

<?php
  }

  private function empty_result( $result_type, $max_points_per_round, $rank_numbers ) {
    ?>
    <tr>
      <td><input id="pitch-<?php esc_html_e( $result_type ) ?>" name="pitch-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="20" size="5" /></td>
      <td><?php
          Ekc_Drop_Down_Helper::teams_drop_down("team1-" . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="team1-placeholder-<?php esc_html_e( $result_type ) ?>" name="team1-placeholder-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" />
      </td>
      <td><input id="team1-score-<?php esc_html_e( $result_type ) ?>" name="team1-score-<?php esc_html_e( $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" />
        <?php
          if ( count( $rank_numbers ) > 0 ) { ?>
            <span>(<?php esc_html_e( $rank_numbers[0] ) ?>)</span><?php
          } ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td><?php
          Ekc_Drop_Down_Helper::teams_drop_down("team2-" . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="team2-placeholder-<?php esc_html_e( $result_type ) ?>" name="team2-placeholder-<?php esc_html_e( $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="Placeholder" />
      </td>
      <td><input id="team2-score-<?php esc_html_e( $result_type ) ?>" name="team2-score-<?php esc_html_e( $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php esc_html_e( $max_points_per_round ) ?>" />
        <?php
          if ( count( $rank_numbers ) > 0 ) { ?>
            <span>(<?php esc_html_e( $rank_numbers[1] ) ?>)</span><?php
          } ?>
      </td>
    </tr>
    
    <?php
  }

  private function delete_results( $tournament_id ) {
    $db = new Ekc_Database_Access();
    $db->delete_results_for_stage( $tournament_id, Ekc_Drop_Down_Helper::TOURNAMENT_STAGE_KO );
  }
}

