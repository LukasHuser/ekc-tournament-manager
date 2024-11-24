<?php

class Ekc_Tournaments_Table extends WP_List_Table {

	function get_columns(){
		$columns = array(
			'code_name'				=> esc_html__( 'Code Name' ),
			'name'					=> esc_html__( 'Name' ),
			'team_size'				=> esc_html__( 'Team size' ),
			'tournament_date'		=> esc_html__( 'Date' ),
			'max_teams'				=> esc_html__( 'Max teams' ),
			'is_wait_list_enabled'	=> esc_html__( 'Wait list' ),
			'tournament_system'		=> esc_html__( 'Tournament system' ),
			'elimination_rounds'	=> esc_html__( 'Elimination rounds' ),
			'swiss_system_rounds'	=> esc_html__( 'Swiss rounds' ),
			'owner_user'			=> esc_html__( 'Owner user' )
		);
		return $columns;
	}

	function get_sortable_columns(){
		$columns = array(
			'code_name'			=> array( 'code_name', false ),
			'name'				=> array( 'name', false ),
			'team_size'			=> array( 'team_size', false ),
			'tournament_date'	=> array( 'tournament_date', false ),
			'max_teams'			=> array( 'max_teams', false ),
			'tournament_system'	=> array( 'tournament_system', false ),
			'elimination_rounds'	=> array( 'elimination_rounds', false ),
			'swiss_system_rounds'	=> array( 'swiss_system_rounds', false ),
			'owner_user'		=> array( 'owner_user', false ),
		);
		return $columns;
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$validation_helper = new Ekc_Validation_Helper();
		$order_by_column = $validation_helper->validate_get_text( 'orderby' );  
		$order = $validation_helper->validate_get_text( 'order' );
		// $order_by_column and $order are validated in get_all_tournaments_as_table
		$db = new Ekc_Database_Access();
		$tournaments_data = $db->get_all_tournaments_as_table( $order_by_column, $order );

		$this->items = $tournaments_data;
	}

	function column_code_name( $item ) {
		$actions = array();
		$tournament_id = $item['tournament_id'];
		$can_edit_tournaments = current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id );
		$can_manage_tournaments = current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ); 
		if ( $can_edit_tournaments || $can_manage_tournaments ) {
			$teams_url = sprintf( '?page=%s&tournamentid=%s', 'ekc-teams', $tournament_id );
			$actions['teams'] = sprintf('<a href="%s">%s</a>', esc_url( $teams_url ), esc_html__( 'Teams' ) );
		}
		if ( $can_edit_tournaments ) {
			$validation_helper = new Ekc_Validation_Helper();
			$page = $validation_helper->validate_get_text( 'page' );
			$edit_url = sprintf( '?page=%s&action=%s&tournamentid=%s', $page, 'edit', $tournament_id );
			$actions['edit'] = sprintf('<a href="%s">%s</a>', esc_url( $edit_url ), esc_html__( 'Edit' ) );
		}
		return sprintf('%s %s', esc_html( $item['code_name'] ), $this->row_actions( $actions ) );
	}

	function column_name( $item ) {
		$actions = array();
		$tournament_id = $item['tournament_id'];

		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
			if ( $item['elimination_rounds'] ) {
				$elimination_bracket_url = sprintf( '?page=%s&action=%s&tournamentid=%s', 'ekc-bracket', 'elimination-bracket', $tournament_id );
				$actions['elimination-bracket'] = sprintf('<a href="%s">%s</a>', esc_url( $elimination_bracket_url ), esc_html__( 'Elimination Bracket' ) );
			}
			if ( $item['swiss_system_rounds'] > 0 ) {
				$swiss_system_url = sprintf( '?page=%s&action=%s&tournamentid=%s', 'ekc-swiss', 'swiss-system', $tournament_id );
				$actions['swiss-system'] = sprintf('<a href="%s">%s</a>', esc_url( $swiss_system_url ), esc_html__( 'Swiss System' ) );
			}
		}
		return sprintf('%s %s', esc_html( $item['name'] ), $this->row_actions( $actions ) );
	}

	function column_owner_user( $item ) {
		$actions = array();
		$tournament_id = $item['tournament_id'];
		$nonce_helper = new Ekc_Nonce_Helper();
		$validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );

		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_EDIT_TOURNAMENTS, $tournament_id ) ) {
			$copy_url = sprintf('?page=%s&action=%s&tournamentid=%s', $page, 'copy', $tournament_id );
			$actions['copy'] = sprintf('<a href="%s">%s</a>', esc_url( $copy_url ), esc_html__( 'Copy' ) );
		}
		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
			$result_log_url = sprintf( '?page=%s&tournamentid=%s', 'ekc-result-log', $tournament_id );
			$actions['result-log'] = sprintf('<a href="%s">%s</a>', esc_url( $result_log_url ), esc_html__( 'Result Log' ) );
			
			$backup_url = sprintf('?page=%s&action=%s&tournamentid=%s', $page, 'backup', $tournament_id );
			$backup_url = $nonce_helper->nonce_url( $backup_url, $nonce_helper->nonce_text( 'backup', 'tournament', $tournament_id ) );
			$actions['backup'] = sprintf('<a href="%s">%s</a>', esc_url( $backup_url ), esc_html__( 'Backup' ) );
		}
		if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_DELETE_TOURNAMENTS, $tournament_id ) ) {
			$delete_url = sprintf('?page=%s&action=%s&tournamentid=%s', $page, 'delete', $tournament_id );
			$delete_url = $nonce_helper->nonce_url( $delete_url, $nonce_helper->nonce_text( 'delete', 'tournament', $tournament_id ) );
			$actions['delete'] = sprintf('<a href="%s">%s</a>', esc_url( $delete_url ), esc_html__( 'Delete' ) );
		}
		return sprintf('%s %s', esc_html( $item['owner_user'] ), $this->row_actions( $actions ) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'team_size':
			case 'tournament_date':
			case 'max_teams':
			case 'is_wait_list_enabled':
			case 'tournament_system':
			case 'elimination_rounds':
			case 'swiss_system_rounds':
			return esc_html( $item[ $column_name ] );
			default:
			return '';
		}
	}

	function no_items() {
		esc_html_e( 'No tournaments available yet.' );
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
}

