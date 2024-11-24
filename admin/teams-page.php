<?php

/**
 * Admin page which allows administraton of teams
 */
class Ekc_Teams_Admin_Page {

	public function create_teams_page() {

    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$action = $validation_helper->validate_get_text( 'action' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
		$team_id = $validation_helper->validate_get_key( 'teamid' );
    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

    if ( $action === 'new' ) {
			$this->show_new_team( $tournament_id );
		}
		elseif ( $action === 'edit' ) {
			$this->show_edit_team( $team_id );
		}
		elseif ( $action === 'activate' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_active( $team_id, true );
      }
			$this->show_teams( $tournament_id );
		}
		elseif ( $action === 'inactivate' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_active( $team_id, false );
      }
			$this->show_teams( $tournament_id );
    }
		elseif ( $action === 'onwaitlist' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_on_wait_list( $team_id, true );
      }
			$this->show_teams( $tournament_id );
		}
		elseif ( $action === 'offwaitlist' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ) ) ) {
        $this->set_team_on_wait_list( $team_id, false );
      }
			$this->show_teams( $tournament_id );
		}
		elseif ( ! $this->handle_post() ) {
      $this->show_teams( $tournament_id );
    }
	}

	private function handle_post() {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$team = new Ekc_Team();
		$has_data = false;
		$players = array();
		$action = $validation_helper->validate_post_text( 'action' );
    $tournament_id = $validation_helper->validate_post_key( 'tournamentid' );
    if ( $tournament_id ) {
      if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
        return true;
      } 
      $team->set_tournament_id( $tournament_id );
      $has_data = true;
    }
    $team_id = $validation_helper->validate_post_key( 'teamid' );
		if ( $team_id ) {
			$team->set_team_id( $team_id );
			$has_data = true;
		}
    $name = $validation_helper->validate_post_text( 'name' );
		if ( ! $name ) {
      $name = $validation_helper->validate_post_text( 'player1first' ) . ' ' . $validation_helper->validate_post_text( 'player1last' );
		}
    if ( $name ) {
      $team->set_name( $name );
			$has_data = true;
		}
    if ( $has_data ) {
      if ( ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'team', $team_id ? $team_id : -1 ) ) ) {
        return;
      }

      $country = $validation_helper->validate_post_dropdown_text( 'country' );
      if ( ! $country ) {
        $country = $validation_helper->validate_post_dropdown_text( 'player1country' );
      }
      $team->set_country( $country );
      $team->set_club( $validation_helper->validate_post_text( 'club' ) );
      $team->set_active( $validation_helper->validate_post_boolean( 'active' ) );
      $team->set_on_wait_list( $validation_helper->validate_post_boolean( 'waitlist' ) );
      $team->set_registration_order( $validation_helper->validate_post_float( 'registrationorder' ) );
      $team->set_email( $validation_helper->validate_post_text( 'email' ) );
      $team->set_phone( $validation_helper->validate_post_text( 'phone' ) );
      $team->set_camping_count( $validation_helper->validate_post_integer( 'camping' ) );
      $team->set_breakfast_count( $validation_helper->validate_post_integer( 'breakfast' ) );
      $team->set_registration_fee_paid( $validation_helper->validate_post_boolean( 'registrationfee' ) );
      $team->set_seeding_score( $validation_helper->validate_post_float( 'seedingscore' ) );
      $team->set_initial_score( $validation_helper->validate_post_float( 'initialscore' ) );
      $team->set_virtual_rank( $validation_helper->validate_post_integer( 'virtualrank' ) );
      
      $player = $this->extract_player( 'player1first', 'player1last', 'player1country' );
      if ( $player ) {
        $player->set_captain( true );
        $players[] = $player;
      }
      $player = $this->extract_player( 'player2first', 'player2last', 'player2country' );
      if ( $player ) {
        $players[] = $player;
      }
      $player = $this->extract_player( 'player3first', 'player3last', 'player3country' );
      if ( $player ) {
        $players[] = $player;
      }
      $player = $this->extract_player( 'player4first', 'player4last', 'player4country' );
      if ( $player ) {
        $players[] = $player;
      }
      $player = $this->extract_player( 'player5first', 'player5last', 'player5country' );
      if ( $player ) {
        $players[] = $player;
      }
      $player = $this->extract_player( 'player6first', 'player6last', 'player6country' );
      if ( $player ) {
        $players[] = $player;
      }

			$team->set_players( $players );
			$db = new Ekc_Database_Access();
			if ( $action === 'new' ) {
        $team->set_registration_date( wp_date( 'Y-m-d H:i:s' ) ); // current date
				$db->insert_team( $team );
			}
			elseif ( $action === 'edit' ) {
				$db->update_team( $team );
      }

      $this->show_teams( $tournament_id );
		}

    return $has_data;
	}

	private function extract_player( $first_name_id, $last_name_id, $country_id ) {
		$validation_helper = new Ekc_Validation_Helper();
    $first_name = trim( $validation_helper->validate_post_text( $first_name_id ) );
    $last_name = trim( $validation_helper->validate_post_text( $last_name_id ) );
    if ( ! $first_name && ! $last_name ) {
      return null;
    }

    $player = new Ekc_Player();
		$player->set_captain( false );
		$player->set_active( true );
		$player->set_first_name( $first_name );
		$player->set_last_name( $last_name );
		$player->set_country( $validation_helper->validate_post_dropdown_text( $country_id ) );
		return $player;
	}

	public function show_teams( $tournament_id ) {
    if ( ! $tournament_id ) {
      return;
    }
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );

		$teams_table = new Ekc_Teams_Table( $tournament_id );
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    $new_team_url = sprintf( '?page=%s&tournamentid=%s&action=new', $page, $tournament_id );
    $csvexport_url = sprintf( '?page=%s&tournamentid=%s&action=csvexport', $page, $tournament_id );
