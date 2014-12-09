<?php
/**
 * EDD Product Updates AJAX
 *
 * Functions and actions for processing various AJAX requests in
 * the Product Updates plugin – specifically for batch sending of emails,
 * generating previews, and sending test emails from the admin pages.
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
 * Generates HTML for email confirmation via AJAX on send button press
 * 
 * @access public
 * @return void
 * @since 0.9
 */
function edd_pup_email_confirm_html(){
	$form = array();
	parse_str( $_POST['form'], $form );
	parse_str( $_POST['url'], $url );
	
	// Nonce security check
	if ( empty( $form['edd-pup-send-nonce'] ) || ! wp_verify_nonce( $form['edd-pup-send-nonce'], 'edd-pup-confirm-send' ) ) {
		echo 'noncefail';
		die();
	}
	
	// Make sure there are products to be updated
	if ( empty( $form['products'] ) ) {
		echo 'nocheck';
		die();
	}
	
	// Save the email before generating preview
	$email_id = edd_pup_sanitize_save( $_POST );
	
	// If "Send Update Email" was clicked from Add New Email page, trigger browser to refresh to edit page for continued editing
	if ( $url['view'] == 'add_pup_email' ) {
		echo absint( $email_id );
		die();
	}
	
	// Necessary for preview HTML
	set_transient( 'edd_pup_preview_email_'. get_current_user_id(), $email_id, 60 );
	
	$email         = get_post( $email_id );
	$emailmeta     = get_post_custom( $email_id );
	$products  	   = get_post_meta( $email_id, '_edd_pup_updated_products', true );
	$popup_url 	   = add_query_arg( array( 'view' => 'send_pup_ajax', 'id' => $email_id ), admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) );
	$customercount = edd_pup_customer_count( $email_id, $products );
	$email_body    = isset( $email->post_content ) ? stripslashes( $email->post_content ) : 'Cannot retrieve message content';
    $subject       = empty( $emailmeta['_edd_pup_subject'][0] ) ? '(no subject)' : strip_tags( edd_email_preview_template_tags( $emailmeta['_edd_pup_subject'][0] ) );
	
	// Construct templated email HTML
	add_filter('edd_email_template', 'edd_pup_template' );
	
	if ( version_compare( get_option( 'edd_version' ), '2.1' ) >= 0 ) {
		$edd_emails = new EDD_Emails;
		$message = $edd_emails->build_email( edd_email_preview_template_tags( $email_body ) );
	} else {
		$message = edd_apply_email_template( $email_body, null, null );		
	}
	
	// Add max-width for plaintext emails so they don't run off the page
	if ( 'none' == edd_pup_template() ) {
		$message = '<div style="width: 640px;">'. $message .'</div>';
	}
	
	// Save message to metadata for later retrieval
	update_post_meta( $email_id, '_edd_pup_message' ,$message );
	
	ob_start();
	?>
		<!-- Begin send email confirmation message -->
			<h2 id="edd-pup-confirm-title"><strong><?php _e( 'Almost Ready to Send!', 'edd-pup' ); ?></strong></h2>
			<p style="text-align: center;"><?php _e( 'Please carefully check the information below before sending your emails.', 'edd-pup' ); ?></p>
			<div id="edd-pup-confirm-message">
				<div id="edd-pup-confirm-header">
					<h3><?php _e( 'Email Message Preview', 'edd-pup' ); ?></h3>
					<ul>
						<li><strong><?php _e( 'From:', 'edd-pup' ); ?></strong> <?php echo $emailmeta['_edd_pup_from_name'][0];?> (<?php echo $emailmeta['_edd_pup_from_email'][0];?>)</li>
						<li><strong><?php _e( 'Subject:', 'edd-pup' ); ?></strong> <?php echo $subject;?></li>
					</ul>
				</div>
				<?php echo $message ?>
				<div id="edd-pup-confirm-footer">
					<h3><?php _e( 'Additional Information', 'edd-pup' ); ?></h3>
					<ul>
						<li><strong><?php _e( 'Updated Products:', 'edd-pup' ); ?></strong></li>
							<ul id="edd-pup-confirm-products">
							<?php foreach ( $products as $product_id => $product ):?>
								<li data-id="<?php echo $product_id; ?>"><?php echo $product; ?></li>
							<?php endforeach; ?>
							</ul>
						<li><strong><?php _e( 'Recipients:', 'edd-pup' ); ?></strong> <?php printf( _n( '1 customer will receive this email and have their downloads reset', '%s customers will receive this email and have their downloads reset', $customercount, 'edd-pup' ), number_format( $customercount ) ); ?></li>
					</ul>
					<a href="<?php echo $popup_url; ?>" id="prod-updates-email-ajax" class="button-primary button" title="<?php _e( 'Confirm and Send Emails', 'edd-pup' ); ?>" onclick="window.open(this.href,'targetWindow', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=480');return false;"><?php _e( 'Confirm and Send Emails', 'edd-pup' ); ?></a>
					<button class="closebutton button-secondary"><?php _e( 'Close without sending', 'edd-pup' ); ?></button>
				</div>
			</div>
		<!-- End send email confirmation message -->
		<script type="text/javascript">
			jQuery('.postbox .recipient-count').text("<?php echo number_format( $customercount );?>");
			jQuery('.postbox .recipient-input').val(<?php echo $customercount;?>);
		</script>
	<?php
	echo ob_get_clean();
	
	die();
}
add_action( 'wp_ajax_edd_pup_confirm_ajax', 'edd_pup_email_confirm_html' );

