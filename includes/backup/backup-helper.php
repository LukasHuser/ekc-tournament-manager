<?php

/**
 * Helper class for import/export, backup and data transfer
 */
class Ekc_Backup_Helper {

	public function backup_path() {
		$upload_dir = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir']; // /path/to/wp-content/uploads
		$backup_dir = $upload_basedir . '/ekc-tournament-manager/backup_data/';
		if ( ! file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}
		return $backup_dir;
	}

	public function safe_move_uploaded_file( $original_name, $temp_file ) {
		$name = str_replace('\\', '/', $original_name );
		move_uploaded_file( $temp_file, $this->backup_path() . basename( $name ));
	}

	public function get_all_backup_file_names() {
		return array_diff( scandir( $this->backup_path() ), array( '.', '..' ) );
	}

	public function get_file_size( $file_name ) {
		return $this->pretty_filesize( filesize( $this->backup_path() . $file_name ) );
	}

	public function get_file_content( $file_name ) {
		return file_get_contents( $this->backup_path() . $file_name );
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
		$export->set_export_date( date( "Y-m-d H:i:s" ) );
		$export->set_tournament( $tournament );
		$export->set_teams( $teams );
		$export->set_results( $results );

		return json_encode( $export );
	}
	
	public function import_from_json( $file_name ) {
		$json_mapper = new JsonMapper();
		$json = $this->get_file_content( $file_name );
		$import = $json_mapper->map( json_decode( $json ), new Ekc_Tournament_Backup());
		if ( $import->get_version() !== Ekc_Tournament_Backup::DATA_MODEL_VERSION ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>Failed to import tournament. 
				   Expected data model version <?php _e(Ekc_Tournament_Backup::DATA_MODEL_VERSION); ?>, 
				   but was <?php _e($import->get_version()); ?>.</p>
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
		$helper->store_shareable_links_content( $tournament_id, '', $import->get_tournament()->get_shareable_link_email_text(), $import->get_tournament()->get_shareable_link_sender_email() );
		
		$team_id_map = array();
		foreach ( $import->get_teams() as $team ) {
			$old_team_id = $team->get_team_id();
			$team->set_tournament_id( $tournament_id );
			$new_team_id = $db->insert_team( $team );
			$team_id_map[$old_team_id] = $new_team_id;
		}
		foreach ( $import->get_results() as $result ) {
			$result->set_tournament_id( $tournament_id );
			if ( $result->get_team1_id() ) {
				$result->set_team1_id( $team_id_map[$result->get_team1_id()]);
			}
			if ( $result->get_team2_id() ) {
				$result->set_team2_id( $team_id_map[$result->get_team2_id()]);
			}
			$new_id = $db->insert_tournament_result( $result );
		}
	}

	public function store_backup( $tournament_id ) {
		$db = new Ekc_Database_Access();
		$tournament = $db->get_tournament_by_id( $tournament_id );
		$file_name = "tournament-" . $tournament->get_code_name() . '-' . date(  "Y-m-d-H:i:s" ) . ".json";
		file_put_contents( $this->backup_path() . $file_name, $this->export_as_json( $tournament_id ));
	}

	public function delete_backup_file( $file_name ) {
		unlink( $this->backup_path() . $file_name );
	}
}



