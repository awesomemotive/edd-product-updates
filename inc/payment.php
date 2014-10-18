<?php
/**
 * EDD Product Updates Payment History
 *
 * Add unsubscribe option on payment history page for customers
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
 * Add the "Send Product Updates?" option to payment history page
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_order_history( $payment_id ){

	$payment_meta = edd_get_payment_meta( $payment_id );
	$sendupdates = isset( $payment_meta['edd_send_prod_updates'] ) ? $payment_meta['edd_send_prod_updates'] : true ;

	ob_start();
	?>
			<div class="edd-admin-box-inside edd-send-updates">
				<p>
					<span class="label" title="<?php _e( 'When checked, customer will receive product update emails.', 'edd-pup' ); ?>"><i data-code="f463" class="dashicons dashicons-update"></i></span>&nbsp;
					<input type="checkbox" name="edd-send-product-updates" id="edd_send_product_updates" value="1"<?php checked( true, $sendupdates, true ); ?>/>
					<label class="description" for="edd_send_product_updates"><?php _e( 'Send Product Updates', 'edd-pup' ); ?></label>
				</p>
			</div>
			<?php

	echo ob_get_clean();
}
add_action('edd_view_order_details_update_inner','edd_pup_order_history');

/**
 * Store the unsubscribe custom field into EDD's payment meta
 * 
 * @access public
 * @param mixed $payment_meta
 * @return void
 */
function edd_pup_store_field( $payment_meta ) {

	$payment_meta['edd_send_prod_updates'] = isset( $_POST['edd-send-product-updates'] ) ? true : false ;
    
    return $payment_meta;
}
add_filter( 'edd_payment_meta', 'edd_pup_store_field');

/**
 * Check "Send Product Updates?" option by default on payment completion
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_send_updates_default( $payment_id ) {

    // get the payment meta
    $payment_meta = get_post_meta( $payment_id, '_edd_payment_meta', true );
 
    // update our checkbox
    $payment_meta['edd_send_prod_updates'] = true;
 
    // update the payment meta with the new array 
    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );

}
add_action( 'edd_complete_purchase', 'edd_pup_send_updates_default' );
add_action( 'edd_insert_payment', 'edd_pup_send_updates_default' );

/**
 * Save unsubscribe field when modified on payment history page
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_updated_edited_purchase( $payment_id ) {
 
    // get the payment meta
    $payment_meta = get_post_meta( $payment_id, '_edd_payment_meta', true );
 
    // update our checkbox
    $payment_meta['edd_send_prod_updates'] = isset( $_POST['edd-send-product-updates'] ) ? true : false;
 
    // update the payment meta with the new array 
    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'edd_pup_updated_edited_purchase' );

/**
 * Checks the users that are eligible for updates
 * 
 * @access public
 * @param mixed $products (array of products using id => name format. default: null)
 * @param bool $subscribed (whether to query for subscribed - true - or unsubscribed - false - customers. default: true)
 *
 * @return obj array of payment_ids that are subscribed for updates and have purchashed at least one product being updated.
 */
function edd_pup_user_send_updates( $products = null, $subscribed = true ){
    if ( empty( $products ) ) {
	    return;
    }
    
    global $wpdb;
      
    $bool = $subscribed ? 1 : 0;
        
    $i = 1;
    $n = count($products);
    $q = '';
    
	foreach ( $products as $prod_id => $prod_name ) {
		
		if ($i === $n) {
			$q .= "meta_value LIKE '%\"id\";i:$prod_id%')";		
		} else {
			$q .= "meta_value LIKE '%\"id\";i:$prod_id%' OR";
		}
		
		$i++;
	}
    
    return $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_meta' AND meta_value LIKE '%\"edd_send_prod_updates\";b:$bool%' AND ($q", OBJECT_K );
    
}