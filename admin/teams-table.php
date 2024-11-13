<?php

class Ekc_Teams_Table extends WP_List_Table {

	protected $tournament_id;

	public function __construct( $tournament_id ) {
		parent::__construct();
		$this->tournament_id = $tournament_id;
	}

	function get_columns(){
		$columns = array(
			'name'				=> 'Name',
			'is_active'			=> 'Active',
			'country'			=> 'Country',
			'club'              => 'Club / City',
			'email'				=> 'E-mail',
			'phone'				=> 'Phone',
			'registration_date'	=> 'Registered',
			'registration_order' => 'Order',
			'is_on_wait_list'	=> 'Waiting list',
			'seeding_score'		=> 'Seeding score',
			'players'			=> 'Players',
		);
		return $columns;
	}

	/**
	 * Mapping from UI-Table columns to SQL-Table columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
		  'name' 		=> array('name', false),
		  'is_active'	=> array('is_active', false),
		  'country'		=> array('country', false),
		  'club'        => array('club', false),
		  'registration_date'	=> array('registration_date', true),
		  'registration_order'	=> array('registration_order', false),
		  'is_on_wait_list'	=> array('is_on_wait_list', false),
		  'seeding_score'	=> array('seeding_score', false)
		);
		return $sortable_columns;
	  }

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$validation_helper = new Ekc_Validation_Helper();
		$order_by_column = $validation_helper->validate_get_text( 'orderby' );  
		$order = $validation_helper->validate_get_text( 'order' );
		// $order_by_column and $order are validated in get_all_teams_as_table
		$db = new Ekc_Database_Access();
		$teams = $db->get_all_teams_as_table( $this->tournament_id, $order_by_column, $order, $this->get_filter() );
		$this->items = $teams;
	}

	private function get_filter() {
		$validation_helper = new Ekc_Validation_Helper();
		$filter = array();

		$filter_active = $validation_helper->validate_get_text( 'filter-active' );
		if ( $filter_active === 'yes' ) {
			$filter['is_active'] = '1';
		}
		elseif ( $filter_active === 'no' ) {
			$filter['is_active'] = '0';
		}
		
		$filter_wait_list =  $validation_helper->validate_get_text( 'filter-wait-list' );
		if ( $filter_wait_list === 'yes' ) {
			$filter['is_on_wait_list'] = '1';
		}
		elseif ( $filter_wait_list === 'no' ) {
			$filter['is_on_wait_list'] = '0';
		}
		
		$filter_country =  $validation_helper->validate_get_text( 'filter-country' );
		if ( in_array( $filter_country, array_keys( Ekc_Drop_Down_Helper::COUNTRY_COMMON ) ) ) {
			$filter['country'] = $filter_country;
		}
	
		return $filter;
	}

	function column_name( $item ) {
		$actions = array();
		$validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );

		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
			$actions['edit'] = sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Edit</a>', esc_html( $page ), 'edit', esc_html( $item['team_id'] ), esc_html( $tournament_id ) );
		}
		return sprintf( '%s %s', $item['name'], $this->row_actions( $actions ) );
	}

	function column_is_active( $item ) {
		$actions = array();
		$nonce_helper = new Ekc_Nonce_Helper();
		$validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
		$team_id = $item['team_id'];

		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
			if ( filter_var( $item['is_active'], FILTER_VALIDATE_BOOLEAN ) ) {
				$inactivate_url = sprintf( '?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s', esc_html( $page ), 'inactivate', esc_html( $team_id ), esc_html( $tournament_id ) );
				$inactivate_url = $nonce_helper->nonce_url( $inactivate_url, $nonce_helper->nonce_text( 'inactivate', 'team', $team_id ) );
				$actions['inactivate'] = sprintf( '<a href="%s">Inactivate</a>', $inactivate_url );
			}
			else {
				$activate_url = sprintf( '?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s', esc_html( $page ), 'activate', esc_html( $team_id ), esc_html( $tournament_id ) );
				$activate_url = $nonce_helper->nonce_url( $activate_url, $nonce_helper->nonce_text( 'activate', 'team', $team_id ) );
				$actions['activate'] = sprintf( '<a href="%s">Activate</a>', $activate_url );
			}
		}
		return sprintf( '%s %s', $item['is_active'], $this->row_actions( $actions ) );
	}

	function column_is_on_wait_list( $item ) {
		$actions = array();
		$nonce_helper = new Ekc_Nonce_Helper();
		$validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$tournament_id = $validation_helper->validate_get_key( 'tournamentid' );
		$team_id = $item['team_id'];

		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
			if ( filter_var( $item['is_on_wait_list'], FILTER_VALIDATE_BOOLEAN ) ) {
				$offwaitlist_url = sprintf( '?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s', esc_html( $page ), 'offwaitlist', esc_html( $team_id ), esc_html( $tournament_id ) );
				$offwaitlist_url = $nonce_helper->nonce_url( $offwaitlist_url, $nonce_helper->nonce_text( 'offwaitlist', 'team', $team_id ) );
				$actions['offwaitlist'] = sprintf( '<a href="%s">Remove</a>',  $offwaitlist_url );
			}
			else {
				$onwaitlist_url = sprintf( '?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s', esc_html( $page ), 'onwaitlist', esc_html( $team_id ), esc_html( $tournament_id ) );
				$onwaitlist_url = $nonce_helper->nonce_url( $onwaitlist_url, $nonce_helper->nonce_text( 'onwaitlist', 'team', $team_id ) );
				$actions['onwaitlist'] = sprintf( '<a href="%s">Add</a>',  $onwaitlist_url );
			}
		}
		return sprintf( '%s %s', $item['is_on_wait_list'], $this->row_actions( $actions ) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'country':
			case 'club':
			case 'email':
			case 'phone':
			case 'registration_date':
			case 'registration_order':
			case 'seeding_score':
			case 'players':
			return $item[ $column_name ];
			default:
			return '';
		}
	}

	function no_items() {
		esc_html_e( 'No teams available yet.' );
	}

	/**
	 * Complete override of pagination method of super class.
	 * We dont use pagination, but simply display the total number of items.
	 */
	protected function pagination( $which ) {
		$total_items = count( $this->items );
?>
		<div class='tablenav-pages one-page'><span class='displaying-num'>
		<?php esc_html_e( sprintf(
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items ))) ?>
		</span></div>
<?php
	}

	protected function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
		<?php
		if ( 'top' === $which ) {

			$this->filter_active_dropdown();
			$this->filter_wait_list_dropdown();
			$this->filter_country_dropdown();
			submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'filter-submit' ) );
		}
		?>
		</div>
		<?php
	}

	protected function filter_active_dropdown() {
		$this->filter_yes_no_dropdown( 'Active', 'filter-active' );
	}

	protected function filter_wait_list_dropdown() {
		$this->filter_yes_no_dropdown( 'Waiting list', 'filter-wait-list' );
	}

	protected function filter_yes_no_dropdown( $name, $filter_id ) {
		$validation_helper = new Ekc_Validation_Helper();
		$value = $validation_helper->validate_get_text( $filter_id );
		if ( ! $value || ! in_array( $value, Ekc_Drop_Down_Helper::FILTER_ALL_YES_NO ) ) {
			$value = Ekc_Drop_Down_Helper::FILTER_ALL;
		}
		Ekc_Drop_Down_Helper::filter_yes_no_drop_down( $filter_id, $value, $name );
	}

	protected function filter_country_dropdown() {
		$validation_helper = new Ekc_Validation_Helper();
		$filter_id = 'filter-country';
		$value = $validation_helper->validate_get_text( $filter_id );
		if ( ! $value || ! in_array( $value, array_keys( Ekc_Drop_Down_Helper::COUNTRY_COMMON ) ) ) {
			$value = Ekc_Drop_Down_Helper::FILTER_ALL;
		}
		Ekc_Drop_Down_Helper::filter_country_small_drop_down( $filter_id, $value );
	}
}

