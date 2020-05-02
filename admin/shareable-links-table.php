<?php

class Ekc_Shareable_Links_Table extends WP_List_Table {

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
			'shareable_link'	=> 'Shareable link'
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
		);
		return $sortable_columns;
	  }

	  function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$db = new Ekc_Database_Access();
		$teams = $db->get_all_shareable_links_as_table( $this->tournament_id, $_REQUEST['orderby'], $_REQUEST['order'], $this->get_filter() );
		
		$this->items = $this->append_shareable_link( $teams );
	}

	private function append_shareable_link( $teams ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $this->tournament_id );
		$helper = new Ekc_Shareable_links_Helper();

		$teams_appended = array();
		foreach( $teams as $team ) {
			$shareable_link = '';
			$link_id = $team['shareable_link_id'];
			$url_prefix = $tournament->get_shareable_link_url_prefix();
			if ( $link_id ) {
				$shareable_link = $helper->create_shareable_link_url( $url_prefix, $link_id );
			}
			$team['shareable_link'] = $shareable_link;
			$teams_appended[] = $team;
		}
		return $teams_appended;
	}


	private function get_filter() {
		$filter = array();
		if ( isset( $_REQUEST['filter-active'] ) ) {
			$filter_active = $_REQUEST['filter-active'];
			if ( $filter_active === 'yes' ) {
				$filter['is_active'] = '1';
			}
			elseif ( $filter_active === 'no' ) {
				$filter['is_active'] = '0';
			}
		}
		if ( isset( $_REQUEST['filter-country'] ) ) {
			$filter_country = $_REQUEST['filter-country'];
			if ( in_array( $filter_country, array_keys( Ekc_Drop_Down_Helper::COUNTRY_COMMON ) ) ) {
				$filter['country'] = $filter_country;
			}
		}
		return $filter;
	}

	function column_name( $item ) {
		$actions = array();
		$actions['generate'] = sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Generate link</a>', $_REQUEST['page'], 'generate', $item['team_id'], $_REQUEST['tournamentid']);
		$actions['send'] = sprintf('<a href="?page=%s&amp;action=%s&amp;teamid=%s&amp;tournamentid=%s">Send link</a>', $_REQUEST['page'], 'send', $item['team_id'], $_REQUEST['tournamentid']);

		return sprintf('%s %s', $item['name'], $this->row_actions($actions) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'is_active':
			case 'country':
			case 'shareable_link':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}


	function no_items() {
		esc_html_e("No teams available.");
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

	protected function filter_yes_no_dropdown( $name, $filter_id ) {
		$value = isset( $_GET[$filter_id] ) ? $_GET[$filter_id] : Ekc_Drop_Down_Helper::FILTER_ALL;
		if ( ! in_array( $value, Ekc_Drop_Down_Helper::FILTER_ALL_YES_NO )) {
			$value = Ekc_Drop_Down_Helper::FILTER_ALL;
		}
		Ekc_Drop_Down_Helper::filter_yes_no_drop_down( $filter_id, $value, $name );
	}

	protected function filter_country_dropdown() {
		$filter_id = 'filter-country';
		$value = isset( $_GET[$filter_id] ) ? $_GET[$filter_id] : Ekc_Drop_Down_Helper::FILTER_ALL;
		if ( ! in_array( $value, array_keys( Ekc_Drop_Down_Helper::COUNTRY_COMMON ) ) ) {
			$value = Ekc_Drop_Down_Helper::FILTER_ALL;
		}
		Ekc_Drop_Down_Helper::filter_country_small_drop_down( $filter_id, $value );
	}
}

