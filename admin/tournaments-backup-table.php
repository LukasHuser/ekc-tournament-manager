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

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->tournaments_backup_data;
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
}

