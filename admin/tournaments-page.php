<?php

/**
 * Top level admin page which allows administraton of tournaments
 */
class Ekc_Tournaments_Admin_Page {

	public function create_tournaments_page() {
	
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$action = $validation_helper->validate_get_text( 'action' );
    $tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    $file_name = rawurldecode( $validation_helper->validate_get_text( 'backup' ) );
    
    if ( $action === 'new' ) {
			$this->show_new_tournament();
		}
    elseif ( $action === 'copy' ) {
			$this->show_copy_tournament( $tournament_id );
		}
		elseif ( $action === 'edit' ) {
			$this->show_edit_tournament( $tournament_id );
		}
		elseif ( $action === 'delete' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $this->delete_tournament( $tournament_id );
      }
			$this->show_tournaments();
    }
		elseif ( $action === 'backup' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ) ) ) {
        $backup_helper = new Ekc_Backup_Helper();
        $backup_helper->store_backup( $tournament_id );
      }
			$this->show_tournaments();
    }
    elseif ( $action === 'jsonimport' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'filename', $file_name ) ) ) {
        $backup_helper = new Ekc_Backup_Helper();
        $backup_helper->import_from_json( $file_name );
      }
      $this->show_tournaments();
    }
		else {
			// handle POST
			$tournament = new Ekc_Tournament();
			$has_data = false;
      $action = $validation_helper->validate_post_text( 'action' );
      $tournament_id = $validation_helper->validate_post_key( 'tournamentid' );
			if ( $tournament_id ) {
        if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
          return;
        }
				$tournament->set_tournament_id( $tournament_id );
				$has_data = true;
			}
      $code_name = $validation_helper->validate_post_text( 'codename' );
      if ( $code_name ) {
        $tournament->set_code_name( $code_name );
        $has_data = true;
      }
			if ( $has_data ) {
        if ( ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'tournament', $tournament_id ? $tournament_id : -1 ) ) ) {
          return;
        }
        $tournament->set_name( $validation_helper->validate_post_text( 'name' ) );
        $tournament->set_owner_user_id( $validation_helper->validate_post_dropdown_key( 'owner' ) );
        $tournament->set_date( $validation_helper->validate_post_text( 'date' ) );
        $tournament->set_team_size( $validation_helper->validate_post_dropdown_text( 'teamsize' ) ); 
        $tournament->set_max_teams( $validation_helper->validate_post_integer( 'maxteams' ) );
        $tournament->set_wait_list_enabled( $validation_helper->validate_post_boolean( 'waitlist') );
        $tournament->set_player_names_required( $validation_helper->validate_post_boolean( 'playernames' ) );
        $tournament->set_auto_backup_enabled( $validation_helper->validate_post_boolean( 'backup' ) );
        $tournament->set_tournament_system( $validation_helper->validate_post_dropdown_text( 'system' ) );
        $tournament->set_elimination_rounds( $validation_helper->validate_post_dropdown_text( 'eliminationrounds' ) );
        $tournament->set_elimination_silver_rounds( $validation_helper->validate_post_dropdown_text( 'eliminationsilverrounds' ) );
        $tournament->set_elimination_max_points_per_round( $validation_helper->validate_post_integer( 'eliminationmaxpoints' ) );
        $tournament->set_swiss_system_rounds( $validation_helper->validate_post_integer( 'swissrounds' ) );
        $tournament->set_swiss_system_max_points_per_round( $validation_helper->validate_post_integer( 'swissmaxpoints' ) );
        $tournament->set_swiss_system_bye_points( $validation_helper->validate_post_integer( 'swissbyepoints' ) );
        $tournament->set_swiss_system_virtual_result_points( $validation_helper->validate_post_integer( 'swissvirtualresultpoints' ) );
        $tournament->set_swiss_system_additional_rounds( $validation_helper->validate_post_integer( 'swissadditionalrounds' ) );
        $tournament->set_swiss_system_slide_match_rounds( $validation_helper->validate_post_integer( 'swissslidematchrounds' ) );
        $tournament->set_swiss_system_round_time( $validation_helper->validate_post_integer( 'swissroundtime' ) );
        $tournament->set_swiss_system_tiebreak_time( $validation_helper->validate_post_integer( 'swisstiebreaktime' ) );
        $tournament->set_swiss_system_start_pitch( $validation_helper->validate_post_integer( 'swissstartpitch' ) );
        $tournament->set_swiss_system_pitch_limit( $validation_helper->validate_post_integer( 'swisspitchlimit' ) );

				$db = new Ekc_Database_Access();
				if ( $action === 'new' ) {
					$db->insert_tournament( $tournament );
				}
				elseif ( $action === 'edit' ) {
					$db->update_tournament( $tournament );
				}
			}

			$this->show_tournaments();
		}
	}

	public function show_tournaments() {
		$tournaments_table = new Ekc_Tournaments_Table();
    $show_new_button = current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS );
    $show_backup_button = current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_BACKUPS );
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
    $new_tournament_url = sprintf( '?page=%s&action=new', $page );
