<?php
/**
 * Based on plugin Duplicate Page 4.5.3
 * https://wordpress.org/plugins/duplicate-page
 * by mndpsingh287
 * licensed under GPLv2
 * 
 * Adapted to work with roles and capabilities as defined by the EKC Tournament Manager Plugin.
 * Instead of checking for the primitive capability 'edit_posts', we will check for the meta capability 'edit_page'.
 * Also, we intentionally only support pages, but not other post types.
 */
class Ekc_Page_Helper {

    public function ekc_duplicate_page( $post_id ) {
        $nonce_helper = new Ekc_Nonce_Helper();
        $validation_helper = new Ekc_Validation_Helper();
        $post_id = $validation_helper->validate_get_key( 'post' );
        $action =  $validation_helper->validate_get_text( 'action' );
        
        if ( ! $post_id || $action !== 'ekc_duplicate_page' || ! $nonce_helper->validate_nonce( $nonce_helper->nonce_text( $action, 'page', $post_id ) ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
        
        $post = get_post( $post_id );
        $new_post_author = wp_get_current_user()->ID;
        $new_post_status = 'draft';
        $new_post_title = $post->post_title . ' ' . __('Copy');
        if ( isset( $post ) && $post != null ) {
            $new_post_data = array(
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'post_author' => $new_post_author,
                    'post_content' => $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_parent' => $post->post_parent,
                    'post_password' => $post->post_password,
                    'post_status' => $new_post_status,
                    'post_title' => $new_post_title,
                    'post_type' => $post->post_type,
                    'to_ping' => $post->to_ping,
                    'menu_order' => $post->menu_order,
            );
            $new_post_id = wp_insert_post( $new_post_data );
            if ( is_wp_error( $new_post_id ) ) {
                wp_die(__($new_post_id->get_error_message()));
            }
            
            // get all current post terms and set them to the new post draft
            $taxonomies = array_map( 'sanitize_text_field', get_object_taxonomies( $post->post_type ) );
            if ( !empty( $taxonomies ) && is_array( $taxonomies ) ) {
                foreach ( $taxonomies as $taxonomy ) {
                    $post_terms = wp_get_object_terms( $post_id, $taxonomy, array('fields' => 'slugs') );
                    wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
                }
            }

            // duplicate all post meta, except for the public template flag
            $post_meta_keys = get_post_custom_keys( $post_id );
            if ( !empty( $post_meta_keys ) ) {
                foreach ( $post_meta_keys as $meta_key ) {
                    $meta_values = get_post_custom_values( $meta_key, $post_id );
                    foreach ( $meta_values as $meta_value ) {
                        $meta_value = maybe_unserialize( $meta_value );
                        if ( $meta_key !== Ekc_Role_Helper::CUSTOM_FIELD_EKC_PUBLIC_TEMPLATE ) {
                            update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
                        }
                    }
                }
            }
                
            // Elementor compatibility
            if ( is_plugin_active( 'elementor/elementor.php' ) ) {
                $css = Elementor\Core\Files\CSS\Post::create( $new_post_id );
                $css->update();
            } 
            
            $redirect_url = 'edit.php';
            if ( $post->post_type !== 'post' ) {
                $redirect_url .= '?post_type=' . $post->post_type;
            }
            // redirect to page list and exit
            wp_redirect( esc_url_raw( admin_url( $redirect_url ) ) ); 
            exit;
        }
    }

    /*
     * Add the duplicate link to action list for post_row_actions
     */
    public function ekc_duplicate_page_link( $actions, $post ) {
        if ( $post->post_type !== 'page' ) {
            return $actions;
        }

        if ( isset( $post ) && current_user_can( 'edit_page', $post->ID ) ) {
            $nonce_helper = new Ekc_Nonce_Helper();
            $post_id = intval( $post->ID );
            $duplicate_url = sprintf( 'admin.php?action=ekc_duplicate_page&amp;post=%d', $post_id ); 
            $duplicate_url = $nonce_helper->nonce_url( $duplicate_url, $nonce_helper->nonce_text( 'ekc_duplicate_page', 'page', $post_id ) );
            $actions['duplicate'] = sprintf( '<a href="%s" title="%s" rel="permalink">%s</a>', $duplicate_url,  __('Duplicate as draft'), __('Duplicate') );
        }
        
        return $actions;
    }
}

