<?php

/**
 * Admin page which allows administraton of an elimination bracket
 * for a given tournament.
 */
class Ekc_Elimination_Bracket_Admin_Page {

  public function intercept_redirect() {
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    
    if ( $page !== 'ekc-bracket' ) {
      return;
    }

    $action = $validation_helper->validate_get_text( 'action' );
    if ( ! $action ) {
      $action = $validation_helper->validate_post_text( 'action' );
    }
    if ( $action === 'elimination-bracket-store'
      || $action === 'swiss-ranking'
      || $action === 'delete' ) {
        $this->create_elimination_bracket_page();
    }
  }

	public function create_elimination_bracket_page() {

    $admin_helper = new Ekc_Admin_Helper();
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();

    $action = $validation_helper->validate_get_text( 'action' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    $bracket_type = $validation_helper->validate_get_text( 'bracket' );
    if ( ! $bracket_type ) {
      $bracket_type =  Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_GOLD; // default is gold bracket
    }

    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

		if ( $action === 'elimination-bracket' ) {
			$this->show_elimination_bracket( $tournament_id, $bracket_type );
		}
    elseif ( $action === 'swiss-ranking' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $helper = new Ekc_Elimination_Bracket_Helper();
        $helper->elimination_bracket_from_swiss_system_ranking( $tournament_id, $bracket_type );
      }
      $admin_helper->elimination_bracket_redirect( $tournament_id );
    }
    elseif ( $action === 'delete' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->delete_results( $tournament_id, $bracket_type );
      }
      $admin_helper->elimination_bracket_redirect( $tournament_id );
    }
		else {
			// handle POST
      $tournament_id = $validation_helper->validate_post_key( 'tournamentid' );
      if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
        return;
      }

      $action = $validation_helper->validate_post_text( 'action' );
      $bracket_type = $validation_helper->validate_post_text( 'bracket' );
      if ( ! $bracket_type ) {
        $bracket_type = Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_GOLD; // default is gold bracket
      }

