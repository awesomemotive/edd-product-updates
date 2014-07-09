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
function edd_pup_order_history($payment_id){

	$payment_meta = edd_get_payment_meta($payment_id);
	$sendupdates = isset( $payment_meta['edd_send_prod_updates'] ) ? $payment_meta['edd_send_prod_updates'] : true ;

	ob_start();
	?>
			<div class="edd-admin-box-inside edd-send-updates">
				<p>
					<span class="label" title="<?php _e( 'When checked, customer will receive product update emails.', 'edd_pup' ); ?>"><i data-code="f463" class="dashicons dashicons-update"></i></span>&nbsp;
					<input type="checkbox" name="edd-send-product-updates" id="edd_send_product_updates" value="1"<?php checked( true, $sendupdates, true ); ?>/>
					<label class="description" for="edd_send_product_updates"><?php _e( 'Send Product Updates', 'edd_pup' ); ?></label>
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
 * Save unsubscribe field when modified on payment history page
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_updated_edited_purchase( $payment_id ) {
 
    // get the payment meta
    $payment_meta = edd_get_payment_meta( $payment_id );
 
    // update our phone number
    $payment_meta['edd_send_prod_updates'] = isset( $_POST['edd-send-product-updates'] ) ? true : false;
 
    // update the payment meta with the new array 
    update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'edd_pup_updated_edited_purchase' );

/**
 * Returns boolean whether or not customer is subscribed for updates
 * 
 * @access public
 * @param mixed $payment_id
 * @return bool $sendupdates 
 */
function edd_pup_user_send_updates($payment_id){
    
    $payment_meta = edd_get_payment_meta( $payment_id );
    
	$sendupdates = isset( $payment_meta['edd_send_prod_updates'] ) ? $payment_meta['edd_send_prod_updates'] : true ;

	return $sendupdates;
}