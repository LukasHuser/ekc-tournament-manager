<?php

/**
 * Admin page which allows administraton of teams
 */
class Ekc_Teams_Admin_Page {

	public function create_teams_page() {

		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
		$team_id = ( isset($_GET['teamid'] ) ) ? sanitize_key( wp_unslash( $_GET['teamid'] ) ) : null;
    if ( $action === 'new' ) {
			$this->show_new_team($tournament_id);
		}
		elseif ( $action === 'edit' ) {
			$this->show_edit_team($team_id);
		}
		elseif ( $action === 'activate' ) {
			$this->set_team_active($team_id, true);
			$this->show_teams($tournament_id);
		}
		elseif ( $action === 'inactivate' ) {
			$this->set_team_active( $team_id, false );
			$this->show_teams( $tournament_id );
    }
		elseif ( $action === 'onwaitlist' ) {
			$this->set_team_on_wait_list($team_id, true);
			$this->show_teams($tournament_id);
		}
		elseif ( $action === 'offwaitlist' ) {
			$this->set_team_on_wait_list( $team_id, false );
			$this->show_teams( $tournament_id );
		}
		else {
			$this->handle_post( $tournament_id );
		}
	}

	private function handle_post( $tournament_id ) {
		$team = new Ekc_Team();
		$has_data = false;
		$players = array();
		$action = ( isset($_POST['action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( isset($_POST['teamid'] ) ) {
			$team->set_team_id( intval( sanitize_key( wp_unslash( $_POST['teamid'] ) ) ) );
			$has_data = true;
		}
		if ( isset($_POST['tournamentid'] ) ) {
			$team->set_tournament_id( intval( sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) ) );
			$has_data = true;
		}
		if ( isset($_POST['name'] ) ) {
			$team->set_name( sanitize_text_field( wp_unslash( $_POST['name'] ) ) );
			$has_data = true;
		}
		elseif ( isset($_POST['player1first']) ) {
			$team->set_name( sanitize_text_field( wp_unslash( $_POST['player1first'] ) ) . ' '. sanitize_text_field( wp_unslash( $_POST['player1last'] ) ) );
			$has_data = true;
		}
		if ( isset($_POST['country'] ) ) {
			$team->set_country( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['country'] ) ) ) );
		}
		elseif ( isset($_POST['player1country'] ) ) {
			$team->set_country( Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST['player1country'] ) ) ) );
		}
		if ( isset($_POST['club'] ) ) {
			$team->set_club( sanitize_text_field( wp_unslash( $_POST['club'] ) ) );
		}
    if ( isset($_POST['active'] ) ) {
			$team->set_active( filter_var( $_POST['active'], FILTER_VALIDATE_BOOLEAN ) );
    }
    if ( isset($_POST['waitlist'] ) ) {
			$team->set_on_wait_list( filter_var( $_POST['waitlist'], FILTER_VALIDATE_BOOLEAN ) );
    }
    if ( isset($_POST['registrationorder'] ) ) {
			$team->set_registration_order( Ekc_Type_Helper::opt_floatval( sanitize_text_field( wp_unslash( $_POST['registrationorder'] ) ) ) );
		}
		if ( isset($_POST['email'] ) ) {
			$team->set_email( sanitize_text_field( wp_unslash( $_POST['email'] ) ) );
		}
		if ( isset($_POST['phone'] ) ) {
			$team->set_phone( sanitize_text_field( wp_unslash( $_POST['phone'] ) ) );
    }
		if ( isset($_POST['camping'] ) ) {
			$team->set_camping_count( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['camping'] ) ) ) );
    }
		if ( isset($_POST['breakfast'] ) ) {
			$team->set_breakfast_count( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['breakfast'] ) ) ) );
		}
    if ( isset($_POST['registrationfee'] ) ) {
			$team->set_registration_fee_paid( filter_var( $_POST['registrationfee'], FILTER_VALIDATE_BOOLEAN ) );
		}
		if ( isset($_POST['seedingscore'] ) ) {
			$team->set_seeding_score( Ekc_Type_Helper::opt_floatval( sanitize_text_field( wp_unslash( $_POST['seedingscore'] ) ) ) );
    }
    if ( isset($_POST['initialscore'] ) ) {
			$team->set_initial_score( Ekc_Type_Helper::opt_floatval( sanitize_text_field( wp_unslash( $_POST['initialscore'] ) ) ) );
    }
    if ( isset($_POST['virtualrank'] ) ) {
			$team->set_virtual_rank( Ekc_Type_Helper::opt_intval( sanitize_text_field( wp_unslash( $_POST['virtualrank'] ) ) ) );
		}
		if ( isset( $_POST['player1first'] ) && trim( $_POST['player1first'] ) !== '' ) {
			$player = $this->extract_player( 'player1first', 'player1last', 'player1country' );
			$player->set_captain( true );
			$players[] = $player;
		}
		if ( isset($_POST['player2first'] )  && trim( $_POST['player2first'] ) !== '' ) {
			$players[] = $this->extract_player( 'player2first', 'player2last', 'player2country' );
		}
		if ( isset($_POST['player3first'] )  && trim( $_POST['player3first'] ) !== '' ) {
			$players[] = $this->extract_player( 'player3first', 'player3last', 'player3country' );
		}
		if ( isset($_POST['player4first'] )  && trim( $_POST['player4first'] ) !== '' ) {
			$players[] = $this->extract_player( 'player4first', 'player4last', 'player4country' );
		}
		if ( isset($_POST['player5first'] ) && trim( $_POST['player5first'] ) !== '' ) {
			$players[] = $this->extract_player( 'player5first', 'player5last', 'player5country' );
		}
		if ( isset($_POST['player6first'] ) && trim( $_POST['player6first'] ) !== '' ) {
			$players[] = $this->extract_player( 'player6first', 'player6last', 'player6country' );
		}
		if ($has_data) {
			$team->set_players($players);
			$db = new Ekc_Database_Access();
			if ( $action === 'new' ) {
        $team->set_registration_date(date("Y-m-d H:i:s")); // current date
				$db->insert_team($team);
			}
			elseif ( $action === 'edit' ) {
				$db->update_team($team);
      }
		}
		$this->show_teams( $tournament_id ? $tournament_id : $team->get_tournament_id() );
	}

	private function extract_player( $first_name_id, $last_name_id, $country_id ) {
		$player = new Ekc_Player();
		$player->set_captain(false);
		$player->set_active(true);
		$player->set_first_name( sanitize_text_field( wp_unslash( $_POST[$first_name_id] ) ) );
		$player->set_last_name( isset( $_POST[$last_name_id] ) ? sanitize_text_field( wp_unslash( $_POST[$last_name_id] ) ) : '' );
		$player->set_country( isset( $_POST[$country_id] ) ? Ekc_Drop_Down_Helper::empty_if_none( sanitize_text_field( wp_unslash( $_POST[$country_id] ) ) ) : '' );
		return $player;
	}

	public function show_teams($tournament_id) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id($tournament_id);

		$teams_table = new Ekc_Teams_Table( $tournament_id );