      if ( $action === 'elimination-bracket-store'
        && $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $db = new Ekc_Database_Access();
        $tournament = $db->get_tournament_by_id( $tournament_id );
        $stage = Ekc_Elimination_Bracket_Helper::get_stage_for_bracket_type( $bracket_type );
        $elimination_rounds = Ekc_Elimination_Bracket_Helper::get_elimination_rounds_for_bracket_type( $tournament, $bracket_type );
        $results = $db->get_tournament_results( $tournament_id, $stage, '', null );
        
        
        foreach ( Ekc_Elimination_Bracket_Helper::get_result_types( $elimination_rounds ) as $result_type ) {
          $this->insert_or_update_result( $db, $stage, $results, $result_type );
        }
        if ( $tournament->is_auto_backup_enabled() ) {
          $helper = new Ekc_Backup_Helper();
          $helper->store_backup( $tournament_id );
        }
      }
      $admin_helper->elimination_bracket_redirect( $tournament_id );
		}
  }
  
  private function insert_or_update_result ( $db, $stage, $results, $result_type ) {
    $existing_result = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, $result_type );
    $post_result = $this->extract_result( $stage, $result_type );
    if ( $existing_result ) {
      // update existing result
      $post_result->set_result_id( $existing_result->get_result_id() );
    }
    $db->insert_or_update_tournament_result( $post_result );
  }

  private function extract_result( $stage, $result_type ) {
    $validation_helper = new Ekc_Validation_Helper();
    
    $result = new Ekc_Result();
    $result->set_stage( $stage );
    $result->set_result_type( $result_type );
    $result->set_tournament_id( $validation_helper->validate_post_key( 'tournamentid' ) );
    $result->set_pitch( $validation_helper->validate_post_text( 'pitch-' . $result_type ) );
    $result->set_team1_id( $validation_helper->validate_post_dropdown_key( 'team1-' . $result_type ) );
    $result->set_team1_score( $validation_helper->validate_post_integer( 'team1-score-' . $result_type ) );
    $result->set_team1_placeholder( $validation_helper->validate_post_text( 'team1-placeholder-' . $result_type ) );
    $result->set_team2_id( $validation_helper->validate_post_dropdown_key( 'team2-' . $result_type ) );
    $result->set_team2_score( $validation_helper->validate_post_integer( 'team2-score-' . $result_type ) );
    $result->set_team2_placeholder( $validation_helper->validate_post_text( 'team2-placeholder-' . $result_type ) );
    return $result;
  }

  public function show_elimination_bracket( $tournament_id, $bracket_type ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$db = new Ekc_Database_Access();
    $stage = Ekc_Elimination_Bracket_Helper::get_stage_for_bracket_type( $bracket_type );
    $results = $db->get_tournament_results( $tournament_id, $stage, '', null );
    $tournament = $db->get_tournament_by_id( $tournament_id );
    $teams = Ekc_Drop_Down_Helper::teams_drop_down_data( $tournament_id );
    $max_points_per_round = $tournament->get_elimination_max_points_per_round();

    $title = esc_html__( 'Elimination Bracket', 'ekc-tournament-manager' );
    if ( $tournament->get_elimination_silver_rounds() && $bracket_type === Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_SILVER ) {
      $title = esc_html__( 'Silver Bracket', 'ekc-tournament-manager' );
    }
    else if ( $tournament->get_elimination_silver_rounds() ) {
      $title = esc_html__( 'Gold Bracket', 'ekc-tournament-manager' );
    }

?>
  <div class="wrap">
    
    <h1 class="wp-heading-inline"><?php printf( '%s %s', esc_html( $tournament->get_name() ), $title ) ?></h1>
    <hr class="wp-header-end">

    <?php 
    $this->show_swiss_system_ranking_link( $tournament, $bracket_type, $results );
    $this->show_delete_results_link( $tournament, $bracket_type );
    $this->show_bracket_type_links( $tournament );

    $action_url = sprintf( '?page=ekc-bracket&tournamentid=%s&bracket=%s', $tournament->get_tournament_id(), $bracket_type );
    ?>

    <form class="ekc-form" method="post" action="<?php echo esc_url( $action_url ) ?>" accept-charset="utf-8">
      <fieldset>
        <div class="columns">
<?php 

    $elimination_rounds = Ekc_Elimination_Bracket_Helper::get_elimination_rounds_for_bracket_type( $tournament, $bracket_type );
    $rank_offset = Ekc_Elimination_Bracket_Helper::get_rank_offset( $tournament, $bracket_type );
    $show_rank_numbers = true; 
    if (Ekc_Elimination_Bracket_Helper::has_1_16_finals( $elimination_rounds ) ) {
      $this->show_column( __( '1/16 Finals', 'ekc-tournament-manager' ), $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_16_FINALS, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset );
      $show_rank_numbers = false;
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_8_finals( $elimination_rounds ) ) {
      $this->show_column( __( '1/8 Finals', 'ekc-tournament-manager' ), $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_8_FINALS, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset );
      $show_rank_numbers = false;
    }
    if (Ekc_Elimination_Bracket_Helper::has_1_4_finals( $elimination_rounds ) ) {
      $this->show_column( __( '1/4 Finals', 'ekc-tournament-manager' ), $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_4_FINALS, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset );
    }
    // never show rank numbers for semifinals and finals
    if (Ekc_Elimination_Bracket_Helper::has_1_2_finals( $elimination_rounds ) ) {
      $this->show_column( __( 'Semifinals', 'ekc-tournament-manager' ), $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_1_2_FINALS, $teams, $max_points_per_round, false, 0 );
    }
    $this->show_column( __( 'Finals', 'ekc-tournament-manager' ), $results, Ekc_Elimination_Bracket_Helper::BRACKET_RESULT_TYPES_FINALS, $teams, $max_points_per_round, false, 0 );
?>

        </div>
        <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary"><?php esc_html_e( 'Save results', 'ekc-tournament-manager' ) ?></button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="elimination-bracket-store" />
            <input id="bracket" name="bracket" type="hidden" value="<?php echo esc_attr( $bracket_type ) ?>" />
            <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( 'elimination-bracket-store', 'tournament', $tournament->get_tournament_id() ) ) ?>
        </div>
      </fieldset>
    </form>
  </div><!-- .wrap -->
