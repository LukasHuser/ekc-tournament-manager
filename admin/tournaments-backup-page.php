<?php

/**
 * Page to list all backups and allow to upload and import backups
 */
class Ekc_Tournaments_Backup_Page {

	public function create_tournaments_backup_page() {

    $nonce_helper = new Ekc_Nonce_Helper();
    $validation_helper = new Ekc_Validation_Helper();
		$action = $validation_helper->validate_get_text( 'action' );
		$file_name = rawurldecode( $validation_helper->validate_get_text( 'backup' ) );
		
    $upload_error_message = null;
    if ( $action === 'delete' ) {
      if ( $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'filename', $file_name ) ) ) {
        $this->delete_backup( $file_name );
      }
    }
    else {
      // handle POST
      if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $helper = new Ekc_Backup_Helper();
        $upload_error_message = $helper->upload_backup_file();
      }
    }
    $this->show_wp_header();
    $this->show_upload_error_message( $upload_error_message );
    if ( current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_BACKUPS ) ) {
      if ( $action === 'showupload' ) {
        $this->show_upload();
      }
      $this->show_backup_table();
    }
    $this->close_wp_content();
  }

  private function show_wp_header() {
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
    $upload_url = sprintf( '?page=%s&action=showupload', $page );
    ?>
    <div class="wrap">
    
      <h1 class="wp-heading-inline"><?php esc_html_e( 'Backup Files', 'ekc-tournament-manager' ); ?></h1>
      <a href="<?php echo esc_url( $upload_url ) ?>" class="page-title-action"><?php esc_html_e( 'Upload backup file', 'ekc-tournament-manager' ) ?></a>
    
      <hr class="wp-header-end">
    
    <?php 
  }

  private function show_upload_error_message( $upload_error_message ) {
    if ( $upload_error_message ) {
      ?>
      <p><?php echo esc_html( $upload_error_message ) ?></p>
      <?php
    }
  }

  /**
   * Close the css class 'wrap' div from wordpress
   */
  private function close_wp_content() {
    ?>
    </div><!-- .wrap -->
    <?php
  }
  
  private function show_upload() {
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
?>
<form enctype="multipart/form-data" action="<?php echo esc_url( '?page=' . $page ) ?>" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
    <?php esc_html_e( 'Backup file (JSON): ', 'ekc-tournament-manager' ) ?><input name="backup-file" type="file" accept="text/csv, application/json" />
    <input type="submit" class="ekc-button button"value="<?php esc_attr_e( 'Upload', 'ekc-tournament-manager' ) ?>" />
</form>
<?php
  }

	public function show_backup_table() {
		$backup_helper = new Ekc_Backup_Helper();
    $file_names = $backup_helper->get_all_backup_file_names();
    $table_data = array();
    foreach ( $file_names as $file_name ) {
      $file_size = $backup_helper->get_file_size( $file_name );
      $table_data[] = array(
          'file_name' => $file_name,
          'file_size' => $file_size );
    }

		$backup_table = new Ekc_Tournaments_Backup_Table($table_data);

    if ( $table_data ) {
      $backup_table->prepare_items();
      $backup_table->display();
    } 
    else {
      esc_html_e( 'No backup files available.', 'ekc-tournament-manager' );
    }
	}	

	public function delete_backup( $file_name ) {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_BACKUPS ) ) {
      return;
    }
		$backup_helper = new Ekc_Backup_Helper();
		$backup_helper->delete_backup_file( $file_name );
  }

  public function import_from_file( $file_name ) {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_BACKUPS ) ) {
      return;
    }
    $backup_helper = new Ekc_Backup_Helper();
		$backup_helper->import_from_json( $file_name );
  }

	public function download_backup_file() {
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_BACKUPS ) ) {
      return;
    }
    $validation_helper = new Ekc_Validation_Helper();
		$page = $validation_helper->validate_get_text( 'page' );
		$action = $validation_helper->validate_get_text( 'action' );
    $file_name = rawurldecode( $validation_helper->validate_get_text( 'backup' ) );
    
		if ( $page === 'ekc-backup' && $action === 'download' && $file_name ) {
      $backup_helper = new Ekc_Backup_Helper();
      $json = $backup_helper->get_file_content( $file_name );
      $fp = fopen('php://output', 'w');
      if ($fp && $json) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        fwrite( $fp, $json );
        fclose( $fp );
        exit();
      }
    }
  }
}
