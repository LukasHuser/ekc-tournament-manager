<?php

class Ekc_Tournaments_Backup_Table extends WP_List_Table {

	protected $tournaments_backup_data;

	public function __construct($tournaments_backup_data) {
		parent::__construct();
		$this->tournaments_backup_data = $tournaments_backup_data;
	}

	function get_columns(){
		$columns = array(
			'file_name'		=> esc_html__( 'Backup File' ),
			'file_size'		=> esc_html__( 'Size' )	
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'file_name'		=> array('file_name', true)
		);
		return $sortable_columns;
	  }

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$data = $this->tournaments_backup_data;
		$validation_helper = new Ekc_Validation_Helper();
		$order_by_column = $validation_helper->validate_get_text( 'orderby' );  
		$order = $validation_helper->validate_get_text( 'order' );
		
		if( $order_by_column === 'file_name' ) {
			uasort($data, array( $this, 'sort_by_file_name' ) );
		}
		if ( $order === 'desc' ) {
			$data = array_reverse( $data );
		}
		
		$this->items = $data;
	}

	private function sort_by_file_name( $a, $b ) {
		return $a['file_name'] > $b['file_name'];
	}

	function column_file_name( $item ) {
		$file_name = $item['file_name'];
		$file_name_encoded = rawurlencode( $file_name );
		$actions = array();
		$nonce_helper = new Ekc_Nonce_Helper();
		$validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );

		$download_url = sprintf( '?page=%s&action=%s&backup=%s', $page, 'download', $file_name_encoded );
		$actions['download'] = sprintf( '<a href="%s">%s</a>', esc_url( $download_url ), esc_html__( 'Download' ) );
		
		$delete_url = sprintf( '?page=%s&action=%s&backup=%s', $page, 'delete', $file_name_encoded );
		$delete_url =  $nonce_helper->nonce_url( $delete_url, $nonce_helper->nonce_text( 'delete', 'filename', $file_name ) );
		$actions['delete'] = sprintf('<a href="%s">%s</a>', esc_url( $delete_url ), esc_html__( 'Delete' ) );

		$jsonimport_url = sprintf( '?page=%s&action=%s&backup=%s', 'ekc-tournaments', 'jsonimport', $file_name_encoded );
		$jsonimport_url =  $nonce_helper->nonce_url( $jsonimport_url, $nonce_helper->nonce_text( 'jsonimport', 'filename', $file_name ) );
		$actions['jsonimport'] = sprintf( '<a href="%s">%s</a>', esc_url( $jsonimport_url ), esc_html__( 'Import' ) );

		return sprintf( '%s %s', esc_html( $file_name ), $this->row_actions( $actions ) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'file_size':
			return esc_html( $item[ $column_name ] );
			default:
			return '';
		}
	}


	function no_items() {
		esc_html_e( 'No backups available.' );
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