?>
<div class="wrap">

  <h1 class="wp-heading-inline"><?php echo esc_html( $tournament->get_name() ) ?></h1>
  <a href="<?php echo esc_url( $new_team_url ) ?>" class="page-title-action"><?php esc_html_e( 'New team', 'ekc-tournament-manager' ) ?></a>
  <a href="<?php echo esc_url( $csvexport_url ) ?>" class="page-title-action"><?php esc_html_e( 'CSV export', 'ekc-tournament-manager' ) ?></a>
  <?php
  if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
    $shareable_links_url = sprintf( '?page=ekc-links&tournamentid=%s&action=shareable-links', $tournament_id );
  ?><a href="<?php echo esc_url( $shareable_links_url ) ?>" class="page-title-action"><?php esc_html_e( 'Shareable links', 'ekc-tournament-manager' ) ?></a>
  <?php
  }
  ?>

  <hr class="wp-header-end">
  <form id="teams-filter" method="get" >
  <input id="page" name="page" type="hidden" value="<?php echo esc_attr( $page ) ?>" />
  <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament_id ) ?>" />
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
    $this->show_team( 'new', __( 'Create a new team', 'ekc-tournament-manager' ), __( 'Create team', 'ekc-tournament-manager' ), $tournament );
  }

	public function show_edit_team( $team_id ) {
    $db = new Ekc_Database_Access();
		$team = $db->get_team_by_id( $team_id );
		$tournament = $db->get_tournament_by_id( $team->get_tournament_id() );

    $this->show_team( 'edit', __( 'Edit team', 'ekc-tournament-manager' ), __( 'Save team', 'ekc-tournament-manager' ), $tournament, $team );
  }

	public function show_team( $action, $title, $button_text, $tournament, $team = null ) {
    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
    $action_url = sprintf( '?page=%s&tournamentid=%s', $page, $tournament->get_tournament_id() );
?>
  <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( $title ) ?></h1>
    <hr class="wp-header-end">

      <form class="ekc-form" method="post" action="<?php echo esc_url( $action_url ) ?>" accept-charset="utf-8">
        <fieldset>
        <legend><h3><?php esc_html_e( 'Team', 'ekc-tournament-manager' ) ?></h3></legend>
<?php if ( $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1 || ! $tournament->is_player_names_required() ) { ?>
          <div class="ekc-control-group">
            <label for="name" class="ekc-required"><?php esc_html_e( 'Name', 'ekc-tournament-manager' ) ?></label>
            <input id="name" name="name" type="text" maxlength="500" required value="<?php if ( $team ) echo esc_attr( $team->get_name() ) ?>" />
          </div>         
          <div class="ekc-control-group">
            <label for="country"><?php esc_html_e( 'Country', 'ekc-tournament-manager' ) ?></label>
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'country', $team ? $team->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
<?php } ?>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="active" name="active" type="checkbox" value="true" <?php if ( $team && $team->is_active() ) echo 'checked' ?>/>
                 <label for="active"><?php esc_html_e( 'Is active', 'ekc-tournament-manager' ) ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="waitlist" name="waitlist" type="checkbox" value="true" <?php if ( $team && $team->is_on_wait_list() ) echo 'checked' ?>/>
                 <label for="waitlist"><?php esc_html_e( 'Is on waiting list', 'ekc-tournament-manager' ) ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="registrationfee" name="registrationfee" type="checkbox" value="true" <?php if ( $team && $team->is_registration_fee_paid() ) echo 'checked' ?> />
                 <label for="registrationfee"><?php esc_html_e( 'Registration fee paid', 'ekc-tournament-manager' ) ?></label></div>
          </div>
          <div class="ekc-control-group">
            <label for="club"><?php esc_html_e( 'Sports club / city', 'ekc-tournament-manager' ) ?></label>
            <input id="club" name="club" type="text" maxlength="500" value="<?php if ( $team ) echo esc_attr( $team->get_club() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="email"><?php esc_html_e( 'E-mail', 'ekc-tournament-manager' ) ?></label>
            <input id="email" name="email" type="email" placeholder="my.name@mail.com" maxlength="500" value="<?php if ( $team ) echo esc_attr( $team->get_email() ) ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="phone"><?php esc_html_e( 'Phone', 'ekc-tournament-manager' ) ?></label>
            <div><input id="phone" name="phone" type="tel" placeholder="+41 79 888 77 66" maxlength="50" value="<?php if ( $team ) echo esc_attr( $team->get_phone() ) ?>" />
            <p><?php esc_html_e( 'Phone number format including a national prefix such as +41 for Switzerland, +49 for Germany etc.', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="registrationorder"><?php esc_html_e( 'Order', 'ekc-tournament-manager' ) ?></label>
            <div><input id="registrationorder" name="registrationorder" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_registration_order() ) ?>" />
                  <p><?php esc_html_e( 'Sort order used in registration list and waiting list', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="camping"><?php esc_html_e( 'Camping', 'ekc-tournament-manager' ) ?></label>
            <div><input id="camping" name="camping" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_camping_count() ) ?>" />
                 <p><?php esc_html_e( 'Number of persons', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="breakfast"><?php esc_html_e( 'Breakfast', 'ekc-tournament-manager' ) ?></label>
            <div><input id="breakfast" name="breakfast" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_breakfast_count() ) ?>"/>
                 <p><?php esc_html_e( 'Number of persons', 'ekc-tournament-manager' ) ?></p></div>
          </div>
<?php if ( $tournament->get_swiss_system_rounds() > 0 ) { ?>
        <fieldset>
        <legend><h3><?php esc_html_e( 'Swiss System', 'ekc-tournament-manager' ) ?></h3></legend>
          <div class="ekc-control-group">
            <label for="seedingscore"><?php esc_html_e( 'Seeding score', 'ekc-tournament-manager' ) ?></label>
            <div><input id="seedingscore" name="seedingscore" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_seeding_score() ) ?>" />
                 <p><?php esc_html_e( 'Seeding score defining an initial ranking', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="initialscore"><?php esc_html_e( 'Initial score', 'ekc-tournament-manager' ) ?></label>
            <div><input id="initialscore" name="initialscore" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_initial_score() ) ?>" />
                 <p><?php esc_html_e( 'Initial score for an accelerated system', 'ekc-tournament-manager' ) ?></p></div>
          </div>
          <div class="ekc-control-group">
            <label for="virtualrank"><?php esc_html_e( 'Virtual rank', 'ekc-tournament-manager' ) ?></label>
            <div><input id="virtualrank" name="virtualrank" type="number" step="any" value="<?php if ( $team ) echo esc_attr( $team->get_virtual_rank() ) ?>" />
                 <p><?php esc_html_e( 'Virtual rank for a top team which is excluded in additional ranking rounds', 'ekc-tournament-manager' ) ?></p></div>
          </div>
        </fieldset>
<?php }
      if ( $this->is_visible_player_1( $tournament ) ) { ?>
        <fieldset>
        <legend><h3><?php esc_html_e( 'Players', 'ekc-tournament-manager' ) ?></h3></legend>
          <div class="ekc-control-group">
            <label for="player1first" class="ekc-required"><?php esc_html_e( 'Captain', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player1first" name="player1first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" required 
                   value="<?php if ( $team && $team->get_player(0) ) echo esc_attr( $team->get_player(0)->get_first_name() ) ?>"/>
            <input id="player1last" name="player1last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" required 
                   value="<?php if ( $team && $team->get_player(0) ) echo esc_attr( $team->get_player(0)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player1country', $team && $team->get_player(0) ? $team->get_player(0)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

      if ( $this->is_visible_player_2( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player2first" class="ekc-required"><?php esc_html_e( 'Player 2', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player2first" name="player2first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" required 
                   value="<?php if ( $team && $team->get_player(1) ) echo esc_attr( $team->get_player(1)->get_first_name() ) ?>" />
            <input id="player2last" name="player2last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" required 
                   value="<?php if ( $team && $team->get_player(1) ) echo esc_attr( $team->get_player(1)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player2country', $team && $team->get_player(1) ? $team->get_player(1)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_3( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player3first" <?php if ( $this->is_required_player_3( $tournament ) ) echo 'class="ekc-required"' ?>><?php esc_html_e( 'Player 3', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player3first" name="player3first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_3( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(2) ) echo esc_attr( $team->get_player(2)->get_first_name() ) ?>" />
            <input id="player3last" name="player3last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_3( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(2) ) echo esc_attr( $team->get_player(2)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player3country', $team && $team->get_player(2) ? $team->get_player(2)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player4first" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'class="ekc-required"' ?>><?php esc_html_e( 'Player 4', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player4first" name="player4first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(3) ) echo esc_attr( $team->get_player(3)->get_first_name() ) ?>" />
            <input id="player4last" name="player4last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(3) ) echo esc_attr( $team->get_player(3)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player4country', $team && $team->get_player(3) ? $team->get_player(3)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player5first" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'class="ekc-required"' ?>><?php esc_html_e( 'Player 5', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player5first" name="player5first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(4) ) echo esc_attr( $team->get_player(4)->get_first_name() ) ?>" />
            <input id="player5last" name="player5last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(4) ) echo esc_attr( $team->get_player(4)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player5country', $team && $team->get_player(4) ? $team->get_player(4)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player6first" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'class="ekc-required"' ?>><?php esc_html_e( 'Player 6', 'ekc-tournament-manager' ) ?></label>
            <div><input id="player6first" name="player6first" type="text" placeholder="<?php esc_attr_e( 'First name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(5) ) echo esc_attr( $team->get_player(5)->get_first_name() ) ?>" />
            <input id="player6last" name="player6last" type="text" placeholder="<?php esc_attr_e( 'Last name', 'ekc-tournament-manager' ) ?>" maxlength="500" <?php if ( $this->is_required_player_4_5_6( $tournament ) ) echo 'required' ?>
                   value="<?php if ( $team && $team->get_player(5) ) echo esc_attr( $team->get_player(5)->get_last_name() ) ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down( 'player6country', $team && $team->get_player(5) ? $team->get_player(5)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
        </fieldset>          
<?php } ?>
        <fieldset>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary"><?php echo esc_html( $button_text ) ?></button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php echo esc_attr( $tournament->get_tournament_id() ) ?>" />
            <input id="teamid" name="teamid" type="hidden" value="<?php if ( $team ) echo esc_attr( $team->get_team_id() ) ?>" />
            <input id="action" name="action" type="hidden" value="<?php echo esc_attr( $action ) ?>" />
            <?php $nonce_helper->nonce_field( $nonce_helper->nonce_text( $action, 'team', $team ? $team->get_team_id() : -1 ) ) ?>
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
    $validation_helper = new Ekc_Validation_Helper();
    $page = $validation_helper->validate_get_text( 'page' );
		$action = $validation_helper->validate_get_text( 'action' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
		if ( $page === 'ekc-teams' && $action === 'csvexport' && $tournament_id ) {

			$db = new Ekc_Database_Access();
			$csv = $db->get_all_teams_as_csv( $tournament_id );
			$tournament = $db->get_tournament_by_id( $tournament_id );
			$file_name = sanitize_file_name( 'teams-' . $tournament->get_code_name() . '.csv' );

			$fp = fopen('php://output', 'w');
			if ($fp && $csv) {
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename="' . $file_name . '"');
				header('Pragma: no-cache');
				header('Expires: 0');
				foreach ($csv as $row) {
					fputcsv($fp, $row, ';', '"');
				}
				exit();
			}
		}
	}
}