?>
<div class="wrap">

  <h1 class="wp-heading-inline"><?php esc_html_e( 'EKC Tournament Manager', 'ekc-tournament-manager' ) ?></h1>
  <?php 
  if ( $show_new_button ) {
  ?><a href="<?php echo esc_url( $new_tournament_url ) ?>" class="page-title-action"><?php esc_html_e( 'New tournament', 'ekc-tournament-manager' ); ?></a>
  <?php
  }
  if ( $show_backup_button ) {
  ?><a href="?page=ekc-backup" class="page-title-action"><?php esc_html_e( 'Show backups', 'ekc-tournament-manager' ); ?></a>
  <?php
  }
  ?>
  <hr class="wp-header-end">

<?php 
  if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_READ_TOURNAMENTS ) ) {
	  $tournaments_table->prepare_items();
	  $tournaments_table->display();
  }
?>

</div><!-- .wrap -->
<?php
	}	

	public function show_new_tournament() {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS ) ) {
      return;
    }
    $this->show_tournament( 'new', __( 'Create a new tournament', 'ekc-tournament-manager' ), __( 'Create tournament', 'ekc-tournament-manager' ) );
  }

	public function show_copy_tournament( $tournament_id ) {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS ) ) {
      return;
    }
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
    $tournament->set_tournament_id( -1 ); // should never be read
    $tournament->set_name( sprintf( /* translators: %s: tournament name */ __( 'Copy of %s', 'ekc-tournament-manager' ), $tournament->get_name() ) );
    $tournament->set_code_name( 'COPY-' . $tournament->get_code_name() );
    $tournament->set_owner_user_id( wp_get_current_user()->ID );

    $this->show_tournament( 'new', __( 'Create a new tournament', 'ekc-tournament-manager' ), __( 'Create tournament', 'ekc-tournament-manager' ), $tournament );
  }

	public function show_edit_tournament( $tournament_id ) {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );

    $this->show_tournament( 'edit', __( 'Edit tournament', 'ekc-tournament-manager' ), __( 'Save tournament', 'ekc-tournament-manager' ), $tournament );
  }

	public function show_tournament( $action, $title, $button_text, $tournament = null ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
?>
  <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $title ) ?></h1>
    <hr class="wp-header-end">

    <form class="ekc-form" method="post" action="<?php echo esc_url( '?page=' . $page ) ?>" accept-charset="utf-8">
        <fieldset>
        <legend><h3><?php esc_html_e( 'Tournament', 'ekc-tournament-manager' ) ?></h3></legend>
        <div class="ekc-control-group">
          <label for="name" class="ekc-required"><?php esc_html_e( 'Name', 'ekc-tournament-manager' ) ?></label>
          <input id="name" name="name" type="text" maxlength="500" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_name() ) ?>" required />
        </div>
        <div class="ekc-control-group">
          <label for="codename" class="ekc-required"><?php esc_html_e( 'Code name', 'ekc-tournament-manager' ) ?></label>
          <div><input id="codename" name="codename" type="text" maxlength="50" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_code_name() ) ?>" required />
               <p><?php esc_html_e( 'Unique, short and descriptive identifier used to reference the tournament in WP shortcodes', 'ekc-tournament-manager' ) ?></p></div>
        </div>
          <div class="ekc-control-group">
            <label for="owner"><?php esc_html_e( 'Owner user', 'ekc-tournament-manager' ) ?></label>
            <div><?php Ekc_Drop_Down_Helper::user_drop_down( 'owner', $tournament ? Ekc_Drop_Down_Helper::none_if_empty( $tournament->get_owner_user_id() ) : wp_get_current_user()->ID ) ?>
                 <p><?php esc_html_e( 'Owner of the tournament, relevant for permission checks.', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="teamsize"><?php esc_html_e( 'Team size', 'ekc-tournament-manager' ) ?></label>
            <?php Ekc_Drop_Down_Helper::team_size_drop_down( 'teamsize', $tournament ? $tournament->get_team_size() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="system"><?php esc_html_e( 'Tournament system', 'ekc-tournament-manager' ) ?></label>
            <?php Ekc_Drop_Down_Helper::tournament_system_drop_down( 'system', $tournament ? $tournament->get_tournament_system() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="date"><?php esc_html_e( 'Date', 'ekc-tournament-manager' ) ?></label>
            <input id="date" name="date" class="ekc-datepicker" type="text" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_date() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="maxteams"><?php esc_html_e( 'Teams', 'ekc-tournament-manager' ) ?></label>
            <div><input id="maxteams" name="maxteams" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_max_teams() ) ?>" />
                 <p><?php esc_html_e( 'Maximum number of teams', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="waitlist" name="waitlist" type="checkbox" value="true" <?php if ( $tournament && $tournament->is_wait_list_enabled() ) echo 'checked' ?> />
                 <label for="waitlist"><?php esc_html_e( 'Wait list available', 'ekc-tournament-manager' ) ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="playernames" name="playernames" type="checkbox" value="true" <?php if ( $tournament && $tournament->is_player_names_required() ) echo 'checked' ?> />
                 <label for="playernames"><?php esc_html_e( 'Player names required', 'ekc-tournament-manager' ) ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="bakcup" name="backup" type="checkbox" value="true" <?php if ( $tournament && $tournament->is_auto_backup_enabled() ) echo 'checked' ?> />
                 <label for="backup"><?php esc_html_e( 'Auto backup enabled', 'ekc-tournament-manager' ) ?></label></div>
          </div>
        </fieldset>
        <fieldset>
        <legend><h3><?php esc_html_e( 'Elimination Bracket', 'ekc-tournament-manager' ) ?></h3></legend>
          <div class="ekc-control-group">
            <label for="eliminationrounds"><?php esc_html_e( 'Gold bracket', 'ekc-tournament-manager' ) ?></label>
            <?php Ekc_Drop_Down_Helper::elimination_bracket_drop_down( 'eliminationrounds', $tournament ? $tournament->get_elimination_rounds() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationsilverrounds"><?php esc_html_e( 'Silver bracket', 'ekc-tournament-manager' ) ?></label>
            <?php Ekc_Drop_Down_Helper::elimination_bracket_drop_down( 'eliminationsilverrounds', $tournament ? $tournament->get_elimination_silver_rounds() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationmaxpoints"><?php esc_html_e( 'Points per round', 'ekc-tournament-manager' ) ?></label>
            <div><input id="eliminationmaxpoints" name="eliminationmaxpoints" type="number" min="0" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_elimination_max_points_per_round() ) ?>" />
                 <p><?php esc_html_e( 'Maximum number of points per round for elimination bracket', 'ekc-tournament-manager' ) ?></p></div>
          </div>
        </fieldset>
        <fieldset>
        <legend><h3><?php esc_html_e( 'Swiss System', 'ekc-tournament-manager' ) ?></h3></legend>
          <div class="ekc-control-group">
            <label for="swissrounds"><?php esc_html_e( 'Number of rounds', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissrounds" name="swissrounds" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_rounds() ) ?>" />
                 <p><?php esc_html_e( 'Number of rounds of Swiss System', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissmaxpoints"><?php esc_html_e( 'Points per round', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissmaxpoints" name="swissmaxpoints" type="number" min="0" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_max_points_per_round() ) ?>" />
                 <p><?php esc_html_e( 'Maximum number of points per round for Swiss System', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissslidematchrounds"><?php esc_html_e( 'Pairing', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissslidematchrounds" name="swissslidematchrounds" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_slide_match_rounds() ) ?>" />
            <p><?php esc_html_e( 'Number of slide pairing rounds for Swiss System, i.e. the pairing method for the first n rounds is "slide pairing", all following rounds use "top-down pairing"', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissadditionalrounds"><?php esc_html_e( 'Additional rounds', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissadditionalrounds" name="swissadditionalrounds" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_additional_rounds() ) ?>" />
            <p><?php esc_html_e( 'Number of additional rounds of Swiss System, i.e. ranking matches after elimination bracket has started', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissbyepoints"><?php esc_html_e( 'Points for BYE', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissbyepoints" name="swissbyepoints" type="number" min="0" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_bye_points() ) ?>" />
                 <p><?php esc_html_e( 'Number of points for teams which have no opponent in case of an odd number of teams.', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissvirtualresultpoints"><?php esc_html_e( 'Points for virtual result', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissvirtualresultpoints" name="swissvirtualresultpoints" type="number" min="0" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_virtual_result_points() ) ?>" />
                 <p><?php esc_html_e( 'Points awarded for a virtual result during an additional round, i.e. points for dummy matches between teams actually playing in the elimination bracket. Relevant for opponent score of teams playing the ranking matches.', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissroundtime"><?php esc_html_e( 'Time limit', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissroundtime" name="swissroundtime" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_round_time() ) ?>" />
                 <p><?php esc_html_e( 'Time limit for a Swiss System round, setting a value will enable a timer', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swisstiebreaktime"><?php esc_html_e( 'Tie break', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swisstiebreaktime" name="swisstiebreaktime" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_tiebreak_time() ) ?>" />
                 <p><?php esc_html_e( 'Time limit until tie break starts, setting a value will enable a timer', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swissstartpitch"><?php esc_html_e( 'Start pitch number', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swissstartpitch" name="swissstartpitch" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_start_pitch() ) ?>" />
                 <p><?php esc_html_e( 'Useful if two tournaments run in parallel', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="swisspitchlimit"><?php esc_html_e( 'Available pitches', 'ekc-tournament-manager' ) ?></label>
            <div><input id="swisspitchlimit" name="swisspitchlimit" type="number" value="<?php if ( $tournament ) echo esc_attr( $tournament->get_swiss_system_pitch_limit() ) ?>" />
                 <p><?php esc_html_e( 'Number of available pitches for pitch limit mode. If the number of teams exceeds the number of pitches, additional BYEs will be added to the tournament.', 'ekc-tournament-manager' ) ?></p></div>
          </div>
        </fieldset>
        <fieldset>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary"><?php echo esc_html( $button_text ) ?></button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php if ( $tournament && $action === 'edit' ) echo esc_attr( $tournament->get_tournament_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="<?php echo esc_attr( $action ) ?>" />
            <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( $action, 'tournament', $tournament ? $tournament->get_tournament_id() : -1 ) ) ?>
          </div>
        </fieldset>
      </form>
  </div><!-- .wrap -->
<?php
	}

	public function delete_tournament( $tournament_id ) {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_DELETE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
		$db = new Ekc_Database_Access();
		$db->delete_tournament( $tournament_id );
	}
}