?>
<div class="wrap">

  <h1 class="wp-heading-inline"><?php esc_html_e( $tournament->get_name() ) ?></h1>
  <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e($tournament_id) ?>&amp;action=new" class="page-title-action"><?php _e( 'New team' ) ?></a>
  <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e($tournament_id) ?>&amp;action=csvexport" class="page-title-action"><?php _e( 'CSV export' ) ?></a>
  <a href="?page=ekc-links&amp;tournamentid=<?php esc_html_e($tournament_id) ?>" class="page-title-action"><?php _e( 'Shareable links' ) ?></a>

  <hr class="wp-header-end">
  <form id="teams-filter" method="get" >
  <input id="page" name="page" type="hidden" value="<?php esc_html_e( $_REQUEST['page'] ) ?>" />
  <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament_id ) ?>" />
<?php 
	$teams_table->prepare_items();
	$teams_table->display();
?>
</form>
</div><!-- .wrap -->
<?php
	}	

	public function show_new_team( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
?>
  <div class="wrap">
      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>&amp;tournamentid=<?php esc_html_e($tournament_id) ?>" accept-charset="utf-8">
        <fieldset>
        <legend>Create a new team</legend>
<?php if ( $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1 || ! $tournament->is_player_names_required() ) { ?>
          <div class="ekc-control-group">
            <label for="name">Name <span class="ekc-required">*</span></label>
            <input id="name" name="name" type="text" placeholder="Name" maxlength="500" required />
          </div>         
          <div class="ekc-control-group">
            <label for="country">Country</label>
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php } ?>
          <div class="ekc-control-group">
            <label for="active">Is active</label>
            <input id="active" name="active" type="checkbox" value="true" checked />
          </div>
          <div class="ekc-control-group">
            <label for="waitlist">Is on waiting list</label>
            <input id="waitlist" name="waitlist" type="checkbox" value="true" />
          </div>
          <div class="ekc-control-group">
            <label for="club">Sports club / city</label>
            <input id="club" name="club" type="text"  maxlength="500" />
          </div>
          <div class="ekc-control-group">
          <label for="registrationorder">Order (used in registration list / waiting list)</label>
            <input id="registrationorder" name="registrationorder" type="number" step="any" placeholder="1.0" />
          </div>
          <div class="ekc-control-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" placeholder="my.name@mail.com" maxlength="500" />
          </div>
          <div class="ekc-control-group">
            <label for="phone">Phone (including national prefix such as +41 for Switzerland, +49 for Germany etc.)</label>
            <input id="phone" name="phone" type="tel" placeholder="+41 79 888 77 66" maxlength="50" />
          </div>
          <div class="ekc-control-group">
            <label for="camping">Camping (number of persons)</label>
            <input id="camping" name="camping" type="number" step="any" placeholder="3" />
          </div>
          <div class="ekc-control-group">
            <label for="breakfast">Breakfast (number of persons)</label>
            <input id="breakfast" name="breakfast" type="number" step="any" placeholder="3" />
          </div>
          <div class="ekc-control-group">
            <label for="registrationfee">Registration fee paid</label>
            <input id="registrationfee" name="registrationfee" type="checkbox" />
          </div>
<?php if ( $tournament->get_swiss_system_rounds() > 0 ) { ?>
          <div class="ekc-control-group">
            <label for="seedingscore">Swiss System: Seeding score</label>
            <input id="seedingscore" name="seedingscore" type="number" step="any" placeholder="7.43" />
          </div>
          <div class="ekc-control-group">
            <label for="initialscore">Swiss System: Initial score (for an accelerated system)</label>
            <input id="initialscore" name="initialscore" type="number" step="any" placeholder="2.5" />
          </div>
          <div class="ekc-control-group">
            <label for="virtualrank">Swiss System: Virtual Rank (for a top team which is excluded in additional ranking rounds)</label>
            <input id="virtualrank" name="virtualrank" type="number" step="any" placeholder="1" />
          </div>
<?php }
      if ( $this->is_visible_player_1( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player1first">Captain <span class="ekc-required">*</span></label>
            <input id="player1first" name="player1first" type="text" placeholder="First name" maxlength="500" required />
            <input id="player1last" name="player1last" type="text" placeholder="Last name" maxlength="500" required />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player1country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php }

      if ( $this->is_visible_player_2( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player2first">Player 2 <span class="ekc-required">*</span></label>
            <input id="player2first" name="player2first" type="text" placeholder="First name" maxlength="500" required />
            <input id="player2last" name="player2last" type="text" placeholder="Last name" maxlength="500" required />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player2country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php }

     if ( $this->is_visible_player_3( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player3first">Player 3 <?php $this->is_required_player_3( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player3first" name="player3first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?> />
            <input id="player3last" name="player3last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?> />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player3country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player4first">Player 4 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player4first" name="player4first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <input id="player4last" name="player4last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player4country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player5first">Player 5 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player5first" name="player5first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <input id="player5last" name="player5last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player5country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player6first">Player 6 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player6first" name="player6first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <input id="player6last" name="player6last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?> />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player6country", Ekc_Drop_Down_Helper::SELECTION_NONE) ?>
          </div>
<?php } ?>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary">Create team</button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament_id ) ?>" />
            <input id="teamid" name="teamid" type="hidden" />
            <input id="action" name="action" type="hidden" value="new" />
          </div>
        </fieldset>
      </form>
  </div><!-- .wrap -->
<?php
  }

	public function show_edit_team( $team_id ) {
		$db = new Ekc_Database_Access();
		$team = $db->get_team_by_id( $team_id );
		$tournament = $db->get_tournament_by_id($team->get_tournament_id());
?>
  <div class="wrap">
      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>&amp;tournamentid=<?php esc_html_e($tournament->get_tournament_id() ) ?>" accept-charset="utf-8">
        <fieldset>
        <legend>Edit team</legend>
<?php if ( $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1 || ! $tournament->is_player_names_required() ) { ?>
          <div class="ekc-control-group">
            <label for="name">Name <span class="ekc-required">*</span></label>
            <input id="name" name="name" type="text" placeholder="Name" maxlength="500" required value="<?php esc_html_e( $team->get_name() ) ?>" />
          </div>         
          <div class="ekc-control-group">
            <label for="country">Country</label>
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("country", $team->get_country()) ?>
          </div>
<?php } ?>
          <div class="ekc-control-group">
            <label for="active">Is active</label>
            <input id="active" name="active" type="checkbox" value="true" <?php $team->is_active() ? _e( "checked" ) : _e('') ?>/>
          </div>
          <div class="ekc-control-group">
            <label for="waitlist">Is on waiting list</label>
            <input id="waitlist" name="waitlist" type="checkbox" value="true" <?php $team->is_on_wait_list() ? _e( "checked" ) : _e('') ?>/>
          </div>
          <div class="ekc-control-group">
            <label for="club">Sports club / city</label>
            <input id="club" name="club" type="text" maxlength="500" value="<?php esc_html_e( $team->get_club() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="registrationorder">Order (used in registration list / waiting list)</label>
            <input id="registrationorder" name="registrationorder" type="number" step="any" placeholder="1.0" value="<?php esc_html_e( $team->get_registration_order() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" placeholder="my.name@mail.com" maxlength="500" value="<?php esc_html_e( $team->get_email() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="phone">Phone (including national prefix such as +41 for Switzerland, +49 for Germany etc.)</label>
            <input id="phone" name="phone" type="tel" placeholder="+41 79 888 77 66" maxlength="50" value="<?php esc_html_e( $team->get_phone() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="camping">Camping (number of persons)</label>
            <input id="camping" name="camping" type="number" step="any" placeholder="3" value="<?php esc_html_e( $team->get_camping_count() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="breakfast">Breakfast (number of persons)</label>
            <input id="breakfast" name="breakfast" type="number" step="any" placeholder="3" value="<?php esc_html_e( $team->get_breakfast_count() ) ?>"/>
          </div>
          <div class="ekc-control-group">
            <label for="registrationfee">Registration fee paid</label>
            <input id="registrationfee" name="registrationfee" type="checkbox" value="true" <?php $team->is_registration_fee_paid() ? _e( "checked" ) : _e('') ?> />
          </div>
<?php if ( $tournament->get_swiss_system_rounds() > 0 ) { ?>
          <div class="ekc-control-group">
            <label for="seedingscore">Swiss System: Seeding score</label>
            <input id="seedingscore" name="seedingscore" type="number" step="any" placeholder="7.43" value="<?php esc_html_e( $team->get_seeding_score() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="initialscore">Swiss System: Initial score (for an accelerated system)</label>
            <input id="initialscore" name="initialscore" type="number" step="any" placeholder="2.5" value="<?php esc_html_e( $team->get_initial_score() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="virtualrank">Swiss System: Virtual Rank (for a top team which is excluded in additional ranking rounds)</label>
            <input id="virtualrank" name="virtualrank" type="number" step="any" placeholder="1" value="<?php esc_html_e( $team->get_virtual_rank() ) ?>" />
          </div>
<?php }
      if ( $this->is_visible_player_1( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player1first">Captain <span class="ekc-required">*</span></label>
            <input id="player1first" name="player1first" type="text" placeholder="First name" maxlength="500" required 
                   value="<?php $team->get_player(0) ? esc_html_e( $team->get_player(0)->get_first_name() ) : _e('') ?>"/>
            <input id="player1last" name="player1last" type="text" placeholder="Last name" maxlength="500" required 
                   value="<?php $team->get_player(0) ? esc_html_e( $team->get_player(0)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player1country", $team->get_player(0) ? $team->get_player(0)->get_country() : '') ?>
          </div>
<?php }

      if ( $this->is_visible_player_2( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player2first">Player 2 <span class="ekc-required">*</span></label>
            <input id="player2first" name="player2first" type="text" placeholder="First name" maxlength="500" required 
                   value="<?php $team->get_player(1) ? esc_html_e( $team->get_player(1)->get_first_name() ) : _e('') ?>" />
            <input id="player2last" name="player2last" type="text" placeholder="Last name" maxlength="500" required 
                   value="<?php $team->get_player(1) ? esc_html_e( $team->get_player(1)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player2country", $team->get_player(1) ? $team->get_player(1)->get_country() : '') ?>
          </div>
<?php }

     if ( $this->is_visible_player_3( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player3first">Player 3 <?php $this->is_required_player_3( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player3first" name="player3first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(2) ? esc_html_e( $team->get_player(2)->get_first_name() ) : _e('') ?>" />
            <input id="player3last" name="player3last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(2) ? esc_html_e( $team->get_player(2)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player3country", $team->get_player(2) ? $team->get_player(2)->get_country() : '') ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player4first">Player 4 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player4first" name="player4first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(3) ? esc_html_e( $team->get_player(3)->get_first_name() ) : _e('') ?>" />
            <input id="player4last" name="player4last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(3) ? esc_html_e( $team->get_player(3)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player4country", $team->get_player(3) ? $team->get_player(3)->get_country() : '') ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player5first">Player 5 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player5first" name="player5first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(4) ? esc_html_e( $team->get_player(4)->get_first_name() ) : _e('') ?>" />
            <input id="player5last" name="player5last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(4) ? esc_html_e( $team->get_player(4)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player5country", $team->get_player(4) ? $team->get_player(4)->get_country() : '') ?>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player6first">Player 6 <?php $this->is_required_player_4_5_6( $tournament ) ? _e('<span class="ekc-required">*</span>') : _e('') ?></label>
            <input id="player6first" name="player6first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(5) ? esc_html_e( $team->get_player(5)->get_first_name() ) : _e('') ?>" />
            <input id="player6last" name="player6last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team->get_player(5) ? esc_html_e( $team->get_player(5)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player6country", $team->get_player(5) ? $team->get_player(5)->get_country() : '') ?>
          </div>
<?php } ?>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary">Save team</button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
            <input id="teamid" name="teamid" type="hidden" value="<?php esc_html_e( $team_id ) ?>" />
            <input id="action" name="action" type="hidden" value="edit" />
          </div>
        </fieldset>
      </form>
  </div><!-- .wrap -->
