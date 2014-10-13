<?php
/**
 * EDD Product Updates Miscellanous Functions
 *
 * @package    EDD_PUP
 * @author     Evan Luzi
 * @copyright  Copyright 2014 Evan Luzi, The Black and Blue, LLC
 * @since      0.9.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Prepares posted data array to be used in saving emails.
 * 
 * @access public
 * @param mixed $data
 * @return array $posted (processed array of email data)
 */
function edd_pup_prepare_data( $data ) {

	// Setup the email details
	$posted = array();

	foreach ( $data as $key => $value ) {
		
		if ( $key != 'edd_pup_nonce' && $key != 'edd-action' && $key != 'edd_pup_edit_url' ) {

			if ( 'email' == $key || 'product' == $key )
				$posted[ $key ] = $value;
			elseif ( is_string( $value ) || is_int( $value ) )
				$posted[ $key ] = strip_tags( addslashes( $value ) );
			elseif ( is_array( $value ) )
				$posted[ $key ] = array_map( 'absint', $value );
		}
	}
	
	return $posted;
}

/**
 * Saves an email. If the email already exists, it is updated.
 *
 * @since 0.9.2
 * @param string $data
 * @param int $email_id
 * @return int the post ID of the email saved
 */
function edd_pup_save_email( $data, $email_id = null ) {
	
	// Set variables that are the same for all customers
	$from_name = isset( $data['from_name'] ) ? $data['from_name'] : get_bloginfo('name');
	$from_email = isset( $data['from_email'] ) ? $data['from_email'] : get_option('admin_email');
	$subject = apply_filters( 'edd_purchase_subject', ! empty( $data['subject'] )
		? wp_strip_all_tags( $data['subject'], true )
		: __( 'New Product Update', 'edd-pup' ) );
	$products = isset( $data['product'] ) ? $data['product'] : '';
		
	if ( 0 != $email_id ) {
		
		// Don't save any changes unless email is editable
		if ( get_post_status( $email_id ) != 'draft' ) {
			return;
		}
		
		$updateargs = array(
			'ID' => $email_id,
			'post_content' => $data['message'],
			'post_title' => $data['title'],
			'post_excerpt' => $data['subject']
		);
		
		$update_id = wp_update_post( $updateargs );
		update_post_meta ( $email_id, '_edd_pup_from_name', $from_name );
		update_post_meta ( $email_id, '_edd_pup_from_email', $from_email );
		update_post_meta ( $email_id, '_edd_pup_subject', $data['subject'] );
		update_post_meta ( $email_id, '_edd_pup_message', $data['message'] );
		update_post_meta ( $email_id, '_edd_pup_updated_products', $products );
		update_post_meta ( $email_id, '_edd_pup_recipients', $data['recipients'] );

		if ( ( $update_id != 0 ) && ( $update_id == $email_id ) ) {
			return $email_id;
		}
		
	} else {
		// Build post parameters array for custom post
		$post = array(
		  'post_content'   => $data['message'],
		  'post_name'      => '',
		  'post_title'     => $data['title'],
		  'post_status'    => 'draft',
		  'post_type'      => 'edd_pup_email',
		  'post_author'    => '',
		  'ping_status'    => 'closed',
		  'post_parent'    => 0,
		  'menu_order'     => 0,
		  'to_ping'        => '',
		  'pinged'         => '',
		  'post_password'  => '',
		  'guid'           => '',
		  'post_content_filtered' => '',
		  'post_excerpt'   => $data['subject'], //maybe $headers
		  'comment_status' => 'closed'
		);
	
		// Create post and get the ID
		$create_id = wp_insert_post( $post );
		
		// Get number of recipients for this email
		$recipients = edd_pup_customer_count( $create_id, $products );
		
		// Insert custom meta for newly created post
		if ( 0 != $create_id )	{
			add_post_meta ( $create_id, '_edd_pup_from_name', $from_name, true );
			add_post_meta ( $create_id, '_edd_pup_from_email', $from_email, true );
			add_post_meta ( $create_id, '_edd_pup_subject', $data['subject'], true );
			add_post_meta ( $create_id, '_edd_pup_message', $data['message'], true );
			add_post_meta ( $create_id, '_edd_pup_updated_products', $products, true );
			add_post_meta ( $email_id, '_edd_pup_recipients', $data['recipients'] );	
		}
		
    	if ( 0 != $create_id) {	
			return $create_id;
		}
	}
}

/**
 * Count number of customers who will receive product update emails
 *
 * 
 * @access public
 * @return $customercount (number of customers eligible for product updates)
 */
function edd_pup_customer_count( $email_id = null, $products = null ){
	
	if ( empty( $email_id ) && empty( $products ) ) {
		return false;
	}
	
	if ( isset( $email_id ) ) {
		$products = get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );
	}
	
	$total = 0;
	$payments = edd_pup_get_all_customers();
	
	foreach ( $payments as $customer ){	
		
		if ( edd_pup_user_send_updates( $customer->ID ) ){
		
			$customer_updates = edd_pup_eligible_updates( $customer->ID, $products, false );
			
			if ( ! empty( $customer_updates ) ) {
				$total++;
			}
		}
	}
    
    return $total;
}

/**
 * Returns all payment history posts / customers
 * 
 * @access public
 * @return object (all edd_payment post types)
 */
function edd_pup_get_all_customers(){

	$customers = get_transient( 'edd_pup_all_customers' );
	
	if ( false === $customers ) {
	
		$queryargs = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'orderby'          => 'ID',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'edd_payment',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => true
			);
		$customers = get_posts($queryargs);
		
		set_transient( 'edd_pup_all_customers', $customers, 60 );
	}
		
	return $customers;
}

