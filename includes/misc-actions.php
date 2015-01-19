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
		
		$post = edd_pup_sanitize_save( $data );
		
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
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-pup-delete-nonce' ) ) {
		return;
	}
		
	// Clear instances of this email in the queue
	if ( false !== edd_pup_check_queue( $data['id'] ) ) {
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


/**
 * Duplicates an email that already exists
 * and informs user if duplication is successful or not.
 * 
 * @since 1.1
 * @param mixed $data
 * @return void
 */
function edd_pup_duplicate_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-pup-duplicate-nonce' ) ) {
		return;
	}
	
	$new_post = edd_pup_create_duplicate_email( $data['id'] );
	
	if ( false == $new_post ) {
		wp_die( 'Something went wrong', 'oops' );
	}

	if ( false === $new_post || empty( $new_post ) ) {
		wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 6, admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ) ) );
	} else {
		wp_redirect( esc_url_raw( add_query_arg( 'edd_pup_notice', 7, admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ) ) );
		
	}
	
	exit;
		
}
add_action( 'edd_pup_duplicate_email', 'edd_pup_duplicate_email' );