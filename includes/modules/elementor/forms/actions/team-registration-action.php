<?php

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Form Action for an Elementor Form. 
 * 
 * Allows to read form content from a registration form and
 * directly save registered teams to the database.
 * 
 */
class Ekc_Form_Action_Team_Registration extends Action_Base {

	public function get_name() {
		return 'ekc-team';
	}

	public function get_label() {
		return 'EKC Team Registration';
	}

	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_team',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'tournament',
			[
				'label' => 'Tournament',
				'type' => Controls_Manager::TEXT,
				'description' => 'Tournament code name (e.g. EKC18-1)',
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'active',
			[
				'label' => 'Active',
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'render_type' => 'none',
				'options' => [
					'yes' => 'Yes',
					'no' => 'No',
				],
			]
		);

		$widget->add_control(
			'waitlist',
			[
				'label' => 'Waiting List',
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'description' => 'Puts a team directly on the waiting list',
				'render_type' => 'none',
				'options' => [
					'yes' => 'Yes',
					'no' => 'No',
				],
			]
		);

		$widget->end_controls_section();
	}

	public function on_export( $element ) {}

	public function run( $record, $ajax_handler ) {
		$db = new Ekc_Database_Access();
		$settings = $record->get( 'form_settings' );
		$tournament_code_name = sanitize_text_field( wp_unslash( $settings[ 'tournament' ] ) );
		
		if ( !$tournament_code_name ) {
			return;
		}
		$tournament = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( !$tournament ) {
			return;
		}
		
		$team_active = filter_var( $settings[ 'active' ], FILTER_VALIDATE_BOOLEAN );
		$waiting_list = filter_var( $settings[ 'waitlist' ], FILTER_VALIDATE_BOOLEAN );
		$fields = $record->get( 'fields' );

		// 1vs1 tournament
		$first_name = $this->get_field_value( $fields, 'firstname' );
		$last_name = $this->get_field_value( $fields, 'lastname' );
		
		// 3vs3 tournament
		$team_name = $this->get_field_value( $fields, 'teamname' );
		$first_name1 = $this->get_field_value( $fields, 'firstname1' );
		$last_name1 = $this->get_field_value( $fields, 'lastname1' );
		$first_name2 = $this->get_field_value( $fields, 'firstname2' );
		$last_name2 = $this->get_field_value( $fields, 'lastname2' );
		$first_name3 = $this->get_field_value( $fields, 'firstname3' );
		$last_name3 = $this->get_field_value( $fields, 'lastname3' );
		$first_name4 = $this->get_field_value( $fields, 'firstname4' );
		$last_name4 = $this->get_field_value( $fields, 'lastname4' );
		$first_name5 = $this->get_field_value( $fields, 'firstname5' );
		$last_name5 = $this->get_field_value( $fields, 'lastname5' );
		$first_name6 = $this->get_field_value( $fields, 'firstname6' );
		$last_name6 = $this->get_field_value( $fields, 'lastname6' );

		// common fields
		$email = $this->get_field_value( $fields, 'email' );
		$phone = $this->get_field_value( $fields, 'phone' );
		$country = $this->get_field_value( $fields, 'country' );
		$club = $this->get_field_value( $fields, 'club' );

		$team = new Ekc_Team();
		$team->set_tournament_id( $tournament->get_tournament_id() );
		$team->set_registration_date(date("Y-m-d H:i:s")); // current date
		$team->set_active( $team_active );
		$team->set_on_wait_list( $waiting_list );
		$team->set_email( $email );
		$team->set_phone( $phone );
		$team->set_country( $country );
		$team->set_club( $club );
		
		$players = array();
		if ( $team_name ) {
			$team->set_name( $team_name );
			$players[] = $this->create_player( $first_name1, $last_name1, $country, true );
			$players[] = $this->create_player( $first_name2, $last_name2, $country, false );
			$players[] = $this->create_player( $first_name3, $last_name3, $country, false );
			if ( $last_name4 ) {
				$players[] = $this->create_player( $first_name4, $last_name4, $country, false );
			}
			if ( $last_name5 ) {
				$players[] = $this->create_player( $first_name5, $last_name5, $country, false );
			}
			if ( $last_name6 ) {
				$players[] = $this->create_player( $first_name6, $last_name6, $country, false );
			}
		}
		else {
			$team->set_name( $first_name . ' ' . $last_name );
			$players[] = $this->create_player( $first_name, $last_name, $country, true );
		}

		$team->set_players($players);
		$db->insert_team($team);
	}

	private function get_field_value( $fields, $field_id ) {
		if ( $fields[ $field_id ] ) {
			return sanitize_text_field( wp_unslash( $fields[ $field_id ][ 'value' ] ) );
		}
		return '';
	} 

	private function create_player( $first_name, $last_name, $country, $is_captain ) {
		$player = new Ekc_Player();
		$player->set_active( true );
		$player->set_first_name( $first_name );
		$player->set_last_name( $last_name );
		$player->set_country( $country );
		$player->set_captain( $is_captain );
		return $player;
	}
}