<?php
  }

  private function is_visible_player_1( $tournament ) {
    return $tournament->is_player_names_required();
  }

  private function is_visible_player_2( $tournament ) {
    return $tournament->is_player_names_required()
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1;
  }

  private function is_visible_player_3( $tournament ) {
    return $tournament->is_player_names_required()
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_2;
  }

  private function is_required_player_3( $tournament ) {
    return $this->is_visible_player_3( $tournament )
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_2plus;
  }

  private function is_visible_player_4_5_6( $tournament ) {
    return $tournament->is_player_names_required()
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_2
        && $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_3;
  }

  private function is_required_player_4_5_6( $tournament ) {
    return $this->is_visible_player_4_5_6( $tournament )
        && $tournament->get_team_size() === Ekc_Drop_Down_Helper::TEAM_SIZE_6;
  }

  public function set_team_active( $team_id, $is_active ) {
		$db = new Ekc_Database_Access();
		$db->set_team_active( $team_id, $is_active );
	}

	public function set_team_on_wait_list( $team_id, $is_on_wait_list ) {
		$db = new Ekc_Database_Access();
		$db->set_team_on_wait_list( $team_id, $is_on_wait_list );
  }

	public function export_teams_as_csv() {
		$page = ( isset($_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : false;
		if ( $page === 'ekc-teams' && $action === 'csvexport' && $tournament_id ) {

			$db = new Ekc_Database_Access();
			$csv = $db->get_all_teams_as_csv( $tournament_id );
			$tournament = $db->get_tournament_by_id( $tournament_id );
			$file_name = 'teams-' . $tournament->get_code_name() . '.csv';

			$fp = fopen('php://output', 'w');
			if ($fp && $csv) {
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="' . $file_name . '"');
				header("Pragma: no-cache");
				header("Expires: 0");
				foreach ($csv as $row) {
					fputcsv($fp, $row, ';', '"');
				}
				exit();
			}
		}
	}
}

