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
 * @return $count (the number of emails logged in the queue to be sent)
 */
function edd_pup_ajax_start(){
	
	//if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'edd_pup_send_ajax' ) ) {
	//	return;
	//}
    
	global $wpdb;
	$email_id = intval( $_POST['email_id'] );
	$products = get_post_meta( $email_id, '_edd_pup_updated_products', true );
	$payments = edd_pup_get_all_customers();
	$count = 0;
	$i = 1;
		
	// Set email ID transient
	set_transient( 'edd_pup_sending_email', $email_id );
	
	// Update email status as in queue
	wp_update_post( array( 'ID' => $email_id, 'post_status' => 'pending' ) );
	
	// Start building queue
	foreach ( $payments as $customer ){
		
		// Don't send to customers who have unsubscribed from updates
		if ( edd_pup_user_send_updates( $customer->ID ) ){
			
			// Check what products customers are eligible for updates
			$customer_updates = edd_pup_eligible_updates( $customer->ID, $products );	
			
			// Add to queue only if customers have eligible updates available				
			if ( ! empty( $customer_updates ) ) {

				$queue[] = '('.$customer->ID.','.$email_id.', 0)';
				$count++;
								
				// Insert into database in batches of 1000
				if ( $i % 1000 == 0 ){

					$queueinsert = implode(',', $queue );
					$query = "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, sent) VALUES $queueinsert";

					$wpdb->query( $query );
					
					// Reset defaults for next batch
					$queue = '';
				}
			
			}
		}
			
		$i++;
	}
	
	// Insert leftovers or if batch is less than 1000
	if ( !empty( $queue ) ) {
		$queueinsert = implode(',', $queue );
		$query = "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, sent) VALUES $queueinsert";
		$wpdb->query( $query );
	}
    		
	echo $count;
	
	exit;

}
add_action( 'wp_ajax_edd_pup_ajax_start', 'edd_pup_ajax_start' );


/**
 * Fetches emails from queue and sends them in batches of 10
 * 
 * @access public
 * @since 0.9.2
 * @return $sent (number of emails successfully processed)
 */
function edd_pup_ajax_trigger(){
	global $wpdb;
	
	if ( !empty( $_POST['emailid'] ) && ( absint( $_POST['emailid'] ) != 0 ) ) {
		$email_id = $_POST['emailid'];
		
	} else {
		$email_id = get_transient( 'edd_pup_sending_email' );
	}
	
	$batch = $_POST['iteration'];
	$sent = $_POST['sent'];
	$limit = 10;
	$rows = array();
	
	$query = "SELECT * FROM $wpdb->edd_pup_queue WHERE email_id = $email_id AND sent = 0 LIMIT $limit";
	
	$customers = $wpdb->get_results( $query , ARRAY_A);

	foreach ( $customers as $customer ) {
	
			$trigger = edd_pup_ajax_send_email( $customer['customer_id'], $email_id );
						
			// Reset file download limits for customers' eligible updates
			$customer_updates = edd_pup_eligible_updates( $customer['customer_id'], get_post_meta( $email_id, '_edd_pup_updated_products', true ) );
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
	
	// Designate emails in database as having been sent
	if ( ! empty( $rows ) ) {
		$updateids = implode(',',$rows);
		$queryupdate = "UPDATE $wpdb->edd_pup_queue SET sent=1 WHERE eddpup_id IN ($updateids)";
		$wpdb->query( $queryupdate );
	}	
	
	echo $sent;
	exit;
}
add_action( 'wp_ajax_edd_pup_ajax_trigger', 'edd_pup_ajax_trigger' );

/**
 * Email the product update to the customer in a customizable message
 *
 * @param int $payment_id Payment ID
 * @param int $email_id Email ID for a edd_pup_email post-type
 * @return void
 */
function edd_pup_ajax_send_email( $payment_id, $email_id ) {

	$emailpost = get_post( $email_id );
	$emailmeta = get_post_custom( $email_id );

	$payment_data = edd_get_payment_meta( $payment_id );
	$email        = edd_get_payment_user_email( $payment_id );
	
	/* If subject doesn't use tags (and thus is the same for each customer)
	 * then store it in a transient for quick access on subsequent loops. */
	$subject = get_transient( 'edd_pup_subject' );
		
	if (false === $subject) {
		
		$_subject = edd_do_email_tags( $emailmeta['_edd_pup_subject'][0], $payment_id );
		
		if ( $subject == $_subject ) {
			$subject = set_transient( 'edd_pup_subject', $subject, 60 * 60 );
		} else {
			$subject = $_subject;
		}
	}
	
	$email_body_header = get_transient( 'edd_pup_email_body_header' );
	
	if ( false === $email_body_header ) {
		
		$email_body_header = edd_get_email_body_header();
		
		set_transient( 'edd_pup_email_body_header', $email_body_header, 60 * 60 );
	}
	
	$email_body_footer = get_transient( 'edd_pup_email_body_footer' );
	
	if ( false === $email_body_footer ) {
		
		$email_body_footer = edd_get_email_body_footer();
		
		set_transient( 'edd_pup_email_body_footer', $email_body_footer, 60 * 60 );
	}

	$message = $email_body_header;
	$message .= apply_filters( 'edd_purchase_receipt', edd_email_template_tags( $emailpost->post_content, $payment_data, $payment_id ), $payment_id, $payment_data );
	$message .= $email_body_footer;

	// Allow add-ons to add file attachments
	$attachments = apply_filters( 'edd_pup_attachments', array(), $payment_id, $payment_data );
	if ( apply_filters( 'edd_email_purchase_receipt', true ) ) {
		//$mailresult = wp_mail( $email, $subject, $message, '', $attachments );
		$mailresult = true;
	}
	
	// Update payment notes to log this email being sent	
	edd_insert_payment_note($payment_id, 'Sent product update email "'. $subject .'." <a href="/wp-admin/edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='.$email_id.'">View Email</a>');
    
    return $mailresult;
}


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
	wp_publish_post( get_transient('edd_pup_sending_email') );
	
	// Clear customer transients
	$payments = edd_pup_get_all_customers();	
	
	foreach ($payments as $customer){
		delete_transient( 'edd_pup_eligible_updates_'. $customer->ID );
	}
	
	// Clear queue for next send
	$wpdb->query("TRUNCATE TABLE $wpdb->edd_pup_queue");
	//$wpdb->delete( 'edd_pup_queue', array( 'email_id' => $email ), array( '%d' ) );

	// Flush remaining transients
	delete_transient( 'edd_pup_sending_email' );
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_subject' );	
	delete_transient( 'edd_pup_email_body_header' );
	delete_transient( 'edd_pup_email_body_footer' );
		
}
add_action( 'wp_ajax_edd_pup_ajax_end', 'edd_pup_ajax_end' );

