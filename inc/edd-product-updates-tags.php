<?php
/*
Description: Send product update emails in batch
Version: 0.1
Author: Evan Luzi
Author URI: http://evanluzi.com
Contributors: Evan Luzi
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_prod_updates_email_tags( $payment_id ) {
	 edd_add_email_tag( 'updated_products', 'Display a list of updated products without links', 'edd_prod_updates_products_tag' );
	 edd_add_email_tag( 'updated_products_links', 'Display a list of updated products with links', 'edd_prod_updates_products_links_tag' );
	 edd_add_email_tag( 'unsubscribe_link', 'Output an unsubscribe link so users no longer receive product update emails', 'edd_prod_updates_unsub_tag' );
}
add_action( 'edd_add_email_tags', 'edd_prod_updates_email_tags' );

function edd_prod_updates_unsub_tag($payment_id) {

	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );
	$unsub_link_params = array(
		'order_id'  => $payment_id,
		'email'        => rawurlencode( $purchase_data['user_info']['email'] ),
		'purchase_key' => $purchase_data['key'],
		'edd_action' => 'prod_update_unsub'
	);
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );
	$unsubscribe = '<a href="'.$unsublink.'">Unsubscribe</a>';
	
	return $unsubscribe;
}

function edd_prod_updates_products_tag($payment_id) {

	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );

	$unsubscribe = 'replace me with the list';
	
	return $unsubscribe;
}

function edd_prod_updates_products_links_tag($payment_id) {

	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );

	$unsubscribe = 'replace me with links';
	
	return $unsubscribe;
}

function edd_prod_updates_verify_unsub_link() {
		if ( isset( $_GET['order_id'] )  && isset( $_GET['email'] ) && isset( $_GET['purchase_key'] ) && isset( $_GET['edd_action'] ) ) {
			
			if ( ! ( ($_GET['edd_action'] == 'prod_update_unsub') || ($_GET['edd_action'] == 'prod_update_resub') ) ) {
				return;
			}

			$order_id = $_GET['order_id'];
			$action   = $_GET['edd_action'];
			$email    = $_GET['email'];
			$key      = $_GET['purchase_key'];

			$meta_query = array(
				'relation'  => 'AND',
				array(
					'key'   => '_edd_payment_purchase_key',
					'value' => $key
				),
				array(
					'key'   => '_edd_payment_user_email',
					'value' => $email
				)
			);

			$payments = get_posts( array(
				'meta_query' => $meta_query,
				'post_type'  => 'edd_payment'
			) );
			
			if ( $payments ) {
				edd_prod_updates_unsub_page($order_id, $key, $email, $action);
			} else {
				wp_die( 'The email you requested to be removed was not found.' , 'Email Not Found');
			}
		}
}

function edd_prod_updates_unsub_page($payment_id, $purchase_key, $email, $action) {
 
    // Only update payment info if user is currently subscribed for updates
    if ( edd_prod_updates_unsub_status($payment_id) && $action == 'prod_update_unsub' ) {
    	
    	// Unsubscribe customer from futurue updates
	    $payment_meta['edd_send_prod_updates'] = false;
	
	    // Update the payment meta with the new array 
	    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
	    
	    // Update customer log with note about unsubscribing
		edd_insert_payment_note($payment_id, 'User unsubscribed from product update emails');	
	    	
    } else if (!edd_prod_updates_unsub_status($payment_id) && $action == 'prod_update_resub' ) {
    	// Unsubscribe customer from futurue updates
	    $payment_meta['edd_send_prod_updates'] = true;
	
	    // Update the payment meta with the new array 
	    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
	    
	    // Update customer log with note about unsubscribing
		edd_insert_payment_note($payment_id, 'User re-subscribed to product update emails');		    
    }
    	
    edd_prod_updates_unsub_message($payment_id, $purchase_key, $email, $action);
}

function edd_prod_updates_unsub_message($payment_id, $purchase_key, $email, $action){

	$resub_link_params = array(
		'order_id'  => $payment_id,
		'email'        => rawurlencode( $email ),
		'purchase_key' => $purchase_key,
		'edd_action' => 'prod_update_resub'
	);
	
	$unsub_link_params = array(
		'order_id'  => $payment_id,
		'email'        => rawurlencode( $email ),
		'purchase_key' => $purchase_key,
		'edd_action' => 'prod_update_unsub'
	);
	
	$resublink = add_query_arg( $resub_link_params, ''.home_url() );
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );
	
	if ($action == 'prod_update_unsub'){
		$title = 'Unsubscribed - You have been successfully removed from the list.';
		ob_start();
		?>
		<h1>Thank you</h1>
		<p>Your email <strong><?php echo $email; ?></strong> has been successfully removed from the list.</p>
		<p><em>Did you unsubscribe on accident? <a href="<?php echo $resublink;?>">Click here to resubscribe.</a></em></p>
		<?php
	} else if ($action == 'prod_update_resub'){
		$title = 'Resubscribed - You have successfully re-subscribed to the list.';
		ob_start();
		?>
		<h1>Thank you!</h1>
		<p>You have successfully re-subscribed <strong><?php echo $email; ?></strong> to the list.</p>
		<p><em><a href="<?php echo $unsublink;?>">Click here to unsubscribe.</a></em></p>
		<?php		
	}
		wp_die(ob_get_clean(), $title);
		
}

function edd_prod_updates_unsub_status( $payment_id = null ) {
    
    $status = true;
    $payment_meta = edd_get_payment_meta( $payment_id );

		if ( isset(  $payment_meta['edd_send_prod_updates'] ) && ! is_null( $payment_id ) && ! empty( $payment_id ) ) {

			if ( ! ($payment_meta['edd_send_prod_updates']) ) {
				$status = false;
			}
		}

	return $status;
}

/*function edd_prod_updates_email_tagsz($message, $payment_id, $payment_data) {

	$cart_items    = edd_get_payment_meta_cart_details( $payment_id );
	$file_urls     = '';
	$updated_list  = '<ul>';
	$updated_list_links = '<ul>';
	
	//edd_prod_updates_get_unsub_url( $post_id, $payment_data )
	
	//$unsubscribe = '<a href="#">Unsubscribe from future product updates</a>';
	$unsubscribe = var_dump($payment_data);
	
	foreach ( $cart_items as $item ) {
	
		$downloaded_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item['id'], $price_id ) . '<br/>';
	
	}
	
	$message = str_replace('{updated_products}', 'replace me with the list', $message);
	$message = str_replace('{updated_products_links}', 'replace the tag with links', $message);
	//$message = str_replace('{unsubscribe_link}', $unsubscribe, $message);
		
	return $message;
}
add_filter('edd_email_template_tags', 'edd_prod_updates_email_tags', 10, 3);
add_filter('edd_email_preview_template_tags', 'edd_prod_updates_email_tags', 10, 3);*/

function edd_prod_updates_get_tags(){
	$test = 'this is a test';
	
	return $test;
}