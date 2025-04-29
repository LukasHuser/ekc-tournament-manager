<?php

/**
 * Helper class for import/export, backup and data transfer
 */
class Ekc_Backup_Helper {

	const BACKUP_SUB_DIRECTORY = '/ekc-tournament-manager/backup_data/';

	/**
	 * @param $file_name potentially provided by a user and could represent a full path
	 */
	private function backup_file_path( $file_name ) {
		$name = str_replace('\\', '/', $file_name );
		return $this->backup_path() . basename( $name );
	}

	private function backup_path() {
		$upload_dir = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir']; // /path/to/wp-content/uploads
		$backup_dir = $upload_basedir . self::BACKUP_SUB_DIRECTORY;
		$this->ensure_backup_dir_exists( $backup_dir );
		return $backup_dir;
	}

	private function ensure_backup_dir_exists( $backup_dir ) {
		if ( ! file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}
	}
	
	public function upload_backup_file() {
		$backup_file = isset( $_FILES['backup-file'] ) ? $_FILES['backup-file'] : null;
		if ( $backup_file ) { 
			// Temporarily override upload_dir
			add_filter( 'upload_dir', array( $this, 'upload_dir_backup_path' ) );
			// Temporarily override allowed mime types for upload
			add_filter( 'upload_mimes', array( $this, 'filter_upload_mimes' ) );
			// Fix unreliable mime type detection for json files
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'filter_wp_check_filetype_and_ext' ), 10, 5 );

			$moved_file = wp_handle_upload( $backup_file, array('test_form' => false ) );
						
			// Reset upload_dir
			remove_filter( 'upload_dir', array( $this, 'upload_dir_backup_path' ) );
			// Reset upload mimes
			remove_filter( 'upload_mimes', array( $this, 'filter_upload_mimes' ) );
			remove_filter( 'wp_check_filetype_and_ext', array( $this, 'filter_wp_check_filetype_and_ext' ) );

			if ( $moved_file && isset( $moved_file['error'] ) ) {
				return $moved_file['error'];
			}
		}
	}

	/**
	 * Override upload directory with backup directory.
	 */
	public function upload_dir_backup_path( $upload_dir ) {
		$backup_dir = $upload_dir['basedir'] . self::BACKUP_SUB_DIRECTORY;
		$this->ensure_backup_dir_exists( $backup_dir );
		return array(
			'path'   => $backup_dir,
			'url'    => $upload_dir['baseurl'] . self::BACKUP_SUB_DIRECTORY,
			'subdir' => self::BACKUP_SUB_DIRECTORY,
		) + $upload_dir;
	}

	/**
	 * Override allowed mime types to allow csv and json only.
	 */
	public function filter_upload_mimes( $mimes ) {
		return array( 'csv' => 'text/csv', 'json' => 'application/json' );
	}

	/**
	 * Mime type detection by file magic does not work reliably for json files.
	 * Accept text/plain as well as the correct mime type application/json.
	 */
	public function filter_wp_check_filetype_and_ext( $filter, $file, $filename, $mimes, $real_mime ) {
		if ( ! $filter['type'] && $filter['ext'] === 'json' && $real_mime === 'text/plain' ) {
			$filter['type'] = 'application/json';
		}
		return $filter;
	}

	public function get_all_backup_file_names() {
		return array_diff( scandir( $this->backup_path() ), array( '.', '..' ) );
	}

	public function get_file_size( $file_name ) {
		return $this->pretty_filesize( filesize( $this->backup_file_path( $file_name ) ) );
	}

	public function get_file_content( $file_name ) {
		$backup_file = $this->backup_file_path( $file_name );
		if ( file_exists( $backup_file ) ) {
			return file_get_contents( $backup_file );
		}
		return null;
	}

	private function pretty_filesize( $bytes, $decimals = 2 ) {
		$sz = 'BKMGTP'; // bytes, kilo, mega, giga, tera, peta
		$factor = floor( (strlen( $bytes ) - 1) / 3 );
		return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor )) . $sz[intval($factor)];
	}

	public function export_as_json( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
		$teams = $db->get_all_teams( $tournament_id );
		$results = $db->get_tournament_results( $tournament_id );

		$export = new Ekc_Tournament_Backup();
		$export->set_export_date( wp_date( 'Y-m-d H:i:s' ) );
		$export->set_tournament( $tournament );
		$export->set_teams( $teams );
		$export->set_results( $results );

		return wp_json_encode( $export );
	}
	
	public function import_from_json( $file_name ) {
		$json_mapper = new JsonMapper();
		$json = $this->get_file_content( $file_name );
		$import = $json_mapper->map( json_decode( $json ), new Ekc_Tournament_Backup());
		if ( $import->get_version() !== Ekc_Tournament_Backup::DATA_MODEL_VERSION ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>Failed to import tournament. 
				   Expected data model version <?php echo esc_html( Ekc_Tournament_Backup::DATA_MODEL_VERSION ) ?>, 
				   but was <?php echo esc_html( $import->get_version() ) ?>.</p>
			</div>
			<?php
			return;
		}

		$tournament_code_name = $import->get_tournament()->get_code_name();
		$db = new Ekc_Database_Access();
		$tournament_to_delete = $db->get_tournament_by_code_name( $tournament_code_name );
		if ( $tournament_to_delete ) {
			$db->delete_tournament( $tournament_to_delete->get_tournament_id() );
		} 

		$tournament_id = $db->insert_tournament( $import->get_tournament() );

		$helper = new Ekc_Shareable_Links_Helper();
		$helper->store_shareable_links_content( $tournament_id, $import->get_tournament()->get_shareable_link_url_prefix(), $import->get_tournament()->get_shareable_link_email_text(), $import->get_tournament()->get_shareable_link_sender_email() );
		
		$team_id_map = array();
		foreach ( $import->get_teams() as $team ) {
			$old_team_id = $team->get_team_id();
			$team->set_tournament_id( $tournament_id );
			$new_team_id = $db->insert_team( $team );
			$db->update_shareable_link_id( $new_team_id, $team->get_shareable_link_id() );
			$team_id_map[$old_team_id] = $new_team_id;
		}
		foreach ( $import->get_results() as $result ) {
			$result->set_tournament_id( $tournament_id );
			if ( $result->get_team1_id() ) {
				$result->set_team1_id( $this->get_new_team_id( $team_id_map, $result->get_team1_id() ) );
			}
			if ( $result->get_team2_id() ) {
				$result->set_team2_id( $this->get_new_team_id( $team_id_map, $result->get_team2_id() ) );
			}
			$new_id = $db->insert_tournament_result( $result );
		}
	}

	private function get_new_team_id( $team_id_map, $old_team_id ) {
		if ( $old_team_id >= Ekc_Team::TEAM_ID_BYE ) { // multiple byes for pitch limit mode possible
			return $old_team_id;
		} 
		return $team_id_map[$old_team_id];
	}

	public function store_backup( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
		$file_name = sanitize_file_name( 'tournament-' . $tournament->get_code_name() . '-' . wp_date(  'Y-m-d_H-i-s' ) . '.json' );
		file_put_contents( $this->backup_file_path( $file_name ), $this->export_as_json( $tournament_id ) );
	}

	public function delete_backup_file( $file_name ) {
		$backup_file = $this->backup_file_path( $file_name );
		if ( file_exists( $backup_file ) ) {
			wp_delete_file( $backup_file );
		}
	}
}



