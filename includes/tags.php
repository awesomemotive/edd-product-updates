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
	edd_add_email_tag( 'updated_products', __( 'Display a plain list of updated products', 'edd-pup' ), 'none' == edd_pup_template() ? 'edd_pup_products_tag_plain' : 'edd_pup_products_tag' );
	edd_add_email_tag( 'updated_products_links', __( 'Display a list of updated products with links', 'edd-pup' ), 'none' == edd_pup_template() ? 'edd_pup_products_links_tag_plain' : 'edd_pup_products_links_tag' );
	edd_add_email_tag( 'unsubscribe_link', __( 'Outputs an unsubscribe link users can click to opt-out of future product update emails', 'edd-pup' ), 'none' == edd_pup_template() ? 'edd_pup_unsub_tag_plain' : 'edd_pup_unsub_tag' );
}
add_action( 'edd_add_email_tags', 'edd_pup_email_tags' );

/**
 * Filters custom email tags from EDD Product Updates to display placeholders
 * when clicking the "Preview" button on the edit email page
 * 
 * @access public
 * @param string $message
 * @return $message - HTML message with template tags replaced
 */
function edd_pup_preview_tags( $message ) {
	
	$email_id = get_transient( 'edd_pup_preview_email_'. get_current_user_id() );

	if ( false !== $email_id ) {
		$updated_links    = 'none' == edd_pup_template() ? edd_pup_products_links_tag_plain( '', $email_id ) : preg_replace( '/<a(.*?)href="(.*?)"(.*?)>/', '<a href="#">', edd_pup_products_links_tag( '', $email_id ) );
		$updated_products = 'none' == edd_pup_template() ? edd_pup_products_tag_plain( '', $email_id ) : edd_pup_products_tag( '', $email_id );
		
	} else {
		
		// Outputs previews for tags when an $email_id is unavailable
		
		if ( 'none' == edd_pup_template() ) {
			
			// Sample plaintext for {updated_products_links} tag
			$updated_links = __( 'Sample Product Title A - SKU: 2200 (filename1.jpg: http://www.example.com/download_link/?file=filename1, filename2.jpg: http://www.example.com/download_link/?file=filename2 ) - Product notes, Sample Product Title C - SKU: 2201 (filename1.jpg: http://www.example.com/download_link/?file=filename1), Sample Bundle Product C - SKU: 2202 - Sample Bundle Product 1 (bundle1.jpg: http://www.example.com/download_link/?file=bundle1); Sample Bundle Product 2 (bundle2.jpg: http://www.example.com/download_link/?file=bundle2)', 'edd-pup' );
			
			// Sample plaintext for {updated_products} tag
			$updated_products = __( 'Sample Product Title A, Sample Product Title B, Sample Bundle Product Title (Sample Bundle Product 1, Sample Bundle Product 2, Sample Bundle Product 3), Sample Product Title C', 'edd-pup');
			
		} else {
			// Sample HTML for {updated_products_links} tag
			$updated_links = '<ul><li>'. __( 'Sample Product Title A', 'edd-pup' ) .' –</li>';
			$updated_links .= '<ul><li><a href="#">'. __( 'sample_product_a_file1.pdf', 'edd-pup' ) .'</a></li>';
			$updated_links .= '<li><a href="#">'. __( 'sample_product_a_file2.pdf', 'edd-pup' ) .'</a></li></ul>';
			$updated_links .= '<li>'. __( 'Sample Product Title B', 'edd-pup' ) .' –</li>';
			$updated_links .= '<ul><li><a href="#">'. __( 'sample_product_b_file1.pdf', 'edd-pup' ) .'</a></li></ul>';
			$updated_links .= '<li>'. __( 'Sample Bundle Product Title', 'edd-pup' ) .' –</li>';
			$updated_links .= '<ul><li><em>'. __( 'Sample Bundle Product 1', 'edd-pup' ) .' –</em></li>';
			$updated_links .= '<ul><li><a href="#">'. __( 'sample_bundle_product1.pdf', 'edd-pup' ) .'</a></li></ul>';	
			$updated_links .= '<li><em>'. __( 'Sample Bundle Product 2', 'edd-pup' ) .' –</em></li>';
			$updated_links .= '<ul><li><a href="#">'. __( 'sample_bundle_product2.pdf', 'edd-pup' ) .'</a></li></ul>';	
			$updated_links .= '<li><em>'. __( 'Sample Bundle Product 3', 'edd-pup' ) .' –</em></li>';
			$updated_links .= '<ul><li><a href="#">'. __( 'sample_bundle_product2.pdf', 'edd-pup' ) .'</a></li></ul></ul></ul>';
	
			// Sample HTML for {updated_products} tag
			$updated_products = '<ul><li>'. __( 'Sample Product Title A', 'edd-pup' ) .'</li>';
			$updated_products .= '<li>'. __( 'Sample Product Title B', 'edd-pup' ) .'</li>';
			$updated_products .= '<li>'. __( 'Sample Bundle Product Title', 'edd-pup' ) .'</li>';
			$updated_products .= '<ul><li><em>'. __( 'Sample Bundle Product 1', 'edd-pup' ) .'</em></li>';	
			$updated_products .= '<li><em>'. __( 'Sample Bundle Product 2', 'edd-pup' ) .'</em></li>';
			$updated_products .= '<li><em>'. __( 'Sample Bundle Product 3', 'edd-pup' ) .'</em></li>';
			$updated_products .= '</ul><li>'. __( 'Sample Product Title C', 'edd-pup' ) .'</li></ul>';			
		}
	}
	
	// Sample HTML for {unsubscribe_link} tag
	$unsub_link_params = array(
		'order_id'     => '0123',
		'email'        => 'sample@email.com',
		'purchase_key' => strtolower( md5( uniqid() ) ),
		'edd_action'   => 'prod_update_unsub',
		'preview'	   => 1
	);
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );
	$unsubscribe = 'none' == edd_pup_template() ? $unsublink : '<a href="'.$unsublink.'">'. apply_filters( 'edd_pup_unsubscribe_text', __( 'Unsubscribe', 'edd-pup' ) ) .'</a>';
	
	// Replace tags with sample HTML
	$message = str_replace( '{updated_products_links}', $updated_links, $message );
	$message = str_replace( '{updated_products}', $updated_products, $message );
	$message = str_replace( '{unsubscribe_link}', $unsubscribe, $message );
	
	// Add div for CSS styling of custom tags
	$message = '<div id="edd-pup-popup-preview">' . $message . '</div>';

	return $message;
}
add_filter('edd_email_preview_template_tags', 'edd_pup_preview_tags', 999);

