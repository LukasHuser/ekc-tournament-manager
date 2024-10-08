<?php

/**
 * Admin page which allows administraton of teams
 */
class Ekc_Teams_Admin_Page {

	public function create_teams_page() {

		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
		$team_id = ( isset($_GET['teamid'] ) ) ? sanitize_key( wp_unslash( $_GET['teamid'] ) ) : null;
    if ( $tournament_id && ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

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
    if ( isset($_POST['tournamentid'] ) ) {
      $tournament_id = intval( sanitize_key( wp_unslash( $_POST['tournamentid'] ) ) );
      if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
        return;
      } 
      $team->set_tournament_id( $tournament_id );
      $has_data = true;
    }
		if ( isset($_POST['teamid'] ) ) {
			$team->set_team_id( intval( sanitize_key( wp_unslash( $_POST['teamid'] ) ) ) );
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

	public function show_teams( $tournament_id ) {
    if ( ! $tournament_id ) {
      return;
    }
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );

		$teams_table = new Ekc_Teams_Table( $tournament_id );
?>
<div class="wrap">

  <h1 class="wp-heading-inline"><?php esc_html_e( $tournament->get_name() ) ?></h1>
  <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e($tournament_id) ?>&amp;action=new" class="page-title-action"><?php _e( 'New team' ) ?></a>
  <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;tournamentid=<?php esc_html_e($tournament_id) ?>&amp;action=csvexport" class="page-title-action"><?php _e( 'CSV export' ) ?></a>
  <?php
  if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
  ?><a href="?page=ekc-links&amp;tournamentid=<?php esc_html_e($tournament_id) ?>&amp;action=shareable-links" class="page-title-action"><?php _e( 'Shareable links' ) ?></a>
  <?php
  }
  ?>

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
    $this->show_team( 'new', 'Create a new team', 'Create team', $tournament );
  }

	public function show_edit_team( $team_id ) {
    $db = new Ekc_Database_Access();
		$team = $db->get_team_by_id( $team_id );
		$tournament = $db->get_tournament_by_id($team->get_tournament_id());

    $this->show_team( 'edit', 'Edit team', 'Save team', $tournament, $team );
  }

	public function show_team( $action, $title, $button_text, $tournament, $team = null ) {
?>
  <div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( $title ) ?></h1>
    <hr class="wp-header-end">

      <form class="ekc-form" method="post" action="?page=<?php esc_html_e( $_REQUEST['page'] ) ?>&amp;tournamentid=<?php esc_html_e($tournament->get_tournament_id() ) ?>" accept-charset="utf-8">
        <fieldset>
        <legend><h3><?php _e('Team') ?></h3></legend>
<?php if ( $tournament->get_team_size() !== Ekc_Drop_Down_Helper::TEAM_SIZE_1 || ! $tournament->is_player_names_required() ) { ?>
          <div class="ekc-control-group">
            <label for="name" class="ekc-required"><?php _e('Name') ?></label>
            <input id="name" name="name" type="text" maxlength="500" required value="<?php $team ? esc_html_e( $team->get_name() ) : _e('') ?>" />
          </div>         
          <div class="ekc-control-group">
            <label for="country"><?php _e('Country') ?></label>
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("country", $team ? $team->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?>
          </div>
<?php } ?>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="active" name="active" type="checkbox" value="true" <?php $team && $team->is_active() ? _e( "checked" ) : _e('') ?>/>
                 <label for="active"><?php _e('Is active') ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="waitlist" name="waitlist" type="checkbox" value="true" <?php $team && $team->is_on_wait_list() ? _e( "checked" ) : _e('') ?>/>
                 <label for="waitlist"><?php _e('Is on waiting list') ?></label></div>
          </div>
          <div class="ekc-control-group">
            <div></div>
            <div><input id="registrationfee" name="registrationfee" type="checkbox" value="true" <?php $team && $team->is_registration_fee_paid() ? _e( "checked" ) : _e('') ?> />
                 <label for="registrationfee"><?php _e('Registration fee paid') ?></label></div>
          </div>
          <div class="ekc-control-group">
            <label for="club"><?php _e('Sports club / city') ?></label>
            <input id="club" name="club" type="text" maxlength="500" value="<?php $team ? esc_html_e( $team->get_club() ) : _e('') ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="email"><?php _e('E-mail') ?></label>
            <input id="email" name="email" type="email" placeholder="my.name@mail.com" maxlength="500" value="<?php $team ? esc_html_e( $team->get_email() ) : _e('') ?>" />
          </div>
          <div class="ekc-control-group">
            <label for="phone"><?php _e('Phone') ?></label>
            <div><input id="phone" name="phone" type="tel" placeholder="+41 79 888 77 66" maxlength="50" value="<?php $team ? esc_html_e( $team->get_phone() ) : _e('') ?>" />
            <p>Phone number format including a national prefix such as +41 for Switzerland, +49 for Germany etc.</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="registrationorder"><?php _e('Order') ?></label>
            <div><input id="registrationorder" name="registrationorder" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_registration_order() ) : _e('') ?>" />
                  <p>Sort order used in registration list and waiting list</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="camping"><?php _e('Camping') ?></label>
            <div><input id="camping" name="camping" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_camping_count() ) : _e('') ?>" />
                 <p>Number of persons</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="breakfast"><?php _e('Breakfast') ?></label>
            <div><input id="breakfast" name="breakfast" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_breakfast_count() ) : _e('') ?>"/>
                 <p>Number of persons</p></div>
          </div>