/**
 * Generates HTML for preview of email on edit email screen
 * 
 * @access public
 * @return void
 */
function edd_pup_ajax_preview() {
	
	// Nonce security check
	if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'edd-pup-preview-email' ) ) {
		_e( 'Nonce failure: error generating preview.', 'edd-pup' );
		die();
	}
	
	// Save the email before generating a preview
	$email_id = edd_pup_sanitize_save( $_POST );
	
	// Instruct browser to redirect for continued editing of email if triggered on Add New Email page
	parse_str( $_POST['url'], $url );
	if ( $url['view'] == 'add_pup_email' ) {
		echo absint( $email_id );
		die();
	}
	
	// Necessary for preview HTML
	set_transient( 'edd_pup_preview_email_'.get_current_user_id(), $email_id, 60 );
	
	if ( 0 != $email_id ){
	
		$email = get_post( $email_id );
		
		// Use $template_name = apply_filters( 'edd_email_template', $template_name, $payment_id );
		add_filter('edd_email_template', 'edd_pup_template' );
	
		if ( version_compare( get_option( 'edd_version' ), '2.1' ) >= 0 ) {
			$edd_emails = new EDD_Emails;
			$preview = $edd_emails->build_email( edd_email_preview_template_tags( $email->post_content ) );
		} else {
			$preview = edd_apply_email_template( $email->post_content, null, null );		
		}
		
		// Add max-width for plaintext emails so they don't run off the page
		if ( 'none' == edd_pup_template() ) {
			$preview = '<div style="max-width: 640px;">'. $preview .'</div>';
		}
		
		echo $preview;
		
	} else {
	
		_e('There was an error generating a preview.', 'edd-pup');
	}
	
	die();
}
add_action( 'wp_ajax_edd_pup_ajax_preview', 'edd_pup_ajax_preview' );

/**
 * Builds the email queue and stores it in the edd_pup_queue db table
 * 
 * @access public
 * @param mixed $data
 * @return $count (the number of emails logged in the queue to be sent)
 */
