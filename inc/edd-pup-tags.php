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
 * Register custom email tags {updated_products}, {updated_products_links},
 * and {unsubscribe} for use in product update emails
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_email_tags( $payment_id ) {
	edd_add_email_tag( 'updated_products', __( 'Display a list of updated products without links', 'edd-pup' ), 'edd_pup_products_tag' );
	edd_add_email_tag( 'updated_products_links', __( 'Display a list of updated products with links', 'edd-pup' ), 'edd_pup_products_links_tag' );
	edd_add_email_tag( 'unsubscribe_link', __( 'Output an unsubscribe link so users no longer receive product update emails', 'edd-pup' ), 'edd_pup_unsub_tag' );
}
add_action( 'edd_add_email_tags', 'edd_pup_email_tags' );


/**
 * Email template tag: updated_products
 * A list without links of the products that have been updated
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_products_tag($payment_id) {

	global $edd_options;

	$updated_products = $edd_options['prod_updates_products'];	
	$customer_updates = edd_pup_eligible_updates( $payment_id, $updated_products );
	$productlist = '<ul>';

	foreach ($customer_updates as $product) {

		if ( edd_is_bundled_product( $product['id'] ) ) {
		
			$bundled_products = edd_get_bundled_products( $product['id'] );
			
			$productlist .= '<li>'. $product['name'] .'</li>';
			$productlist .= '<ul>';		
			
			foreach ( $bundled_products as $bundle_item ) {
				$productlist .= '<li><em>'. get_the_title( $bundle_item ) .'</em></li>';
			}
			
			$productlist .= '</ul>';
			
		} else {
			$productlist .= '<li>'. get_the_title( $product['id'] ) .'</li>';
			//$productlist .= '<li>'. $product['name'] .'</li>';
		}
	}

	$productlist .= '</ul>';

	return $productlist;
}

/**
 * Email template tag: updated_products_links
 * A list of updated products with download links included
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_products_links_tag($payment_id) {

	global $edd_options;

	$updated_products = $edd_options['prod_updates_products'];
	$customer_updates = edd_pup_eligible_updates( $payment_id, $updated_products, true );

	$payment_data  = edd_get_payment_meta( $payment_id );
	$download_list = '<ul>';
	//$cart_items    = edd_get_payment_meta_cart_details( $payment_id );
	$email         = edd_get_payment_user_email( $payment_id );
	
	if ( $customer_updates ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		
		foreach ( $customer_updates as $item ) {
				
				if ( edd_use_skus() ) {
					$sku = edd_get_download_sku( $item['id'] );
				}

				$price_id = edd_get_cart_item_price_id( $item );

				if ( $show_names ) {
					
					$title = get_the_title( $item['id'] );
					//$title = $item['name'];

					if ( ! empty( $sku ) ) {
						$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'edd' ) . ': ' . $sku;
					}

					if ( $price_id !== false ) {
						$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id );
					}

					$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
					$download_list .= '<ul>';
				}

				$files = edd_get_download_files( $item['id'], $price_id );

				if ( $files ) {
					foreach ( $files as $filekey => &$file ) {
						$download_list .= '<li>';
						$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
						$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
						$download_list .= '</li>';
					}
				}
				elseif ( edd_is_bundled_product( $item['id'] ) ) {

					$bundled_products = edd_get_bundled_products( $item['id'] );
					
					foreach ( $bundled_products as $bundle_item ) {
					
						if (array_key_exists($bundle_item, $customer_updates)) {

							$download_list .= '<li class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></li>';

							$files = edd_get_download_files( $bundle_item );

							foreach ( $files as $filekey => $file ) {
								$download_list .= '<li>';
								$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
								$download_list .= '<a href="' . esc_url( $file_url ) . '">' . $file['name'] . '</a>';
								$download_list .= '</li>';
							}
						}
					}
				}

				if ( $show_names ) {
					$download_list .= '</ul>';
				}

				if ( '' != edd_get_product_notes( $item['id'] ) ) {
					$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
				}


				if ( $show_names ) {
					$download_list .= '</li>';
				}
			}
		}
	
	$download_list .= '</ul>';
    
	return $download_list;
}

/**
 * Email template tag: unsubscribe
 * An unsubscribe link for customers to opt-out of future product updates
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_unsub_tag($payment_id) {

	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );
	$unsub_link_params = array(
		'order_id'  => $payment_id,
		'email'        => rawurlencode( $purchase_data['user_info']['email'] ),
		'purchase_key' => $purchase_data['key'],
		'edd_action' => 'prod_update_unsub'
	);
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );
	$unsubscribe = printf( '<a href="%1$s">%2$s</a>', $unsublink, __( 'Unsubscribe', 'edd-pup' ) );

	return $unsubscribe;
}

/**
 * Check to make sure a customer's payment key and purchase email
 * match after clicking on the unsubscribe link in an email
 * 
 * @access public
 * @return void
 */
