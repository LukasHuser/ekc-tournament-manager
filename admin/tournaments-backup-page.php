<?php

/**
 * Page to list all backups and allow to upload and import backups
 */
class Ekc_Tournaments_Backup_Page {

	public function create_tournaments_backup_page() {
	
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$file_name = ( isset($_GET['backup'] ) ) ? rawurldecode( $_GET['backup'] ) : '';
		if ( $action === 'delete' ) {
      $this->delete_backup( $file_name );
    }
    else {
      // handle POST
      if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        $file_size = $_FILES['backup-file']['size'];
        if ( $file_size <= 0 ){
          ?><div class="notice notice-error is-dismissible"><p>Failed to upload file. File was empty.</p></div><?php
        }
        elseif ( $file_size > 5000000 ) {
          ?><div class="notice notice-error is-dismissible"><p>Failed to upload file. File too large. Maximum size is 5MB.</p></div><?php
        }
        $helper = new Ekc_Backup_Helper();
        $helper->safe_move_uploaded_file( $_FILES['backup-file']['name'], $_FILES['backup-file']['tmp_name']);
      }
    }
    $this->show_wp_header();
    if ( $action === 'showupload' ) {
      $this->show_upload();
    }
    $this->show_backup_table();
    $this->close_wp_content();
  }

  private function show_wp_header() {
    ?>
    <div class="wrap">
    
      <h1 class="wp-heading-inline"><?php _e( 'Backup Files' ); ?></h1>
      <a href="?page=<?php esc_html_e($_REQUEST['page']) ?>&amp;action=showupload" class="page-title-action"><?php _e( 'Upload backup file' ); ?></a>
    
      <hr class="wp-header-end">
    
    <?php 
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
?>
<form enctype="multipart/form-data" action="?page=<?php esc_html_e($_REQUEST['page']) ?>" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
    Backup file (JSON): <input name="backup-file" type="file" />
    <input type="submit" value="Upload" />
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
      esc_html_e("No backup files available.");
    }
	}	

	public function delete_backup( $file_name ) {
		$backup_helper = new Ekc_Backup_Helper();
		$backup_helper->delete_backup_file( $file_name );
  }

  public function import_from_file( $file_name ) {
    $backup_helper = new Ekc_Backup_Helper();
		$backup_helper->import_from_json( $file_name );
  }

	public function download_backup_file() {
		$page = ( isset($_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$action = ( isset($_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    $file_name = ( isset($_GET['backup'] ) ) ? rawurldecode( $_GET['backup'] )  : '';
    
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