function edd_pup_ajax_start(){
	
	// Nonce security check
	if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_pup_ajax_start' ) ) {
		echo 'noncefail';
		exit;
	}
	
    $restart    = edd_pup_is_ajax_restart( $_POST['email_id'] );
    $recipients = get_post_meta( $_POST['email_id'], '_edd_pup_recipients', TRUE );
    $userid = get_current_user_id();
    $usertransient = get_transient( 'edd_pup_sending_email_'. $userid );
    
    // Check if user is already sending another email when attempting to send this one
    if ( false !== $usertransient && $usertransient != $_POST['email_id'] ) {
	 	echo 'usersendfail';
	 	exit;
    }
    
    // Check whether the email is restarting from a previous send or via the pause button
    if ( false != $restart && is_array( $restart ) && empty( $_POST['status'] ) && ( $restart['total'] == $recipients ) ) {
				
		set_transient( 'edd_pup_sending_email_'. $userid, $_POST['email_id'] );
		$restart['status'] = 'restart';
		   
	    echo json_encode($restart);
	    exit;
	    
    } else {
	    
		// Check whether the email was paused by the user when building queue and then resumed on the same popup.
		if ( is_array( $restart ) && $restart['total'] != $recipients ) {
			
			$processed = $restart['total'];
			
		} else if ( isset( $_POST['processed'] ) ) {
			
			$processed = absint( $_POST['processed'] );
			
		} else {
			
			$processed = 0;
		}
		
		global $wpdb;
		$i = 1;
		$count = 0;
		$limit = 1000;
		$total = isset( $_POST['total'] ) ? absint( $_POST['total'] ) : $recipients;
		$email_id  = intval( $_POST['email_id'] );
		$products  = get_post_meta( $email_id, '_edd_pup_updated_products', true );
		$customers = edd_pup_user_send_updates( $products, true, $limit, $processed );
		$licenseditems = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_sl_enabled' AND meta_value = 1", OBJECT_K );

		// Set email ID transient
		set_transient( 'edd_pup_sending_email_'. $userid, $email_id, 60);
		
		// Do some one-time operations at beginning of AJAX loop
		if ( $_POST['iteration'] == 0 ) {
			
			// Update email status as in queue
			wp_update_post( array( 'ID' => $email_id, 'post_status' => 'pending' ) );
			
			// Update email with info on EDD Software Licensing Integration
			global $edd_options;
			update_post_meta( $email_id, '_edd_pup_licensing_status', isset( $edd_options['edd_pup_license'] ) ? 'active' : 'inactive' );
			
		}
		
		// Start building queue
		foreach ( $customers as $customer ){
				
			// Check what products customers are eligible for updates and add to queue only if updates are available to customer
			$customer_updates = serialize( edd_pup_eligible_updates( $customer['post_id'], $products, true, $licenseditems ) );
					
			if ( ! empty( $customer_updates ) ) {

				$queue[] = '('.$customer['post_id'].', '.$email_id.', \''.$customer_updates.'\', 0)';
				$count++;
								
				// Insert into database in batches of 1000
				if ( $i % $limit == 0 ){

					$queueinsert = implode( ',', $queue );
					$wpdb->query( "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, products, sent) VALUES $queueinsert" );
					
					// Reset defaults for next batch
					$queue = '';
					
					break;
				}
			
			}
			
			$i++;
		}
		
		// Insert leftovers or if batch is less than 1000
		if ( ! empty( $queue ) ) {
			$queueinsert = implode( ',', $queue );
			$wpdb->query( "INSERT INTO $wpdb->edd_pup_queue (customer_id, email_id, products, sent) VALUES $queueinsert" );
		}
	    		
		echo json_encode(array('status'=>'new','sent'=>0,'total'=>absint($total),'processed'=>absint($processed+$count)));
		
		exit;
	
	}

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
		$email_id = get_transient( 'edd_pup_sending_email_'. get_current_user_id() );
	}

	// Refresh email ID transient
	set_transient( 'edd_pup_sending_email_'. get_current_user_id(), $email_id, 60);
			
	$batch = $_POST['iteration'];
	$sent = $_POST['sent'];
	$limit = 10;
	$rows = array();
	
	$query = "SELECT * FROM $wpdb->edd_pup_queue WHERE email_id = $email_id AND sent = 0 LIMIT $limit";
	
	$customers = $wpdb->get_results( $query , ARRAY_A);

	foreach ( $customers as $customer ) {
	
			$trigger = edd_pup_ajax_send_email( $customer['customer_id'], $email_id );
						
			// Reset file download limits for customers' eligible updates
			$customer_updates = edd_pup_get_customer_updates( $customer['customer_id'], $email_id );
			
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
		$wpdb->query( "UPDATE $wpdb->edd_pup_queue SET sent=1 WHERE eddpup_id IN ($updateids)" );
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

	$userid 	  = get_current_user_id();
	$emailpost 	  = get_post( $email_id );
	$emailmeta 	  = get_post_custom( $email_id );
	$payment_data = edd_get_payment_meta( $payment_id );
	$email        = edd_get_payment_user_email( $payment_id );
	$from_name 	  = $emailmeta['_edd_pup_from_name'][0];
	$from_email   = $emailmeta['_edd_pup_from_email'][0];
	$attachments  = apply_filters( 'edd_pup_attachments', array(), $payment_id, $payment_data );
	
	add_filter('edd_email_template', 'edd_pup_template' );
		
	/* If subject doesn't use tags (and thus is the same for each customer)
	 * then store it in a transient for quick access on subsequent loops. */
	$subject = get_transient( 'edd_pup_subject_'. $userid );

	if ( false === $subject || $emailmeta['_edd_pup_subject'][0] != $subject ) {
		
		if ( empty( $emailmeta['_edd_pup_subject'][0] ) ) {		
		
			$subject = '(no subject)';
			wp_update_post( array( 'ID' => $email_id, 'post_excerpt' => $subject ) );
			update_post_meta ( $email_id, '_edd_pup_subject', $subject );
			set_transient( 'edd_pup_subject_'. $userid, $subject, 60 * 60 );
			
		} else {
		
			$subject = edd_do_email_tags( $emailmeta['_edd_pup_subject'][0], $payment_id );
			
			if ( $subject == $emailmeta['_edd_pup_subject'][0] ) {
				set_transient( 'edd_pup_subject_'. $userid, $subject, 60 * 60 );					
			}		
		}
	}
	
	if ( version_compare( get_option( 'edd_version' ), '2.1' ) >= 0 ) {
	
		$edd_emails = new EDD_emails();
		$message = edd_do_email_tags( $emailpost->post_content, $payment_id );
		$edd_emails->__set( 'from_name', $from_name );
		$edd_emails->__set( 'from_address', $from_email );
		
		$mailresult = $edd_emails->send( $email, $subject, $message, $attachments );
		// For testing purposes only - comment the above line and uncomment this line below
		//$mailresult = true;
				
	} else {
		
		$email_body_header = get_transient( 'edd_pup_email_body_header_'. $userid );
		
		if ( false === $email_body_header ) {
			
			$email_body_header = edd_get_email_body_header();
			
			set_transient( 'edd_pup_email_body_header_'. $userid, $email_body_header, 60 * 60 );
		}
		
		$email_body_footer = get_transient( 'edd_pup_email_body_footer_'. $userid );
		
		if ( false === $email_body_footer ) {
			
			$email_body_footer = edd_get_email_body_footer();
			
			set_transient( 'edd_pup_email_body_footer_'. $userid, $email_body_footer, 60 * 60 );
		}
		
		$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: ". $from_email . "\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
				
		$message = $email_body_header;
		$message .= apply_filters( 'edd_pup_message', edd_email_template_tags( $emailpost->post_content, $payment_data, $payment_id ), $payment_id, $payment_data );
		$message .= $email_body_footer;	
			
		$mailresult = wp_mail( $email, $subject, $message, $headers, $attachments );
		// For testing purposes only - comment the above line and uncomment this line below
		//$mailresult = true;
	}
	
	// Update payment notes to log this email being sent	
	edd_insert_payment_note( $payment_id, 'Sent product update email "'. $subject .'" <a href="'.admin_url( 'edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='.$email_id ).'">View Email</a>' );
    
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
	$user = get_current_user_id();

	if ( !empty( $_POST['emailid'] ) && ( absint( $_POST['emailid'] ) != 0 ) ) {
		$email_id = $_POST['emailid'];
		
	} else {
		$email_id = get_transient( 'edd_pup_sending_email_'. $user );
	}
	
	// Refresh email ID transient
	set_transient( 'edd_pup_sending_email_'. $user, $email_id, 60);
	
	// Update email post status to publish
	wp_publish_post( $email_id );
	
	// Clear queue for next send
	$wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $email_id ), array( '%d' ) );

	// Flush remaining transients
	delete_transient( 'edd_pup_sending_email_'. $user );
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_subject' );	
	delete_transient( 'edd_pup_email_body_header' );
	delete_transient( 'edd_pup_email_body_footer' );
	delete_transient( 'edd_pup_from_name' );
		
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
function edd_pup_clear_queue() {

	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'clear-queue-'.$_REQUEST['email'] ) ) {
		echo 'noncefail';
		exit;
	}
	
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
	}
	
	die();
}
add_action( 'wp_ajax_edd_pup_clear_queue', 'edd_pup_clear_queue' );

