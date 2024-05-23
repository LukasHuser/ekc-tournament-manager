<?php

/**
 * Page to list the result log for a given tournament
 */
class Ekc_Result_Log_Page {

	public function create_result_log_page() {
		$tournament_id = ( isset($_GET['tournamentid'] ) ) ? sanitize_key( wp_unslash( $_GET['tournamentid'] ) ) : null;
    if ( ! current_user_can( Ekc_Role_Helper::CAPABILITY_EKC_MANAGE_TOURNAMENTS, $tournament_id ) ) {
      return;
    }

    $this->show_wp_header();
    $this->show_result_log_table( $tournament_id );
    $this->close_wp_content();
  }

  private function show_wp_header() {
    ?>
    <div class="wrap">    
      <h1 class="wp-heading-inline"><?php _e( 'Result Log' ); ?></h1>
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
  

	public function show_result_log_table( $tournament_id ) {
		$result_log_table = new Ekc_Result_Log_Table( $tournament_id );
    $result_log_table->prepare_items();
    $result_log_table->display();
	}	
}