<?php if ( $tournament->get_swiss_system_rounds() > 0 ) { ?>
        <fieldset>
        <legend><h3><?php _e('Swiss System') ?></h3></legend>
          <div class="ekc-control-group">
            <label for="seedingscore"><?php _e('Seeding score') ?></label>
            <div><input id="seedingscore" name="seedingscore" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_seeding_score() ) : _e('') ?>" />
                 <p>Seeding score defining an initial ranking</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="initialscore"><?php _e('Initial score') ?></label>
            <div><input id="initialscore" name="initialscore" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_initial_score() ) : _e('') ?>" />
                 <p>Initial score for an accelerated system</p></div>
          </div>
          <div class="ekc-control-group">
            <label for="virtualrank"><?php _e('Virtual rank') ?></label>
            <div><input id="virtualrank" name="virtualrank" type="number" step="any" value="<?php $team ? esc_html_e( $team->get_virtual_rank() ) : _e('') ?>" />
                 <p>Virtual rank for a top team which is excluded in additional ranking rounds</p></div>
          </div>
        </fieldset>
<?php }
      if ( $this->is_visible_player_1( $tournament ) ) { ?>
        <fieldset>
        <legend><h3><?php _e('Players') ?></h3></legend>
          <div class="ekc-control-group">
            <label for="player1first" class="ekc-required"><?php _e('Captain') ?></label>
            <div><input id="player1first" name="player1first" type="text" placeholder="First name" maxlength="500" required 
                   value="<?php $team && $team->get_player(0) ? esc_html_e( $team->get_player(0)->get_first_name() ) : _e('') ?>"/>
            <input id="player1last" name="player1last" type="text" placeholder="Last name" maxlength="500" required 
                   value="<?php $team && $team->get_player(0) ? esc_html_e( $team->get_player(0)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player1country", $team && $team->get_player(0) ? $team->get_player(0)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

      if ( $this->is_visible_player_2( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player2first" class="ekc-required"><?php _e('Player 2') ?></label>
            <div><input id="player2first" name="player2first" type="text" placeholder="First name" maxlength="500" required 
                   value="<?php $team && $team->get_player(1) ? esc_html_e( $team->get_player(1)->get_first_name() ) : _e('') ?>" />
            <input id="player2last" name="player2last" type="text" placeholder="Last name" maxlength="500" required 
                   value="<?php $team && $team->get_player(1) ? esc_html_e( $team->get_player(1)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player2country", $team && $team->get_player(1) ? $team->get_player(1)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_3( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player3first" <?php $this->is_required_player_3( $tournament ) ? _e('class="ekc-required"') : _e('') ?>><?php _e('Player 3') ?></label>
            <div><input id="player3first" name="player3first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(2) ? esc_html_e( $team->get_player(2)->get_first_name() ) : _e('') ?>" />
            <input id="player3last" name="player3last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_3( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(2) ? esc_html_e( $team->get_player(2)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player3country", $team && $team->get_player(2) ? $team->get_player(2)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player4first" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('class="ekc-required"') : _e('') ?>><?php _e('Player 4') ?></label>
            <div><input id="player4first" name="player4first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(3) ? esc_html_e( $team->get_player(3)->get_first_name() ) : _e('') ?>" />
            <input id="player4last" name="player4last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(3) ? esc_html_e( $team->get_player(3)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player4country", $team && $team->get_player(3) ? $team->get_player(3)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player5first" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('class="ekc-required"') : _e('') ?>><?php _e('Player 5') ?></label>
            <div><input id="player5first" name="player5first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(4) ? esc_html_e( $team->get_player(4)->get_first_name() ) : _e('') ?>" />
            <input id="player5last" name="player5last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(4) ? esc_html_e( $team->get_player(4)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player5country", $team && $team->get_player(4) ? $team->get_player(4)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
<?php }

     if ( $this->is_visible_player_4_5_6( $tournament ) ) { ?>
          <div class="ekc-control-group">
            <label for="player6first" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('class="ekc-required"') : _e('') ?>><?php _e('Player 6') ?></label>
            <div><input id="player6first" name="player6first" type="text" placeholder="First name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(5) ? esc_html_e( $team->get_player(5)->get_first_name() ) : _e('') ?>" />
            <input id="player6last" name="player6last" type="text" placeholder="Last name" maxlength="500" <?php $this->is_required_player_4_5_6( $tournament ) ? _e('required') : _e('') ?>
                   value="<?php $team && $team->get_player(5) ? esc_html_e( $team->get_player(5)->get_last_name() ) : _e('') ?>" />
            <?php Ekc_Drop_Down_Helper::country_small_drop_down("player6country", $team && $team->get_player(5) ? $team->get_player(5)->get_country() : Ekc_Drop_Down_Helper::SELECTION_NONE ) ?></div>
          </div>
        </fieldset>          
<?php } ?>
        <fieldset>
          <div class="ekc-controls">
            <button type="submit" class="ekc-button ekc-button-primary button button-primary"><?php esc_html_e( $button_text ) ?></button>
            <input id="tournamentid" name="tournamentid" type="hidden" value="<?php esc_html_e( $tournament->get_tournament_id() ) ?>" />
            <input id="teamid" name="teamid" type="hidden" value="<?php $team ? esc_html_e( $team->get_team_id() ) : _e('') ?>" />
            <input id="action" name="action" type="hidden" value="<?php esc_html_e( $action ) ?>" />
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
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
      return;
    }
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