/**
 * Clears emails from the queue when user takes action on "View Details"
 * popup of the admin screen
 * 
 * @access public
 * @param mixed $email (default: null)
 * @return void
 */
function edd_pup_clear_queue( $email = null ) {
	global $wpdb;
	
	// Clear queue
	if ( $_POST['email'] == 'all' ) {
	
		// Build array of queued emails before clearing table
		$queueemails = edd_pup_queue_emails();
		
		// Build array of sent email data before clearing table
		foreach ( $queueemails as $email => $id ) {
			$recipients[$id] = edd_pup_check_queue( $id );
		}
		
		// Clear the database table
		$qr = $wpdb->query( "TRUNCATE TABLE $wpdb->edd_pup_queue" );
		
	} else {
		
		$recipients = edd_pup_check_queue( $_POST['email'] );
		
		// Delete the rows WHERE the specified email_id matches
		$qr = $wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $_POST['email'] ), array( '%d' ) );
		
	}
	
	// If clear queue fails, bail out of function with error message, otherwise change post statuses
	if ( false === $qr ) {
		wp_die( __( 'Error: could not complete database query.', 'edd-pup' ), __( 'Clear Queue Error', 'edd-pup' ) );
		
	} else {
		
		if ( !empty( $queueemails ) ) {
		
			foreach ( $queueemails as $email => $id ) {
				$post[] = wp_update_post( array( 'ID' => $id, 'post_status' => 'abandoned' ) );
				update_post_meta ( $id, '_edd_pup_recipients', $recipients[$id] );
			}	
			
		} else if ( absint( $_POST['email'] ) != 0 ) {
			
			$post = wp_update_post( array( 'ID' => $_POST['email'], 'post_status' => 'abandoned' ) );
			update_post_meta ( $post, '_edd_pup_recipients', $recipients );
		
		} else {
			
			wp_die( __( 'Error: Valid email ID not supplied.', 'edd-pup' ), __( 'Clear Queue Error', 'edd-pup' ) );
		}
	
	// Clear customer transients
	$payments = edd_pup_get_all_customers();
	
	foreach ($payments as $customer){
		delete_transient( 'edd_pup_eligible_updates_'. $customer->ID );
	}

	// Flush remaining transients
	/*delete_transient( 'edd_pup_sending_email' );
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_subject' );	
	delete_transient( 'edd_pup_email_body_header' );
	delete_transient( 'edd_pup_email_body_footer' );*/
	
	//echo $qr;
	}
	
	die();
}
add_action( 'wp_ajax_edd_pup_clear_queue', 'edd_pup_clear_queue' );


function edd_pup_ajax_restart( $email = null ) {
	global $wpdb;
	
	// Clear queue
	if ( $_POST['email'] == 'all' ) {
	
		// Build array of queued emails before clearing table
		$queueemails = edd_pup_queue_emails();
		
		// Clear the database table
		$qr = $wpdb->query( "TRUNCATE TABLE $wpdb->edd_pup_queue" );
		
	} else {
		
		// Delete the rows WHERE the specified email_id matches
		$qr = $wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $_POST['email'] ), array( '%d' ) );
		
	}
	
	// If clear queue fails, bail out of function with error message, otherwise change post statuses
	if ( false === $qr ) {
		wp_die( __( 'Error: could not complete database query.', 'edd-pup' ), __( 'Clear Queue Error', 'edd-pup' ) );
	} else {
		
		if ( !empty( $queueemails ) ) {
		
			foreach ( $queueemails as $email => $id ) {
				$post[] = wp_update_post( array( 'ID' => $id, 'post_status' => 'abandoned' ) );
			}	
			
		} else if ( absint( $_POST['email'] ) != 0 ) {
			
			$post = wp_update_post( array( 'ID' => $_POST['email'], 'post_status' => 'abandoned' ) );
		
		} else {
			
			wp_die( __( 'Error: Valid email ID not supplied.', 'edd-pup' ), __( 'Clear Queue Error', 'edd-pup' ) );
		}
	
	// Clear customer transients
	$payments = edd_pup_get_all_customers();
	
	foreach ($payments as $customer){
		delete_transient( 'edd_pup_eligible_updates_'. $customer->ID );
	}

	}
	
	die();
}
add_action( 'wp_ajax_edd_pup_ajax_restart', 'edd_pup_ajax_restart' );