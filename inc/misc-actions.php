<?php
/**
 * EDD Product Updates Miscellanous Actions
 *
 * @package    EDD_PUP
 * @author     Evan Luzi
 * @copyright  Copyright 2014 Evan Luzi, The Black and Blue, LLC
 * @since      0.9.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up and saves a product update email when added or edited.
 *
 * @since 0.9.3
 * @param array $data email data
 * @return void
 */
function edd_pup_create_email( $data ) {
	if ( isset( $data['edd_pup_nonce'] ) && wp_verify_nonce( $data['edd_pup_nonce'], 'edd_pup_nonce' ) ) {
		
		$posted = edd_pup_prepare_data( $data );
		$email_id = 0;
		
		if ( isset( $data['email-id'] ) ) {
			$email_id = $data['email-id'];
		}
		
		$post = edd_pup_save_email( $posted, $email_id );
		
		if ( 0 != $post ) {
			if ( $data['edd-action'] == 'add_pup_email' ) {
				
				wp_redirect( esc_url_raw( add_query_arg( array( 'view' => 'edit_pup_email', 'id' => $post, 'edd_pup_notice' => 2 ) ) ) );	
				
			} else {
			
				wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 1 ) ) );	
						
			}
			
			edd_die();

		} else {
		
			wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 3 ) ) );
			edd_die();
			
		}		
	}
}
add_action( 'edd_add_pup_email', 'edd_pup_create_email' );
add_action( 'edd_edit_pup_email', 'edd_pup_create_email' );


/**
 * Removes a product update email completely. Does NOT move to trash.
 * 
 * @access public
 * @param mixed $data
 * @return void
 */
function edd_pup_delete_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-pup-delete-nonce' ) )
		return;

	if ( false !== edd_pup_check_queue( $data['id'] ) ) {
		// Clear instances of this email in the queue
		global $wpdb;
		$wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $data['id'] ), array( '%d' ) );
	}
			
	$goodbye = wp_delete_post( $data['id'], true );
	
	if ( false === $goodbye || empty( $goodbye ) ) {
		wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 4, admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ) ) );
	} else {
		wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 5, admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ) ) );
		
	}
	
    exit;
}
add_action( 'edd_pup_delete_email', 'edd_pup_delete_email' );