function edd_pup_get_all_downloads(){

	$products = get_transient( 'edd_pup_all_downloads' );
	
	if ( false === $products ) {
		$products = array();
		$downloads = get_posts(	array( 'post_type' => 'download', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		if ( !empty( $downloads ) ) {
		    foreach ( $downloads as $download ) {
		    	
		        $products[ $download->ID ] = get_the_title( $download->ID );
		
		    }
		}
		
		set_transient( 'edd_pup_all_downloads', $products, 60 );
	}
	
	return $products;
}

/**
 * Returns products that a customer is eligible to receive updates for 
 * 
 * @access public
 * @param mixed $payment_id
 * @param mixed $updated_products	array of products selected to update stored
 * @param bool $object	determines whether to return array of item IDs or item objects
 * in $edd_options['prod_updates_products']
 *
 * @return array $customer_updates
 */
function edd_pup_eligible_updates( $payment_id, $updated_products, $object = true ){
	
	//$customer_updates = get_transient( 'edd_pup_eligible_updates_'.$payment_id );
	
	if ( empty( $payment_id) || empty( $updated_products ) ) {
		return false;
	}
	
	$customer_updates = false;
	
	if ( false === $customer_updates ) {
		global $edd_options;
		
		$customer_updates = '';
		$cart_items = edd_get_payment_meta_cart_details( $payment_id, false );
			
		if ( isset( $edd_options['edd_pup_license']) && is_plugin_active('edd-software-licensing/edd-software-licenses.php' ) ) {
			$licenses = edd_pup_get_license_keys($payment_id);
		}
		
		foreach ( $cart_items as $item ){
		
			if ( array_key_exists( $item['id'], $updated_products ) ){
				
				if ( ! empty($licenses) && isset($edd_options['edd_pup_license']) && get_post_meta( $item['id'], '_edd_sl_enabled', true ) ) {
					
					$checkargs = array(
						'key'        => $licenses[$item['id']],
						'item_name'  => $item['name']
					);
					
					$check = edd_software_licensing()->check_license($checkargs);
					
					if ( $check === 'valid' ) {				
						if ( $object ){
							$customer_updates[] = $item;
						} else {
							$customer_updates[] = $item['id'];
						}		
					}
					
				} else {
						if ( $object ){
							$customer_updates[] = $item;
						} else {
							$customer_updates[] = $item['id'];
						}		
				}
			}	
		}
	
		set_transient( 'edd_pup_eligible_updates_'.$payment_id, $customer_updates, 60*60 );
	}
	
	return $customer_updates;
}

/**
 * Return array of license keys matched with download ID for payment/customer
 * 
 * @access public
 * @param mixed $payment_id
 *
 * @return array $key
 */
function edd_pup_get_license_keys( $payment_id ){
	$key = '';
	$licenses = edd_software_licensing()->get_licenses_of_purchase( $payment_id );
	
	if ( $licenses ) {	
		foreach ( $licenses as $license ){
			$id = get_post_meta( $license->ID, '_edd_sl_download_id', true );
			$key[$id] = get_post_meta( $license->ID, '_edd_sl_key', true );
		}
	}
	
	return $key;
}


/**
 * Checks database for specified email in queue to see if there are
 * email messages that are waiting to be processed and sent.
 * 
 * @access public
 * @param mixed $email_id (default: null)
 * @return array of queue totals (total, sent, queue). False if email_id is not set.
 */
function edd_pup_check_queue( $email_id = null ) {
	
	if ( ! empty( $email_id ) ){
		global $wpdb;
	
		$query =
		"SELECT
		     COUNT(*) total,
		     SUM(case when sent = 0 then 1 else 0 end) queue,
		     SUM(case when sent = 1 then 1 else 0 end) sent,
		     MAX(sent_date) date
		 FROM $wpdb->edd_pup_queue
		 WHERE email_id = $email_id";
		
		$totals = $wpdb->get_results( $query, ARRAY_A );
		
		return $totals[0];
		
	} else {
	
	return false;
	
	}	
}

/**
 * Gets the total number of rows in the queue
 * 
 * @access public
 * @return string number of emails remaining
 */
function edd_pup_check_queue_total() {

	if ( false === get_transient( 'edd_pup_sending' ) ){
		global $wpdb;
		
		$query = "SELECT COUNT(eddpup_id) FROM $wpdb->edd_pup_queue WHERE sent = 0";
		$total = $wpdb->get_results( $query , ARRAY_A);
	}

	return $total[0]['COUNT(eddpup_id)'];
}


/**
 * Finds which unique emails are in the queue and returns list
 * 
 * @access public
 * @return array $email_list (unique emails inside of the queue)
 */
function edd_pup_queue_emails() {

	$email_list = false;
	
	if ( false === get_transient( 'edd_pup_sending' ) ) {
		global $wpdb;
		
		$query = "SELECT DISTINCT email_id FROM $wpdb->edd_pup_queue WHERE sent = 0";
		
		$emails = $wpdb->get_results( $query , ARRAY_A );
		
		foreach ( $emails as $email ) {
			$email_list[] = $email['email_id'];
		}
	}
	
	return $email_list;
}


/**
 * Determines whether a specified email is currently being sent or not
 * 
 * @access public
 * @param mixed $emailid (default: null)
 * @return bool true if email is processing, false if not
 */
function edd_pup_is_processing( $emailid = null ) {
	if ( empty( $emailid ) ) {
		return;
	}
	
	$email_list = edd_pup_queue_emails();
	
	if ( is_array( $email_list) && in_array( $emailid, $email_list ) ) {
		$totals = edd_pup_check_queue( $emailid );
		
		if ( $totals['queue'] > 0 && $emailid == get_transient( 'edd_pup_sending_email' ) ) {
			return true;
		}
		
	} else {
	
		return false;
		
	}
	
}