/**
 * Email template tag: updated_products
 * A list without links of the products that have been updated
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_products_tag( $payment_id, $email = null ) {

	
	// Used to generate accurate tag outputs for preview and test emails
	if ( isset( $email ) && absint( $email ) != 0 ) {
		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );		
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );

		foreach ( $updated_products as $id => $name ) {
		
			$customer_updates[$id] = array( 'id' => $id, 'name' => $name);
		
		}

		
	} else {
	
		$email = get_transient( 'edd_pup_sending_email_'. get_current_user_id() );
		$customer_updates = edd_pup_get_customer_updates( $payment_id, $email );
		$customer_updates = is_array( $customer_updates ) ? $customer_updates : array( $customer_updates );
	
	}
	
	$filters = get_post_meta( $email, '_edd_pup_filters', true );
	$productlist = '<ul>';

	foreach ( $customer_updates as $product ) {
		

		if ( edd_is_bundled_product( $product['id'] ) ) {
				
			$bundled_products = edd_get_bundled_products( $product['id'] );
			
			$productlist .= '<li>'. $product['name'] .'</li>';
			$productlist .= '<ul>';		
			
			foreach ( $bundled_products as $bundle_item ) {
			
				if ( isset( $customer_updates[ $bundle_item ] ) ) {
	
					$productlist .= '<li><em>'. get_the_title( $bundle_item ) .'</em></li>';
				}
			}
			
			$productlist .= '</ul>';
			
		} else if ( $filters['bundle_2'] ) {
			
			continue;	
			
		} else {
			
			$productlist .= '<li>'. $product['name'] .'</li>';
		}
	}

	$productlist .= '</ul>';

	return $productlist;
}

/**
 * Email template tag: updated_products (for plaintext emails only)
 * A list without links of the products that have been updated
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_products_tag_plain( $payment_id, $email = null ) {

	
	// Used to generate accurate tag outputs for preview and test emails
	if ( isset( $email ) && absint( $email ) != 0 ) {
		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );		
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );

		foreach ( $updated_products as $id => $name ) {
		
			$customer_updates[$id] = array( 'id' => $id, 'name' => $name);
		
		}

		
	} else {
	
		$email = get_transient( 'edd_pup_sending_email_'. get_current_user_id() );
		$customer_updates = edd_pup_get_customer_updates( $payment_id, $email );
		$customer_updates = is_array( $customer_updates ) ? $customer_updates : array( $customer_updates );
	
	}
	
	// Start generating the list itself
	$filters = get_post_meta( $email, '_edd_pup_filters', true );
	$productlist = '';
	$i = 1;
	
	foreach ( $customer_updates as $product ) {

		if ( edd_is_bundled_product( $product['id'] ) ) {
				
			$bundled_products = edd_get_bundled_products( $product['id'] );
			
			$productlist .= $i == 1 ? $product['name']. ' (' : ', '. $product['name']. ' (';	
			$j = 1;
			
			foreach ( $bundled_products as $bundle_item ) {
			
				if ( isset( $customer_updates[ $bundle_item ] ) ) {
					
					if ( $j == 1 ) {
						$productlist .= get_the_title( $bundle_item );
					} else {
						$productlist .= ', '. get_the_title( $bundle_item );						
					}
				}
				
				$j++;
			}
			
			$productlist .= ')';
			
		} else if ( $filters['bundle_2'] ) {
			
			continue;	
			
		} else {
			
			if ( $i == 1 ) {
				$productlist .= $product['name'];
			} else {	
				$productlist .= ', '. $product['name'];
			}
		}
		
		$i++;
	}
	
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
function edd_pup_products_links_tag( $payment_id, $email = null ) {	
		
	// Used to generate accurate tag outputs for preview and test emails
	if ( isset( $email ) && absint( $email ) != 0 ) {

		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );
	
		foreach ( $updated_products as $id => $name ) {
		
			$customer_updates[$id] = array( 'id' => $id, 'name' => $name);
		
		}
		
	} else {
	
		$email = get_transient( 'edd_pup_sending_email_'. get_current_user_id() );
		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );
		$customer_updates = edd_pup_get_customer_updates( $payment_id, $email );
		$customer_updates = is_array( $customer_updates ) ? $customer_updates : array( $customer_updates );
	}
	
	$filters = get_post_meta( $email, '_edd_pup_filters', true );

	if ( $customer_updates ) {
	
		$show_names    = apply_filters( 'edd_pup_email_show_names', true );
		$payment_data  = edd_get_payment_meta( $payment_id );
		// Set email to most recent email if it's been changed from initial email
		if ( isset( $payment_data['user_info']['email'] ) && $payment_data['user_info']['email'] != $payment_data['email'] ) {
			$payment_data['email'] = $payment_data['user_info']['email'];
		}
		$download_list = '<ul>';
		
		foreach ( $customer_updates as $item ) {
				
				$bundle = edd_is_bundled_product( $item['id'] );
				
				// Show only bundled products for bundle only customers
				if ( $filters['bundle_2'] && !$bundle ) {
					continue;
				}
				
				if ( edd_use_skus() ) {
					$sku = edd_get_download_sku( $item['id'] );
				}

				$price_id = edd_get_cart_item_price_id( $item );

				if ( $show_names ) {
					
					$title = $item['name'];

					if ( ! empty( $sku ) ) {
						$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'edd' ) . ': ' . $sku;
					}

					/*
					// if ( $price_id !== false ) {
					//	$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id );
					// }
					*/

					$download_list .= '<li>' . apply_filters( 'edd_pup_email_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
					$download_list .= '<ul>';
				}

				$files = edd_get_download_files( $item['id'], $price_id );

				if ( $files ) {
				
					foreach ( $files as $filekey => &$file ) {
						$download_list .= '<li>';
						$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $item['id'], $price_id );
						$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
						$download_list .= '</li>';
					}
				}
				
				if ( $bundle ) {

					$bundled_products = edd_get_bundled_products( $item['id'] );
											
					foreach ( $bundled_products as $bundle_item ) {
					
						if ( $filters['bundle_1'] == 'all' || isset( $updated_products[ $bundle_item ] ) ) {

							$download_list .= '<li class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></li>';

							$bundlefiles = edd_get_download_files( $bundle_item );

							foreach ( $bundlefiles as $bundlefilekey => $bundlefile ) {
								$download_list .= '<li>';
								$bundlefile_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $bundlefilekey, $bundle_item, $price_id );
								$download_list .= '<a href="' . esc_url( $bundlefile_url ) . '">' . $bundlefile['name'] . '</a>';
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
	
		$download_list .= '</ul>';

		return $download_list;
	}
	
}

/**
 * Email template tag: updated_products_links
 * A list of updated products with download links included
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_products_links_tag_plain( $payment_id, $email = null ) {	
	
	// Used to generate accurate tag outputs for preview and test emails
	if ( isset( $email ) && absint( $email ) != 0 ) {
		
		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );

		foreach ( $updated_products as $id => $name ) {
		
			$customer_updates[$id] = array( 'id' => $id, 'name' => $name);
		
		}
		
	} else {
	
		$email = get_transient( 'edd_pup_sending_email_'. get_current_user_id() );
		$updated_products = get_post_meta( $email, '_edd_pup_updated_products', true );
		$updated_products = is_array( $updated_products ) ? $updated_products : array ( $updated_products );
		$customer_updates = edd_pup_get_customer_updates( $payment_id, $email );
		$customer_updates = is_array( $customer_updates ) ? $customer_updates : array( $customer_updates );
	}
	
	$filters = get_post_meta( $email, '_edd_pup_filters', true );
	
	if ( $customer_updates ) {
	
		$show_names    = apply_filters( 'edd_pup_email_show_names', true );
		$payment_data  = edd_get_payment_meta( $payment_id );
		// Set email to most recent email if it's been changed from initial email
		if ( isset( $payment_data['user_info']['email'] ) && $payment_data['user_info']['email'] != $payment_data['email'] ) {
			$payment_data['email'] = $payment_data['user_info']['email'];
		}

		// Used for detecting when to place commas
		$c = 1;
		$download_list = '';
		
		foreach ( $customer_updates as $item ) {
			
			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item['id'] );
			}

			$price_id = edd_get_cart_item_price_id( $item );

			if ( $show_names ) {
				
				$title = $c == 1 ? $item['name'] : ', '. $item['name'];

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'edd' ) . ': ' . $sku;
				}

				$download_list .= apply_filters( 'edd_pup_email_products_link_title_plain', $title, $item, $price_id, $payment_id );
			}

			$files = edd_get_download_files( $item['id'], $price_id );

			if ( $files ) {
				// $f used for detecting when to place commas					
				$f = 1;
				$download_list .= ' (';
			
				foreach ( $files as $filekey => &$file ) {
					$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $item['id'], $price_id );
					$download_list .= $f == 1 ? edd_get_file_name( $file ).': '. esc_url( $file_url ) : ', '. edd_get_file_name( $file ).': '. esc_url( $file_url );
					$f++;
				}
				
				$download_list .= ')';
			}
			
			if ( edd_is_bundled_product( $item['id'] ) ) {
				
				$b = 1;
				$bundled_products = edd_get_bundled_products( $item['id'] );
				$download_list .= "&nbsp;&ndash;&nbsp;";
				
				foreach ( $bundled_products as $bundle_item ) {
					 
					if ( $filters['bundle_1'] == 'all' || isset( $updated_products[ $bundle_item ] ) ) {

						$download_list .= $b == 1 ? get_the_title( $bundle_item ) : '; '. get_the_title( $bundle_item );

						$fb = 1;
						$bundlefiles = edd_get_download_files( $bundle_item );
						$download_list .= ' (';
						
						foreach ( $bundlefiles as $bundlefilekey => $bundlefile ) {
							$bundlefile_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $bundlefilekey, $bundle_item, $price_id );
							$download_list .= $fb == 1 ? $bundlefile['name'].': '. esc_url( $bundlefile_url ) : ', '. $bundlefile['name'].': '. esc_url( $bundlefile_url );
							
							$fb++;
						}
						
						$download_list .= ')';
					}
					
					$b++;
				}
			}

			if ( '' != edd_get_product_notes( $item['id'] ) ) {
				$download_list .= ' &ndash; ' . edd_get_product_notes( $item['id'] );
			}
			
			$c++;
		}
		
		return $download_list;
	}
	
}

/**
 * Email template tag: unsubscribe
 * An unsubscribe link for customers to opt-out of future product updates
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_unsub_tag( $payment_id ) {
	
	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );
	$unsub_link_params = array(
		'order_id'     => $payment_id,
		'email'        => rawurlencode( $purchase_data['user_info']['email'] ),
		'purchase_key' => isset( $purchase_data['key'] ) ? $purchase_data['key'] : edd_get_payment_key( $payment_id ),
		'edd_action'   => 'prod_update_unsub'
	);
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );

	return sprintf( '<a href="%1$s">%2$s</a>', $unsublink, apply_filters( 'edd_pup_unsubscribe_text', __( 'Unsubscribe', 'edd-pup' ) ) );
}

/**
 * Email template tag: unsubscribe (for plaintext emails only)
 * An unsubscribe link for customers to opt-out of future product updates
 * 
 * @access public
 * @param mixed $payment_id
 * @return void
 */
function edd_pup_unsub_tag_plain( $payment_id ) {
	
	$purchase_data = get_post_meta( $payment_id, '_edd_payment_meta', true );
	$unsub_link_params = array(
		'order_id'     => $payment_id,
		'email'        => rawurlencode( $purchase_data['user_info']['email'] ),
		'purchase_key' => isset( $purchase_data['key'] ) ? $purchase_data['key'] : edd_get_payment_key( $payment_id ),
		'edd_action'   => 'prod_update_unsub'
	);

	return add_query_arg( $unsub_link_params, ''.home_url() );
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
		$email    = sanitize_email( rawurldecode( $_GET['email'] ) );
		$key      = sanitize_key( $_GET['purchase_key'] );
		$preview  = isset( $_GET['preview'] ) ? boolval( $_GET['preview'] ) : false;

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
		
		if ( $preview ) {
			
			edd_pup_unsub_message( $order_id, $key, $email, $action, true );
		
		} else if ( $payments ) {
			
			edd_pup_unsub_page( $order_id, $key, $email, $action );

		} else {
			wp_die( __( 'The email address or the purchase you requested to be unsubscribed from was not found.', 'edd-pup' ) , __( 'Email Not Found', 'edd-pup' ) );
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
function edd_pup_unsub_message( $payment_id, $purchase_key, $email, $action, $preview = false ){

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
	
	// Add preview parameter if this page is being viewed from a link in a preview or test email
	if ( $preview ) {
		$resub_link_params['preview'] = 1;
		$unsub_link_params['preview'] = 1;
	}
	
	$resublink = add_query_arg( $resub_link_params, ''.home_url() );
	$unsublink = add_query_arg( $unsub_link_params, ''.home_url() );

	if ( $action == 'prod_update_unsub' ){
		
		$title = __( 'Unsubscribe Successful', 'edd-pup' );
		ob_start(); ?>
		
		<h1><?php _e( 'Unsubscribed', 'edd-pup' );?></h1>
		<p><strong><?php _e(' You have been successfully removed from the list.', 'edd-pup' ); ?></strong></p>
		<p><?php printf( __( 'Your email <strong>%s</strong> has been successfully removed from the list for purchase #%s.', 'edd-pup' ), $email, $payment_id ); ?></p>
		<p><em><?php _e( 'Did you unsubscribe on accident?', 'edd-pup' ); ?> <a href="<?php echo $resublink;?>"><?php _e( 'Click here to resubscribe.', 'edd-pup' ); ?></a></em></p>
		<?php
			
	} else if ($action == 'prod_update_resub'){

		$title = __( 'Resubscribe Successful', 'edd-pup' );
		ob_start(); ?>
		
		<h1><?php _e( 'Resubscribed', 'edd-pup' );?></h1>
		<p><strong><?php _e(' You have successfully re-subscribed to the list.', 'edd-pup' ); ?></strong></p>
		<p><?php printf( __( 'You have successfully re-subscribed <strong>%s</strong> to the list for purchase #%s.', 'edd-pup' ), $email, $payment_id ); ?></p>
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