/**
 * Determines whether an AJAX send is from the queue (a restart)
 * or fresh (no previous attempts to send).
 * 
 * @access public
 * @param mixed $emailid (default: null)
 * @return mixed array of queue totals if it's a restart, false if not a restart
 */
function edd_pup_is_ajax_restart( $emailid = null ) {
	
	if ( empty( $emailid ) ) {
		return;
	}
	
	$queue = edd_pup_check_queue( $emailid );
	
	if ( $queue['queue'] > 0 ) {
		return $queue;
	} else {
		return false;
	}
}

/**
 * Trigger the sending of a Product Update Test Email
 *
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function edd_pup_send_test_email() {
	$form = array();
	parse_str( $_POST['form'], $form );
	
	if ( empty( $form['edd-pup-test-nonce'] ) || ! wp_verify_nonce( $form['edd-pup-test-nonce'], 'edd-pup-send-test-email' ) ) {
		_e( 'Nonce failure. Invalid response from server when trying to send test email. Please try again or contact support.', 'edd-pup');
		die();
	}
	
	
	$error = 0;	

	if ( empty( $form['test-email'] ) ) {
		_e( 'Please enter an email address to send the test to.', 'edd-pup' );		
	} else {
		
		$emails = explode( ',', $form['test-email'], 6 );
		
		if ( count( $emails ) > 5 ) {
			array_pop( $emails );
		}
		
		// Sanitize our email addresses to make sure they're valid
		foreach ( $emails as $key => $address ) {
			$clean = sanitize_email( $address );
			
			if ( is_email( $clean ) ) {
				$to[$key] = $clean;
			} else {
				$error++;
			}
		}
		
		if ( !empty( $to ) ) {
			$email_id = edd_pup_sanitize_save( $_POST );
				
			// Set transient for custom tags in test email
			set_transient( 'edd_pup_preview_email_'. get_current_user_id(), $email_id, 60 );
				
			// Send a test email
	    	$sent = edd_pup_test_email( $email_id, $to );
	    	
			// Instruct browser to redirect for continued editing of email
			parse_str( $_POST['url'], $url );
			if ( $url['view'] == 'add_pup_email' ) {
				echo absint( $email_id );
				die();
			}
	
	    	if ( $error > 0 ) {
				_e( 'One or more of the emails entered were invalid. Test emails sent to: ' . implode(', ', $sent), 'edd-pup' );	    	
	    	} else {
	    	
				_e( 'Test email sent to: ' . implode(', ', $sent), 'edd-pup' );
			}		
			
		} else if ( empty( $to ) && $error > 0 ) {
			_e( 'Your email address was invalid. Please enter a valid email address to send the test.', 'edd-pup' );
		}
	
	}	
	
    die();
}
add_action( 'wp_ajax_edd_pup_send_test_email', 'edd_pup_send_test_email' );

/**
 * Email the product update test email to the admin account
 *
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_pup_test_email( $email_id, $to = null ) {	
	
	$email     = get_post( $email_id );
	$emailmeta = get_post_custom( $email_id );
	
	add_filter('edd_email_template', 'edd_pup_template' );

	if ( version_compare( get_option( 'edd_version' ), '2.1' ) >= 0 ) {
		$edd_emails = new EDD_Emails;
		$message = $edd_emails->build_email( edd_email_preview_template_tags( $email->post_content ) );
	} else {
		$message = edd_get_email_body_header();
		$message .= apply_filters( 'edd_pup_test_message', edd_apply_email_template( $email->post_content, null, null ), $email_id );
		$message .= edd_get_email_body_footer();	
	}

	$from_name = isset( $emailmeta['_edd_pup_from_name'][0] ) ? $emailmeta['_edd_pup_from_name'][0] : get_bloginfo('name');
	$from_email = isset( $emailmeta['_edd_pup_from_email'][0] ) ? $emailmeta['_edd_pup_from_email'][0] : get_option('admin_email');

	$subject = apply_filters( 'edd_pup_test_subject', isset( $email->post_excerpt )
		? trim( $email->post_excerpt )
		: __( '(no subject)', 'edd-pup' ), 0 );
	$subject = strip_tags ( edd_email_preview_template_tags( $subject ) );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers = apply_filters( 'edd_pup_test_headers', $headers );
	
	foreach ( $to as $recipient ) {
		wp_mail( $recipient, $subject, $message, $headers );
	}
	
	return $to;
}