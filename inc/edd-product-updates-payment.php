<?php
/*
Plugin Name: Easy Digital Downloads - Product Update Payments
Plugin URL: http://easydigitaldownloads.com/extension/
Description: Send product update emails in batch
Version: 0.1
Author: Evan Luzi
Author URI: http://evanluzi.com
Contributors: Evan Luzi
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_prod_update_order_history($payment_id){

	$payment_meta = edd_get_payment_meta($payment_id);
	$sendupdates = isset( $payment_meta['edd_send_prod_updates'] ) ? $payment_meta['edd_send_prod_updates'] : true ;

	ob_start();
	?>
			<div class="edd-admin-box-inside edd-send-updates">
				<p>
					<span class="label" title="<?php _e( 'Grants the customer unlimited file downloads for this purchase, regardless of other limits set.', 'edd' ); ?>"><i data-code="f463" class="dashicons dashicons-update"></i></span>&nbsp;
					<input type="checkbox" name="edd-send-product-updates" id="edd_send_product_updates" value="1"<?php checked( true, $sendupdates, true ); ?>/>
					<label class="description" for="edd_send_product_updates"><?php _e( 'Send Product Updates', 'edd' ); ?></label>
				</p>
			</div>
			<?php

	echo ob_get_clean();
}
add_action('edd_view_order_details_update_inner','edd_prod_update_order_history');


/**
 * Store the custom field data into EDD's payment meta
 */
function edd_prod_update_store_field( $payment_meta ) {

	$payment_meta['edd_send_prod_updates'] = isset( $_POST['edd-send-product-updates'] ) ? true : false ;
    //$payment_meta['edd_send_prod_updates'] = true;
    
    return $payment_meta;
}
add_filter( 'edd_payment_meta', 'edd_prod_update_store_field');

/**
 * Save the phone field when it's modified via view order details
 */
function sumobi_edd_updated_edited_purchase( $payment_id ) {
 
    // get the payment meta
    $payment_meta = edd_get_payment_meta( $payment_id );
 
    // update our phone number
    $payment_meta['edd_send_prod_updates'] = isset( $_POST['edd-send-product-updates'] ) ? true : false;
 
    // update the payment meta with the new array 
    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'sumobi_edd_updated_edited_purchase' );

/**
 * edd_prod_updates_user_send_updates function.
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_prod_updates_user_send_updates($payment_id){
    
    $payment_meta = edd_get_payment_meta( $payment_id );
    
	$sendupdates = isset( $payment_meta['edd_send_prod_updates'] ) ? $payment_meta['edd_send_prod_updates'] : true ;

	return $sendupdates;
}