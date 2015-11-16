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
 * Sanitizes posted data from before saving an email
 * 
 * @access public
 * @param mixed $posted
 * @return int email id of saved email
 */
function edd_pup_sanitize_save( $data ) {

	// Convert form data to array	
	if ( isset( $data['form'] ) ) {
		$form = $data['form'];
		$data = array();
		parse_str( $form, $data );
	}
	
	// Sanitize our data
	$data['message'] 	= wp_kses_post( $data['message'] );
	$data['email-id']	= isset( $data['email-id'] ) ? absint( $data['email-id'] ) : 0;
	$data['recipients']	= absint( $data['recipients'] );
	$data['from_name'] 	= esc_attr( sanitize_text_field( $data['from_name'] ) );
	$data['from_email'] = sanitize_email( $data['from_email'] );
	$data['title']		= sanitize_text_field( $data['title'], 'ID:'. $data['email-id'], 'save' );
	$data['subject']	= esc_attr( sanitize_text_field( $data['subject'] ) );
	$data['bundle_1']	= sanitize_text_field( $data['bundle_1'] );
	$data['bundle_2']	= isset( $data['bundle_2'] ) ? 1 : 0;
	
	// Sanitize products array and convert to ID => name format
	if ( isset( $data['products'] ) ) {
		
		foreach ( $data['products'] as $product ) {
			$prodid = absint( $product );
			$products[ absint( $prodid ) ] = get_the_title( absint( $prodid ) );
		}
		
		$data['products'] = $products;
	
	}
	
	return edd_pup_save_email( $data, $data['email-id'] );
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
	$from_email = isset( $data['from_email'] ) ? $data['from_email'] : get_bloginfo('admin_email');
	$subject = apply_filters( 'edd_purchase_subject', ! empty( $data['subject'] )
		? wp_strip_all_tags( $data['subject'], true )
		: __( 'New Product Update', 'edd-pup' ) );
	$products = isset( $data['products'] ) ? $data['products'] : '';
	$filters = array(
		'bundle_1' => $data['bundle_1'],
		'bundle_2' => $data['bundle_2']);
		
	// Remove product_dropdown placeholder from being saved as a product
	if ( isset( $products[0] ) ) {
		unset( $products[0]);
	}
		
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
		
		// Get number of recipients for this email
		$recipients = edd_pup_customer_count( $email_id, $products, true, $filters );
		
		$update_id = wp_update_post( $updateargs );
		update_post_meta ( $email_id, '_edd_pup_from_name', $from_name );
		update_post_meta ( $email_id, '_edd_pup_from_email', $from_email );
		update_post_meta ( $email_id, '_edd_pup_subject', $data['subject'] );
		update_post_meta ( $email_id, '_edd_pup_message', $data['message'] );
		update_post_meta ( $email_id, '_edd_pup_updated_products', $products );
		update_post_meta ( $email_id, '_edd_pup_recipients', $recipients );
		update_post_meta ( $email_id, '_edd_pup_filters', $filters );
			
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
		$recipients = edd_pup_customer_count( $create_id, $products, true, $filters );
		
		// Insert custom meta for newly created post
		if ( 0 != $create_id )	{
			add_post_meta ( $create_id, '_edd_pup_from_name', $from_name, true );
			add_post_meta ( $create_id, '_edd_pup_from_email', $from_email, true );
			add_post_meta ( $create_id, '_edd_pup_subject', $data['subject'], true );
			add_post_meta ( $create_id, '_edd_pup_message', $data['message'], true );
			add_post_meta ( $create_id, '_edd_pup_updated_products', $products, true );
			add_post_meta ( $create_id, '_edd_pup_recipients', $recipients );	
			add_post_meta ( $create_id, '_edd_pup_filters', $filters, true );
		}
		
    	if ( 0 != $create_id) {	
			return $create_id;
		}
	}
}