function edd_pup_verify_unsub_link() {
	if ( isset( $_GET['order_id'] )  && isset( $_GET['email'] ) && isset( $_GET['purchase_key'] ) && isset( $_GET['edd_action'] ) ) {

		if ( ! ( ($_GET['edd_action'] == 'prod_update_unsub') || ($_GET['edd_action'] == 'prod_update_resub') ) ) {
			return;
		}

		$order_id = absint( $_GET['order_id'] );
		$action   = sanitize_text_field( $_GET['edd_action'] );
		$email    = sanitize_email( $_GET['email'] );
		$key      = sanitize_key( $_GET['purchase_key'] );

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
			edd_pup_unsub_page($order_id, $key, $email, $action);
		} else {
			wp_die( __( 'The email address you requested to be unsubscribed was not found.', 'edd-pup' ) , __( 'Email Not Found', 'edd-pup' ) );
		}
	}
}
add_action( 'init', 'edd_pup_verify_unsub_link');

/**
 * Unsubscribe or resubscribe a customer, update their payment log, and
 * then show them the appropriate message
 * 
 * @access public
 * @param mixed $payment_id
 * @param mixed $purchase_key
 * @param mixed $email
 * @param mixed $action    either prod_update_unsub or prod_update_resub
 * @return void
 */
function edd_pup_unsub_page($payment_id, $purchase_key, $email, $action) {

	$payment_meta = edd_get_payment_meta( $payment_id );

	// Only update payment info if user is currently subscribed for updates
	if ( edd_pup_unsub_status($payment_id) && $action == 'prod_update_unsub' ) {

		// Unsubscribe customer from futurue updates
		$payment_meta['edd_send_prod_updates'] = false;

		// Update the payment meta with the new array
		update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );

		// Update customer log with note about unsubscribing
		edd_insert_payment_note($payment_id, __( 'User unsubscribed from product update emails', 'edd-pup' ) );

	} else if (!edd_pup_unsub_status($payment_id) && $action == 'prod_update_resub' ) {
	
		// Unsubscribe customer from futurue updates
		$payment_meta['edd_send_prod_updates'] = true;

		// Update the payment meta with the new array
		update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );

		// Update customer log with note about resubscribing
		edd_insert_payment_note($payment_id, __( 'User re-subscribed to product update emails', 'edd-pup' ) );
	}

	edd_pup_unsub_message($payment_id, $purchase_key, $email, $action);
}

/**
 * Generate the message shown to customers on unsubscribe/resubscribe
 * 
 * @access public
 * @param mixed $payment_id
 * @param mixed $purchase_key
 * @param mixed $email
 * @param mixed $action    either prod_update_unsub or prod_update_resub
 * @return void
 */
function edd_pup_unsub_message($payment_id, $purchase_key, $email, $action){

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
		$title = __( 'Unsubscribed - You have been successfully removed from the list.', 'edd-pup' );
		ob_start();
?>
		<h1>Thank you</h1>
		<p><?php sprintf( __( 'Your email <strong>%s</strong> has been successfully removed from the list.', 'edd-pup' ), $email ); ?></p>
		<p><em><?php _e( 'Did you unsubscribe on accident?', 'edd-pup' ); ?> <a href="<?php echo $resublink;?>"><?php _e( 'Click here to resubscribe.', 'edd-pup' ); ?></a></em></p>
		<?php
	} else if ($action == 'prod_update_resub'){
			$title = __( 'Resubscribed - You have successfully re-subscribed to the list.', 'edd-pup' );
			ob_start();
?>
		<h1><?php _e( 'Thank you!', 'edd-pup' ); ?></h1>
		<p><?php sprintf( __( 'You have successfully re-subscribed <strong>%s</strong> to the list.', 'edd-pup' ), $email ); ?></p>
		<p><em><a href="<?php echo $unsublink;?>"><?php _e( 'Click here to unsubscribe.', 'edd-pup' ); ?></a></em></p>
		<?php
		}
	wp_die( ob_get_clean(), $title );

}

/**
 * Get whether a customer is subscribed for product updates
 * 
 * @access public
 * @param mixed $payment_id (default: null)
 * @return void
 */
function edd_pup_unsub_status( $payment_id = null ) {

	$status = true;
	$payment_meta = edd_get_payment_meta( $payment_id );

	if ( isset(  $payment_meta['edd_send_prod_updates'] ) && ! is_null( $payment_id ) && ! empty( $payment_id ) ) {

		if ( ! ($payment_meta['edd_send_prod_updates']) ) {
			$status = false;
		}
	}

	return $status;
}