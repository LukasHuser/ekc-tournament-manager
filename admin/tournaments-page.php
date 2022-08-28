<?php

/**
 * Top level admin page which allows administraton of tournaments
 */
class Ekc_Tournaments_Admin_Page {

	public function create_tournaments_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    $tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    $file_name = ( isset($_GET['backup'] ) ) ? rawurldecode( $_GET['backup'] ) : '';
		if ( $action === 'new' ) {
			$this->show_new_tournament();
		}
		elseif ( $action === 'edit' ) {
			$this->show_edit_tournament($tournament_id);
		}
		elseif ( $action === 'delete' ) {
			$this->delete_tournament($tournament_id);
			$this->show_tournaments();
    }
		elseif ( $action === 'backup' ) {
      $backup_helper = new Ekc_Backup_Helper();
			$backup_helper->store_backup( $tournament_id );
			$this->show_tournaments();
    }
    elseif ( $action === 'jsonimport' ) {
      $backup_helper = new Ekc_Backup_Helper();
      $backup_helper->import_from_json( $file_name );
      $this->show_tournaments();
    }
		else {
			// handle POST
			$tournament = new Ekc_Tournament();
			$has_data = false;
			$action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			if ( isset($_POST['tournamentid'] ) ) {
				$tournament->set_tournament_id( intval( sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) ) );
				$has_data = true;
			}
			if ( isset($_POST['codename'] ) ) {
				$tournament->set_code_name( sanitize_text_field( wp_unslash( $_POST['codename'] ) ) );
				$has_data = true;
			}
			if ( isset($_POST['name'] ) ) {
				$tournament->set_name( sanitize_text_field( wp_unslash( $_POST['name'] ) ) );
			}
			if ( isset($_POST['date'] ) ) {
				$tournament->set_date( sanitize_text_field( wp_unslash( $_POST['date'] ) ) );
			}
			if ( isset($_POST['teamsize'] ) ) {
        $tournament->set_team_size( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['teamsize'] ) ) ) ); 
			}
			if ( isset($_POST['maxteams'] ) ) {
				$tournament->set_max_teams( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['maxteams'] ) ) ) );
			}
			if ( isset($_POST['waitlist'] ) ) {
				$tournament->set_wait_list_enabled( filter_var( $_POST['waitlist'], FILTER_VALIDATE_BOOLEAN ) );
			}
			if ( isset($_POST['playernames'] ) ) {
				$tournament->set_player_names_required( filter_var( $_POST['playernames'], FILTER_VALIDATE_BOOLEAN ) );
      }
			if ( isset($_POST['backup'] ) ) {
				$tournament->set_auto_backup_enabled( filter_var( $_POST['backup'], FILTER_VALIDATE_BOOLEAN ) );
      }
			if ( isset($_POST['system'] ) ) {
				$tournament->set_tournament_system( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['system'] ) ) ) );
      }
			if ( isset($_POST['eliminationrounds'] ) ) {
				$tournament->set_elimination_rounds( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['eliminationrounds'] ) ) ) );
      }
      if ( isset($_POST['eliminationmaxpoints'] ) ) {
				$tournament->set_elimination_max_points_per_round( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['eliminationmaxpoints'] ) ) ) );
      }
			if ( isset($_POST['swissrounds'] ) ) {
				$tournament->set_swiss_system_rounds( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissrounds'] ) ) ) );
      }
      if ( isset($_POST['swissmaxpoints'] ) ) {
				$tournament->set_swiss_system_max_points_per_round( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissmaxpoints'] ) ) ) );
      }
			if ( isset($_POST['swissadditionalrounds'] ) ) {
				$tournament->set_swiss_system_additional_rounds( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissadditionalrounds'] ) ) ) );
      }
			if ( isset($_POST['swissslidematchrounds'] ) ) {
				$tournament->set_swiss_system_slide_match_rounds( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissslidematchrounds'] ) ) ) );
      }
      if ( isset($_POST['swissroundtime'] ) ) {
				$tournament->set_swiss_system_round_time( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissroundtime'] ) ) ) );
      }
      if ( isset($_POST['swisstiebreaktime'] ) ) {
				$tournament->set_swiss_system_tiebreak_time( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swisstiebreaktime'] ) ) ) );
      }
      if ( isset($_POST['swissstartpitch'] ) ) {
				$tournament->set_swiss_system_start_pitch( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swissstartpitch'] ) ) ) );
			}
      if ( isset($_POST['swisspitchlimit'] ) ) {
				$tournament->set_swiss_system_pitch_limit( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['swisspitchlimit'] ) ) ) );
			}
			if ($has_data) {
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
?>
<div class="wrap">

  <h1 class="wp-heading-inline"><?php _e( 'EKC Tournament Registration' ); ?></h1>
  <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;action=new" class="page-title-action"><?php _e( 'New tournament' ); ?></a>
  <a href="?page=ekc-backup" class="page-title-action"><?php _e( 'Show backups' ); ?></a>

  <hr class="wp-header-end">

<?php 
	$tournaments_table->prepare_items();
	$tournaments_table->display();
?>

</div><!-- .wrap -->
<?php
	}	

	public function show_new_tournament() {
?>
  <div class="wrap">
      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
        <fieldset>
          <legend>Create a new tournament</legend>
          <div class="ekc-control-group">
            <label for="codename">Code name <span class="ekc-required">*</span></label>
            <input id="codename" name="codename" type="text" placeholder="Code name" maxlength="50" required />
          </div>
          <div class="ekc-control-group">
            <label for="name">Name <span class="ekc-required">*</span></label>
            <input id="name" name="name" type="text" placeholder="Name" maxlength="500" required />
          </div>
          <div class="ekc-control-group">
            <label for="teamsize">Team size <span class="ekc-required">*</span></label>
            <?php Ekc_Drop_Down_Helper::team_size_drop_down("teamsize", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
          <div class="ekc-control-group">
            <label for="date">Date</label>
            <input id="date" name="date" class="ekc-datepicker" type="text" />
          </div>
          <div class="ekc-control-group">
            <label for="maxteams">Maximum number of teams</label>
            <input id="maxteams" name="maxteams" type="number" placeholder="48" />
          </div>
          <div class="ekc-control-group">
            <label for="waitlist">Wait list available</label>
            <input id="waitlist" name="waitlist" type="checkbox" value="true" />
          </div>
          <div class="ekc-control-group">
            <label for="playernames">Player names required</label>
            <input id="playernames" name="playernames" type="checkbox" value="true" />
          </div>
          <div class="ekc-control-group">
            <label for="backup">Auto backup enabled</label>
            <input id="bakcup" name="backup" type="checkbox" value="true" />
          </div>
          <div class="ekc-control-group">
            <label for="system">Tournament system </label>
            <?php Ekc_Drop_Down_Helper::tournament_system_drop_down("system", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationrounds">Elimination bracket </label>
            <?php Ekc_Drop_Down_Helper::elimination_bracket_drop_down("eliminationrounds", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationmaxpoints">Maximum points per round for elimination bracket</label>
            <input id="eliminationmaxpoints" name="eliminationmaxpoints" type="number" min="0" />
          </div>
          <div class="ekc-control-group">
            <label for="swissrounds">Number of rounds for Swiss System</label>
            <input id="swissrounds" name="swissrounds" type="number" placeholder="7" />
          </div>
          <div class="ekc-control-group">
            <label for="swissmaxpoints">Maximum points per round for Swiss System</label>
            <input id="swissmaxpoints" name="swissmaxpoints" type="number" min="0" />
          </div>
          <div class="ekc-control-group">
            <label for="swissslidematchrounds">Number of slide match rounds for Swiss System (i.e. the pairing for the first n rounds is slide match, all following rounds are top down match)</label>
            <input id="swissslidematchrounds" name="swissslidematchrounds" type="number" placeholder="4" />
          </div>
          <div class="ekc-control-group">
            <label for="swissadditionalrounds">Number of additional rounds for Swiss System (i.e. ranking games after elimination bracket has started)</label>
            <input id="swissadditionalrounds" name="swissadditionalrounds" type="number" placeholder="2" />
          </div>
          <div class="ekc-control-group">
            <label for="swissroundtime">Time limit for a Swiss System round (setting a value will enable a timer)</label>
            <input id="swissroundtime" name="swissroundtime" type="number" placeholder="40" />
          </div>
          <div class="ekc-control-group">
            <label for="swisstiebreaktime">Time limit until Tie Break (setting a value will enable a timer)</label>
            <input id="swissrtiebreaktime" name="swisstiebreaktime" type="number" placeholder="30" />
          </div>
          <div class="ekc-control-group">
            <label for="swissstartpitch">Start pitch number (useful, if two tournaments run in parallel)</label>
            <input id="swissstartpitch" name="swissstartpitch" type="number" placeholder="1" />
          </div>
          <div class="ekc-control-group">
            <label for="swisspitchlimit">Number of available pitches (optional). If the number of teams exceeds the number of pitches, additional BYEs will be added to the tournament.</label>
            <input id="swisspitchlimit" name="swisspitchlimit" type="number" />
          </div>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary">Create tournament</button>
            <input id="tournamentid" name="tournamentid" type="hidden" />
            <input id="action" name="action" type="hidden" value="new" />
          </div>
        </fieldset>
      </form>
  </div><!-- .wrap -->
<?php
	}

	public function show_edit_tournament($tournament_id) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id($tournament_id);
?>
  <div class="wrap">
      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>" accept-charset="utf-8">
        <fieldset>
        <legend>Edit tournament</legend>
          <div class="ekc-control-group">
            <label for="codename">Code name <span class="ekc-required">*</span></label>
            <input id="codename" name="codename" type="text" placeholder="Code name" maxlength="50" value="<?php esc_html_e( $tournament->get_code_name() ) ?>" required />
          </div>
          <div class="ekc-control-group">
            <label for="name">Name <span class="ekc-required">*</span></label>
            <input id="name" name="name" type="text" placeholder="Name" maxlength="500" value="<?php esc_html_e( $tournament->get_name() ) ?>" required />
          </div>         
          <div class="ekc-control-group">
            <label for="teamsize">Team size <span class="ekc-required">*</span></label>
            <?php 
              Ekc_Drop_Down_Helper::team_size_drop_down("teamsize", $tournament->get_team_size() ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="date">Date</label>
            <input id="date" name="date" class="ekc-datepicker" type="text" value="<?php esc_html_e( $tournament->get_date() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="maxteams">Maximum number of teams</label>
            <input id="maxteams" name="maxteams" type="number" placeholder="48" value="<?php esc_html_e( $tournament->get_max_teams() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="waitlist">Wait list available</label>
            <input id="waitlist" name="waitlist" type="checkbox" value="true" <?php $tournament->is_wait_list_enabled() ? esc_html_e( "checked" ) : _e('') ?> />
          </div>
          <div class="ekc-control-group">
            <label for="playernames">Player names required</label>
            <input id="playernames" name="playernames" type="checkbox" value="true" <?php $tournament->is_player_names_required() ? esc_html_e( "checked" ) : _e('') ?> />
          </div>
          <div class="ekc-control-group">
            <label for="backup">Auto backup enabled</label>
            <input id="bakcup" name="backup" type="checkbox" value="true" <?php $tournament->is_auto_backup_enabled() ? esc_html_e( "checked" ) : _e('') ?> />
          </div>
          <div class="ekc-control-group">
            <label for="system">Tournament system </label>
            <?php 
              Ekc_Drop_Down_Helper::tournament_system_drop_down("system", $tournament->get_tournament_system() ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationrounds">Elimination bracket </label>
            <?php 
              Ekc_Drop_Down_Helper::elimination_bracket_drop_down("eliminationrounds", $tournament->get_elimination_rounds() ) ?>
          </div>
          <div class="ekc-control-group">
            <label for="eliminationmaxpoints">Maximum points per round for elimination bracket</label>
            <input id="eliminationmaxpoints" name="eliminationmaxpoints" type="number" min="0" value="<?php esc_html_e( $tournament->get_elimination_max_points_per_round() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissrounds">Number of rounds of Swiss System</label>
            <input id="swissrounds" name="swissrounds" type="number" placeholder="7" value="<?php esc_html_e( $tournament->get_swiss_system_rounds() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissmaxpoints">Maximum points per round for Swiss System</label>
            <input id="swissmaxpoints" name="swissmaxpoints" type="number" min="0" value="<?php esc_html_e( $tournament->get_swiss_system_max_points_per_round() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissslidematchrounds">Number of slide match rounds for Swiss System (i.e. the pairing for the first n rounds is slide match, all following rounds are top down match)</label>
            <input id="swissslidematchrounds" name="swissslidematchrounds" type="number" placeholder="4" value="<?php esc_html_e( $tournament->get_swiss_system_slide_match_rounds() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissadditionalrounds">Number of additional rounds of Swiss System (i.e. ranking games after elimination bracket has started)</label>
            <input id="swissadditionalrounds" name="swissadditionalrounds" type="number" placeholder="2" value="<?php esc_html_e( $tournament->get_swiss_system_additional_rounds() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissroundtime">Time limit for a Swiss System round (setting a value will enable a timer)</label>
            <input id="swissroundtime" name="swissroundtime" type="number" placeholder="40" value="<?php esc_html_e( $tournament->get_swiss_system_round_time() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swisstiebreaktime">Time limit until Tie Break (setting a value will enable a timer)</label>
            <input id="swisstiebreaktime" name="swisstiebreaktime" type="number" placeholder="30" value="<?php esc_html_e( $tournament->get_swiss_system_tiebreak_time() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swissstartpitch">Start pitch number (useful, if two tournaments run in parallel)</label>
            <input id="swissstartpitch" name="swissstartpitch" type="number" placeholder="1" value="<?php esc_html_e( $tournament->get_swiss_system_start_pitch() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="swisspitchlimit">Number of available pitches (optional). If the number of teams exceeds the number of pitches, additional BYEs will be added to the tournament.</label>
            <input id="swisspitchlimit" name="swisspitchlimit" type="number" value="<?php esc_html_e( $tournament->get_swiss_system_pitch_limit() ) ?>" />
          </div>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary">Save tournament</button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="edit" />
          </div>
        </fieldset>
      </form>
  </div><!-- .wrap -->
<?php
	}

	public function delete_tournament($tournament_id) {
		$db = new Ekc_Database_Access();
		$tournament = $db->delete_tournament($tournament_id);
	}
}

