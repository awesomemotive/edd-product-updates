<?php
/**
 * EDD Product Updates Email Tags
 *
 * Functions and actions that register tags for use in the EDD email system,
 * as well as functions and actions that interpret those tags on email send.
 *
 *
 * @package    EDD_PUP
 * @author     Evan Luzi
 * @copyright  Copyright 2014 Evan Luzi, The Black and Blue, LLC
 * @since      0.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Builds the email queue and stores it in the edd_pup_queue db table
 * 
 * @access public
 * @param mixed $data
 * @return $realcount (the number of emails logged in the queue to be sent)
 */
function edd_pup_ajax_queue( $data ){
    $start = microtime(TRUE);
    
	global $wpdb;
	global $edd_options;
	
	$query = "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, sent) VALUES";
	$email_id = get_transient( 'edd_pup_email_id' );
	$payments = edd_pup_get_all_customers();
	$precount = edd_pup_customer_count();
	$realcount = 0;
	$i = 1;
	
	foreach ( $payments as $customer ){
		
		// Don't send to customers who have unsubscribed from updates
		if ( edd_pup_user_send_updates( $customer->ID ) ){
			
			// Check what products customers are eligible for updates
			$customer_updates = edd_pup_eligible_updates( $customer->ID, $edd_options['prod_updates_products'] );	
			
			// Add to queue only if customers have eligible updates available				
			if ( ! empty( $customer_updates ) ) {

				$queue[] = '('.$customer->ID.','.$email_id.', 0)';
				$realcount++;
								
				// Insert into database in batches of 1000
				if ( $i % 1000 == 0 ){

					$query .= implode(',', $queue );
					$wpdb->query( $query );
					
					// Reset defaults for next batch
					$queue = '';
					$query = "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, sent) VALUES";
				}
			
			}
		}
			
		$i++;
	}
	
	// Insert leftovers or if batch is less than 1000
	if ( !empty( $queue ) ) {
		$query .= implode(',', $queue );
		$wpdb->query( $query );
	}
	
    $finish = microtime(TRUE);
    $totaltime = $finish - $start; 
    write_log('edd_pup_ajax_queue took '.$totaltime.' seconds to execute.');
    		
	return $realcount;
	
	exit;

}
add_action( 'wp_ajax_edd_pup_ajax_start', 'edd_pup_ajax_queue' );


/**
 * Fetches emails from queue and sends them in batches of 10
 * 
 * @access public
 * @since 0.9.2
 * @return $sent (number of emails successfully processed)
 */
function edd_pup_ajax_trigger(){
    $start = microtime(TRUE);
    	
	global $wpdb;
	global $edd_options;
	
	$email_data = get_post_custom( get_transient( 'edd_pup_email_id' ) );
	$batch = $_POST['iteration'];
	$sent = $_POST['sent'];
	$limit = 10;
	$offset = $limit * $batch;
	$rows = array();
	
	$query = "SELECT * FROM $wpdb->edd_pup_queue LIMIT $limit OFFSET $offset";
	
	$customers = $wpdb->get_results( $query , ARRAY_A);

	foreach ( $customers as $customer ) {
	
		if ( $customer['sent'] == 0 ) {
	
			$trigger = edd_pup_trigger_email( $customer['customer_id'], $email_data['_edd_pup_subject'][0], $email_data['_edd_pup_message'][0], $email_data['_edd_pup_headers'][0] );
						
			// Reset file download limits for customers' eligible updates
			$customer_updates = edd_pup_eligible_updates( $customer['customer_id'], $edd_options['prod_updates_products'] );
			foreach ( $customer_updates as $download ) {
				$limit = edd_get_file_download_limit( $download['id'] );
				if ( ! empty( $limit ) ) {
					edd_set_file_download_limit_override( $download['id'], $customer['customer_id'] );
				}
			}
			
			if ( true == $trigger ) {
				$rows[] = $customer['eddpup_id'];
				$sent++;
			}
		}
		
	}
	
	// Designate emails in database as having been sent
	if ( ! empty( $rows ) ) {
		$updateids = implode(',',$rows);
		$queryupdate = "UPDATE $wpdb->edd_pup_queue SET sent=1 WHERE eddpup_id IN ($updateids)";
		$wpdb->query( $queryupdate );
	}

    $finish = microtime(TRUE);
    $totaltime = $finish - $start; 
    write_log('edd_pup_ajax_trigger took '.$totaltime.' seconds to execute.');
	
	return $sent;
    
	exit;
}
add_action( 'wp_ajax_edd_pup_ajax_trigger', 'edd_pup_ajax_trigger' );


/**
 * Cleans up AJAX batch resending by publishing email post-type,
 * deleting all transients, and emptying the edd_pup_queue db table.
 * 
 * @access public
 * @return void
 */
function edd_pup_ajax_end(){
	global $wpdb;
	
	// Update email post status to publish
	wp_publish_post( get_transient('edd_pup_email_id') );
	
	// Clear customer transients
	$payments = edd_pup_get_all_customers();	
	
	foreach ($payments as $customer){
		delete_transient( 'edd_pup_eligible_updates_'. $customer->ID );
	}
	
	// Clear queue for next send
	//$wpdb->query("TRUNCATE TABLE $wpdb->edd_pup_queue");

	// Flush remaining transients
	delete_transient( 'edd_pup_email_id' );
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_subject' );	
	delete_transient( 'edd_pup_email_body_header' );
	delete_transient( 'edd_pup_email_body_footer' );
		
}
add_action( 'wp_ajax_edd_pup_ajax_end', 'edd_pup_ajax_end' );