/**
 * Duplicates an existing email.
 *
 * @since 1.1
 * @param int $post_id the post ID of the email being duplicated
 * @return int|bool the ID of the new email if successful, otherwise returns false on failure
 */
function edd_pup_create_duplicate_email( $post_id = 0 ) {
	
	if ( $post_id == 0 ) {
		return false;
	}
	
	$post = get_post( $post_id );
		
	$new_post = array(
	  'post_content'   => $post->post_content,
	  'post_name'      => '',
	  'post_title'     => $post->post_title .' '. __( 'Copy', 'edd-pup' ),
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
	  'post_excerpt'   => $post->post_excerpt,
	  'comment_status' => 'closed'
	);

	// Create post and get the ID
	$create_id = wp_insert_post( $new_post );
		
	// Insert custom meta for newly created post
	if ( 0 != $create_id )	{
				
		$meta = get_post_custom( $post_id );
	
		add_post_meta ( $create_id, '_edd_pup_from_name', $meta['_edd_pup_from_name'][0], true );
		add_post_meta ( $create_id, '_edd_pup_from_email', $meta['_edd_pup_from_email'][0], true );
		add_post_meta ( $create_id, '_edd_pup_subject', $post->excerpt, true );
		add_post_meta ( $create_id, '_edd_pup_message', $post->post_content, true );
		add_post_meta ( $create_id, '_edd_pup_updated_products', maybe_unserialize( $meta['_edd_pup_updated_products'][0] ), true );
		add_post_meta ( $create_id, '_edd_pup_recipients', $meta['_edd_pup_recipients'][0] );	
		add_post_meta ( $create_id, '_edd_pup_filters', maybe_unserialize( $meta['_edd_pup_filters'][0] ), true );
	}
	
	if ( 0 != $create_id) {	
		return $create_id;
	} else {
		return false;
	}
}

/**
 * Count number of customers who will receive product update emails
 *
 * 
 * @access public
 * @return $customercount (number of customers eligible for product updates)
 */