<?php
  }

  private function show_swiss_system_ranking_link( $tournament, $bracket_type, $results ) {
    if ( count( $results ) === 0 && $tournament->get_tournament_system() === Ekc_Drop_Down_Helper::TOURNAMENT_SYSTEM_SWISS_KO ) {
      $nonce_helper = new Ekc_Nonce_Helper();
      $swiss_url = sprintf( '?page=ekc-bracket&action=swiss-ranking&tournamentid=%s&bracket=%s', $tournament->get_tournament_id(), $bracket_type );
      $swiss_url = $nonce_helper->nonce_url( $swiss_url, $nonce_helper->nonce_text( 'swiss-ranking', 'tournament', $tournament->get_tournament_id() ) );
      ?>
      <p><a href="<?php echo esc_url( $swiss_url ) ?>"><?php esc_html_e( 'populate elimination bracket from swiss system ranking', 'ekc-tournament-manager' ) ?></a></p>
      <?php
    }
  }

  private function show_delete_results_link( $tournament, $bracket_type ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $delete_url = sprintf( '?page=ekc-bracket&action=delete&tournamentid=%s&bracket=%s', $tournament->get_tournament_id(), $bracket_type );
    $delete_url = $nonce_helper->nonce_url( $delete_url, $nonce_helper->nonce_text( 'delete', 'tournament', $tournament->get_tournament_id() ) );
    ?>
    <span class="delete ekc-page-delete-link" >
    <a href="<?php echo esc_url( $delete_url ) ?>"><?php esc_html_e( 'delete results', 'ekc-tournament-manager' ) ?></a>
    </span>
    <?php
  }

  private function show_bracket_type_links( $tournament ) {
    if ( $tournament->get_elimination_silver_rounds() ) {
      $gold_bracket = esc_html__( 'Gold bracket', 'ekc-tournament-manager' );
      $silver_bracket = esc_html__( 'Silver bracket', 'ekc-tournament-manager' );
      $gold_bracket_url = sprintf( '?page=ekc-bracket&action=elimination-bracket&tournamentid=%s&bracket=%s', $tournament->get_tournament_id(), Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_GOLD );
      $silver_bracket_url = sprintf( '?page=ekc-bracket&action=elimination-bracket&tournamentid=%s&bracket=%s', $tournament->get_tournament_id(), Ekc_Elimination_Bracket_Helper::BRACKET_TYPE_SILVER );
      ?>
      <p>
        <a href="<?php echo esc_url( $gold_bracket_url ) ?>"><?php echo $gold_bracket ?></a> &nbsp;
        <a href="<?php echo esc_url( $silver_bracket_url ) ?>"><?php echo $silver_bracket ?></a>
      </p>
      <?php
    }
  }

  private function show_column( $column_name, $results, $result_types, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset ) {
    ?>
    <h3><?php echo esc_html( $column_name ) ?></h3>
    <table>
      <thead>
        <tr><td><?php esc_html_e( 'Pitch', 'ekc-tournament-manager' ) ?></td><td><?php esc_html_e( 'Teams', 'ekc-tournament-manager' ) ?></td><td><?php esc_html_e( 'Result', 'ekc-tournament-manager' ) ?></td></tr>
      </thead>
      <tbody>
    <?php
        foreach ( $result_types as $result_type ) {
          $this->show_result_if_exists( $results, $result_type, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset );
        }
    ?>
      </tbody>
      </table>
    <?php
  }

  private function show_result_if_exists( $results, $result_type, $teams, $max_points_per_round, $show_rank_numbers, $rank_offset ) {
    $result = Ekc_Elimination_Bracket_Helper::get_result_for_result_type( $results, $result_type);
    $rank_numbers = [];
    if ( $show_rank_numbers ) {
      $rank_numbers = Ekc_Elimination_Bracket_Helper::get_rank_numbers_for_result_type( $result_type );
    }
    if ( $result ) {
      $this->show_result( $result, $teams, $max_points_per_round, $rank_numbers, $rank_offset );
    }
    else {
      $this->empty_result( $result_type, $max_points_per_round, $rank_numbers, $rank_offset );
    }
  }

  private function show_result( $result, $teams, $max_points_per_round, $rank_numbers, $rank_offset ) {
    $result_type = $result->get_result_type();
?>
<tr>
  <td><input id="<?php echo esc_attr( 'pitch-' . $result_type ) ?>" name="<?php echo esc_attr( 'pitch-' . $result_type ) ?>" type="text" maxlength="20" size="5" value="<?php echo esc_attr( $result->get_pitch() ) ?>" /></td>
  <td><?php 
        $team_id = Ekc_Drop_Down_Helper::none_if_empty( $result->get_team1_id() );
        $team = '';
        if ( array_key_exists( $team_id, $teams ) ) {
          $team = $teams[$team_id];
        }
        Ekc_Drop_Down_Helper::teams_drop_down( 'team1-' . $result_type, $team_id, $team ) ?>
        <input id="<?php echo esc_attr( 'team1-placeholder-' . $result_type ) ?>" name="<?php echo esc_attr( 'team1-placeholder-' . $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder', 'ekc-tournament-manager' ) ?>" value="<?php echo esc_attr( $result->get_team1_placeholder() ) ?>" />
  </td>
  <td><input id="<?php echo esc_attr( 'team1-score-' . $result_type ) ?>" name="<?php echo esc_attr( 'team1-score-' . $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" value="<?php echo esc_attr( $result->get_team1_score() ) ?>" />
    <?php 
        if ( count( $rank_numbers ) > 0 ) { ?>
          <span>(<?php echo esc_html( $rank_numbers[0] + $rank_offset ) ?>)</span><?php
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
        Ekc_Drop_Down_Helper::teams_drop_down( 'team2-' . $result_type, $team_id, $team ) ?>
        <input id="<?php echo esc_attr( 'team2-placeholder-' . $result_type ) ?>" name="<?php echo esc_attr( 'team2-placeholder-' . $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder', 'ekc-tournament-manager' ) ?>" value="<?php echo esc_attr( $result->get_team2_placeholder() ) ?>" />
  </td>
  <td><input id="<?php echo esc_attr( 'team2-score-' . $result_type ) ?>" name="<?php echo esc_attr( 'team2-score-' . $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" value="<?php echo esc_attr( $result->get_team2_score() ) ?>" />
    <?php 
        if ( count( $rank_numbers ) > 0 ) { ?>
          <span>(<?php echo esc_html( $rank_numbers[1] + $rank_offset ) ?>)</span><?php
        } ?>
  </td>
</tr>

<?php
  }

  private function empty_result( $result_type, $max_points_per_round, $rank_numbers, $rank_offset ) {
    ?>
    <tr>
      <td><input id="<?php echo esc_attr( 'pitch-' . $result_type ) ?>" name="<?php echo esc_attr( 'pitch-' . $result_type ) ?>" type="text" maxlength="20" size="5" /></td>
      <td><?php
          Ekc_Drop_Down_Helper::teams_drop_down( 'team1-' . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="<?php echo esc_attr( 'team1-placeholder-' . $result_type ) ?>" name="<?php echo esc_attr( 'team1-placeholder-' . $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder', 'ekc-tournament-manager' ) ?>" />
      </td>
      <td><input id="<?php echo esc_attr( 'team1-score-' . $result_type ) ?>" name="<?php echo esc_attr( 'team1-score-' . $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" />
        <?php
          if ( count( $rank_numbers ) > 0 ) { ?>
            <span>(<?php echo esc_html( $rank_numbers[0] + $rank_offset ) ?>)</span><?php
          } ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td><?php
          Ekc_Drop_Down_Helper::teams_drop_down( 'team2-' . $result_type, Ekc_Drop_Down_Helper::SELECTION_NONE, '' ) ?>
          <input id="<?php echo esc_attr( 'team2-placeholder-' . $result_type ) ?>" name="<?php echo esc_attr( 'team2-placeholder-' . $result_type ) ?>" type="text" maxlength="500" size="20" placeholder="<?php esc_attr_e( 'Placeholder', 'ekc-tournament-manager' ) ?>" />
      </td>
      <td><input id="<?php echo esc_attr( 'team2-score-' . $result_type ) ?>" name="<?php echo esc_attr( 'team2-score-' . $result_type ) ?>" type="number" size="5" step="any" min="0" max="<?php echo esc_attr( $max_points_per_round ) ?>" />
        <?php
          if ( count( $rank_numbers ) > 0 ) { ?>
            <span>(<?php echo esc_html( $rank_numbers[1] + $rank_offset ) ?>)</span><?php
          } ?>
      </td>
    </tr>
    
    <?php
  }

  private function delete_results( $tournament_id, $bracket_type ) {
    $db = new Ekc_Database_Access();
    $stage = Ekc_Elimination_Bracket_Helper::get_stage_for_bracket_type( $bracket_type );
    $db->delete_results_for_stage( $tournament_id, $stage );
  }
}

