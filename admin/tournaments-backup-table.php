<?php

class Ekc_Tournaments_Backup_Table extends WP_List_Table {

	protected $tournaments_backup_data;

	public function __construct($tournaments_backup_data) {
		parent::__construct();
		$this->tournaments_backup_data = $tournaments_backup_data;
	}

	function get_columns(){
		$columns = array(
			'file_name'		=> 'Backup File',
			'file_size'		=> 'Size',	
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
		if( $_REQUEST['orderby'] === 'file_name' ) {
			uasort($data, array( $this, 'sort_by_file_name' ) );
		}
		if ( $_REQUEST['order'] === 'desc' ) {
			$data = array_reverse( $data );
		}
		
		$this->items = $data;
	}

	private function sort_by_file_name( $a, $b ) {
		return $a["file_name"] > $b["file_name"];
	}

	function column_file_name( $item ) {
		$file_name_encoded = rawurlencode( $item['file_name'] );
		$actions = array(
			'download' => sprintf('<a href="?page=%s&amp;action=%s&amp;backup=%s">Download</a>', $_REQUEST['page'], 'download', $file_name_encoded ),
			'delete' => sprintf('<a href="?page=%s&amp;action=%s&amp;backup=%s">Delete</a>', $_REQUEST['page'], 'delete', $file_name_encoded ),
			'jsonimport' => sprintf('<a href="?page=%s&amp;action=%s&amp;backup=%s">Import</a>', 'ekc-tournaments', 'jsonimport', $file_name_encoded ),
		);

		return sprintf('%s %s', $item['file_name'], $this->row_actions($actions) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'file_size':
			return $item[ $column_name ];
			default:
			return print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
		}
	}


	function no_items() {
		esc_html_e("No backups available.");
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