function edd_pup_customer_count( $email_id = null, $products = null, $subscribed = true, $filters = null ){
		
	if ( empty( $email_id ) && !is_numeric( $email_id ) ) {
		return false;
	}
	
	if ( empty( $products ) ) {
		return 0;
	}

    global $wpdb;
    
    $count = 0;
    $b = $subscribed ? 0 : 1;
    $licensing = edd_get_option( 'edd_pup_license' );
	$products = !empty( $products ) ? $products : get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );
	$filters = isset( $filters ) ? $filters : get_post_meta( $email_id, '_edd_pup_filters', true );
	
	// Filter bundle customers only
	if ( $filters['bundle_2'] ) {
		
		$bundleproducts = array();
		$bundlenum = 0;
		
		foreach ( $products as $id => $name ) {
			if ( edd_is_bundled_product( $id ) ) {
				$bundleproducts[ $id ] = $name;
				$bundlenum++;
			}
		}
		$products = $bundlenum > 0 ? $bundleproducts : $products;
	}
        
    // Active EDD Software Licensing integration
	if ( ( $licensing != false ) && is_plugin_active('edd-software-licensing/edd-software-licenses.php' ) ) {
	
		// Get customers who have a completed payment and are subscribed for updates
		$customers = $wpdb->get_results(
	    	"
	    	SELECT post_id, meta_value
	    	FROM $wpdb->posts, $wpdb->postmeta
	    	WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
	    		AND post_type = 'edd_payment'
	    		AND post_status = 'publish'
	    		AND meta_key = '_edd_payment_meta'
				AND meta_value NOT LIKE '%%\"edd_send_prod_updates\";b:0%%'
			", OBJECT_K);
											
		// Get updated products with EDD software licensing enabled
		$products_imp = implode( ',' , array_keys( $products ) );
		$licenseditems = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_sl_enabled' AND meta_value = 1 AND post_id IN ( $products_imp )", OBJECT_K );
		
		foreach ( $customers as $customer ) {

			$paymentmeta = unserialize( $customer->meta_value );
			$cart_details = is_array( $paymentmeta['cart_details'] ) ? $paymentmeta['cart_details'] : array( $paymentmeta['cart_details'] );

			foreach ( $cart_details as $item ) {
				
				// Skip $item if it is not a product being updated
				if ( !isset( $products[ $item['id'] ] ) ){
					continue;
				}
			
				// Check if they have purchased any non-licensed products which would send them the email anyway
				if ( !isset( $licenseditems[ $item['id'] ] ) && isset( $products[ $item['id'] ] ) ) {
					
					$count++;
					break;
					
				// Finally check to make sure customer has licenses then check that it is valid for that item.						
				} else {
				
					$licenses = edd_pup_get_license_keys( $customer->post_id );
					$enabled  = get_post_status( $licenses[$item['id']]['license_id'] ) == 'publish' ? true : false;
										
					if ( !empty( $licenses ) && $enabled && in_array( edd_software_licensing()->get_license_status( $licenses[$item['id']]['license_id'] ), apply_filters( 'edd_pup_valid_license_statuses', array( 'active', 'inactive' ) ) ) ) {
						
						$count++;
						break;
					}
					
				}
			}
		}

	// Inactive EDD Software Licensing integration
	} else {
	
	    $n = count( $products );
	    $i = 1;
	    $q = '';
	    
		foreach ( $products as $id => $name ) {
			
			if ( is_numeric( $id ) ) {
				$s = strlen( $id );
				$id = absint( $id );
				
				if ( $i === $n ) {
					$q .= "meta_value LIKE '%\"id\";s:$s:\"$id\"%' OR meta_value LIKE '%\"id\";i:$id%' )";
				} else {
					$q .= "meta_value LIKE '%\"id\";s:$s:\"$id\"%' OR meta_value LIKE '%\"id\";i:$id%' OR ";				
				}
	
			}
			$i++;
		}
	
		$customers = $wpdb->get_results(
	    	"
	    	SELECT post_id, meta_value
	    	FROM $wpdb->posts, $wpdb->postmeta
	    	WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
	    		AND post_type = 'edd_payment'
	    		AND post_status = 'publish'
	    		AND meta_key = '_edd_payment_meta'
				AND (meta_value NOT LIKE '%%\"edd_send_prod_updates\";b:$b%%'
					AND ($q )
			", OBJECT );
	
		$count = $wpdb->num_rows;
	}
	
    return $count;
}

/**
 * Returns all customers
 * 
 * @access public
 * @return array (all customers regardless of status)
 */
function edd_pup_get_all_customers(){

	$customers = get_transient( 'edd_pup_all_customers' );
	
	if ( false === $customers ) {
	
		global $wpdb;
		
		$customers = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' AND post_status = 'publish'", ARRAY_A );
		
		set_transient( 'edd_pup_all_customers', $customers, 60 );
	}
		
	return $customers;
}


/**
 * Gets a list of all the downloads and formats them as an array
 * 
 * @access public
 * @return array with download IDs as keys and the name of the download as values
 */
function edd_pup_get_all_downloads(){

	$products = get_transient( 'edd_pup_all_downloads' );
	
	if ( false === $products ) {
		$products = array();
		$downloads = get_posts(	array( 'post_type' => 'download', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		if ( !empty( $downloads ) ) {
		    foreach ( $downloads as $download ) {
		    	
		        $products[ $download->ID ] = $download->post_title;
		
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
 * @param array $licenseditems	array of products that have software licensing enabled
 *
 * @return array/object $customer_updates
 */
function edd_pup_eligible_updates( $payment_id, $updated_products, $object = true, $licenseditems = null, $email_id = 0 ){
	
	if ( empty( $payment_id) || empty( $updated_products ) || $email_id = 0 ) {
		return false;
	}
	
	if ( is_null( $licenseditems ) ) {
		global $wpdb;
		$licenseditems = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_sl_enabled' AND meta_value = 1", OBJECT_K );
	}
	
	$customer_updates = '';
	$licensing = edd_get_option( 'edd_pup_license' );
	$payment_meta = get_post_meta( $payment_id, '_edd_payment_meta', true );
	
	if ( ( $licensing != false ) && is_plugin_active('edd-software-licensing/edd-software-licenses.php' ) ) {
		$licenses = edd_pup_get_license_keys( $payment_id );
	}
	
	foreach ( maybe_unserialize( $payment_meta['cart_details'] ) as $item ){
		
		// Skip $item if it is not a product being updated
		if ( !isset( $updated_products[ $item['id'] ] ) ){
			continue;
		}
		
		// If Software Licensing integration is active and the $item has software licensing enabled
		if ( ( $licensing != false ) && isset( $licenseditems[ $item['id'] ] ) ) {
			
			// If the customer has licenses and the license for this $item is enabled and active
			$enabled  = get_post_status( $licenses[$item['id']]['license_id'] ) == 'publish' ? true : false;
					
			if ( !empty( $licenses ) && $enabled && in_array( edd_software_licensing()->get_license_status( $licenses[$item['id']]['license_id'] ), apply_filters( 'edd_pup_valid_license_statuses', array( 'active', 'inactive' ) ) ) ) {
				// Add the $item as an eligible updates
				$customer_updates[ $item['id'] ] = $object ? $item : $item['name'];
			}
			
		} else {
				// Add the $item as an eligible updates
				$customer_updates[ $item['id'] ] = $object ? $item : $item['name'];
		}
	}
	
	return $customer_updates;
}


/**
 * Gets the updates customers will be sent from the email queue
 * 
 * @access public
 * @param mixed $payment_id
 * @param mixed $email_id
 * @return array of products
 */
function edd_pup_get_customer_updates( $payment_id, $email_id ) {
	
	if ( empty( $payment_id ) || empty( $email_id ) ) {
		return;
	}
	
	global $wpdb;
	$payment_id = absint( $payment_id );
	$email_id = absint( $email_id );
	
	return unserialize( trim( $wpdb->get_var( $wpdb->prepare( "SELECT products FROM $wpdb->edd_pup_queue WHERE email_id = %d AND customer_id = %d", $email_id, $payment_id ) ) ) );
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
	$keys = '';
	$licenses = edd_software_licensing()->get_licenses_of_purchase( $payment_id );
	
	if ( $licenses ) {	
		foreach ( $licenses as $license ){
			$meta = get_post_custom( $license->ID );
			$keys[ $meta['_edd_sl_download_id'][0] ] = array( 'license_id' => $license->ID, 'key' => $meta['_edd_sl_key'][0] );
		}
	}
	
	return $keys;
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

	global $wpdb;
		
	$query = "SELECT COUNT(eddpup_id) FROM $wpdb->edd_pup_queue WHERE sent = 0";
	$total = $wpdb->get_results( $query , ARRAY_A);

	return $total[0]['COUNT(eddpup_id)'];
}


/**
 * Finds which unique emails are in the queue and returns list
 * 
 * @access public
 * @return array $email_list (unique emails inside of the queue)
 */
function edd_pup_queue_emails() {

	$email_list = array();
	global $wpdb;
	
	$query = "SELECT DISTINCT email_id FROM $wpdb->edd_pup_queue WHERE sent = 0";
	
	$emails = $wpdb->get_results( $query , ARRAY_A );
	
	foreach ( $emails as $email ) {
		$email_list[] = $email['email_id'];
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
	
	$email_list = edd_pup_emails_processing();
	
	if ( is_array( $email_list['processing'] ) && in_array( $emailid, $email_list['processing'] ) ) {
		
		$totals = edd_pup_check_queue( $emailid );
		
		if ( $totals['queue'] > 0 ) {
			return true;
		}
		
	} else {
	
		return false;
		
	}
	
}

/**
 * Checks which emails are currently being processed by any and all users
 * 
 * @access public
 * @since 1.0.0
 * @return array array of queued post_id's and processing post_id's
 */
function edd_pup_emails_processing() {
	$args = array(
		'post_type'   => 'edd_pup_email',
		'post_status' => 'pending',
		'numberposts' => -1
	);
	
	$emails = get_posts( $args );
	$queued = array();
	$processing = array();
	
	foreach ( $emails as $email ) {
		
		$sending = get_transient( 'edd_pup_sending_email_'. $email->post_author );
		
		if ( false === $sending || $email->ID != $sending ) {
			$queued[] = $email->ID;
		} else {
			$processing[] = $email->ID;
		}
	}
	
	return array( 'queued' => $queued, 'processing' => $processing );
}

/**
 * Checks the users that are eligible for updates
 * 
 * @access public
 * @param mixed $products (array of products using id => name format. default: null)
 * @param bool $subscribed (whether to query for subscribed - true - or unsubscribed - false - customers. default: true)
 *
 * @return array payment_ids that are subscribed/unsubscribed for updates and have purchashed at least one product being updated.
 */
function edd_pup_user_send_updates( $products = null, $subscribed = true, $limit = null, $offset = null ){
    if ( empty( $products ) ) {
	    return;
    }
    
    global $wpdb;
     
    $limit = !empty( $limit ) ? 'LIMIT '. $limit : '';
    $offset = !empty( $offset ) ? 'OFFSET '. $offset : '';
    $bool = $subscribed ? 0 : 1;
        
    $i = 1;
    $n = count( $products );
    $q = '';
    
	foreach ( $products as $prod_id => $prod_name ) {
		
		$s = strlen( $prod_id );
		
		if ( $i === $n ) {
			$q .= "m.meta_value LIKE '%\"id\";s:$s:\"$prod_id\"%' OR m.meta_value LIKE '%\"id\";i:$prod_id%' )";
		} else {
			$q .= "m.meta_value LIKE '%\"id\";s:$s:\"$prod_id\"%' OR m.meta_value LIKE '%\"id\";i:$prod_id%' OR ";				
		}
		
		$i++;
	}
    
    return $wpdb->get_results(
    	"SELECT m.post_id 
    	FROM $wpdb->postmeta m, $wpdb->posts p
    	WHERE m.meta_key = '_edd_payment_meta'
    		AND m.meta_value NOT LIKE '%\"edd_send_prod_updates\";b:$bool%'
    		AND ($q 
    		AND p.ID = m.post_id
    		AND p.post_status = 'publish'
    		$limit $offset
    	", ARRAY_A );
    
}

/**
 * Removes incompatible email templates from the list of template options in the settings
 * 
 * @access public
 * @since 0.9.5
 * @return void
 */
function edd_pup_get_email_templates() {
	
	$templates = edd_get_email_templates();
	$eddpdfi_email_templates = array(
		'invoice_default',
		'blue_stripe',
		'lines',
		'minimal',
		'traditional',
		'invoice_blue',
		'invoice_green',
		'invoice_orange',
		'invoice_pink',
		'invoice_purple',
		'invoice_red',
		'invoice_yellow'
	);
	
	foreach ( $eddpdfi_email_templates as $pdftemplate ) {
		if ( array_key_exists( $pdftemplate, $templates ) ) {
			unset( $templates[$pdftemplate] );
		}
	}
	
	return $templates;
}

/**
 * Helper function to retrieve template selected for product update emails
 * 
 * @access public
 * @return void
 */
function edd_pup_template(){
	
	$template = edd_get_option( 'edd_pup_template' );
	
	if ( ! isset( $template ) ) {
		$template = 'default';
	}
	
	if ( $template == 'inherit' ) {
		return edd_get_option( 'email_template' );
	} else {
		return $template;
	}
}