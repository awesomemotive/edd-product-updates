<?php
/*
Plugin Name: Easy Digital Downloads - Product Update Emails
Plugin URL: http://easydigitaldownloads.com/extension/mail-chimp
Description: Send product update emails in batch
Version: 0.1
Author: Evan Luzi
Author URI: http://evanluzi.com
Contributors: Evan Luzi
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( 'edd-product-updates-submenu.php');
require_once( 'inc/edd-product-updates-payment.php');
require_once( 'inc/edd-product-updates-tags.php');


add_action( 'init', 'edd_prod_updates_verify_unsub_link');
/**
 * Add javascript file to admin for colorbox email preview, etc.
 * 
 * @access public
 * @return void
 */
function edd_prod_updates_scripts() {
        wp_register_script( 'edd_prod_updates_js', plugins_url(). '/edd-product-updates/assets/edd-pup.min.js', false, '1.0.0' );
        wp_enqueue_script( 'edd_prod_updates_js' );

        wp_register_style( 'edd_prod_updates_css', plugins_url(). '/edd-product-updates/assets/edd-pup.min.css', false, '1.0.0' );
        wp_enqueue_style( 'edd_prod_updates_css' );
}
add_action( 'admin_enqueue_scripts', 'edd_prod_updates_scripts' );

    /**
     * Add settings to EDD Settings
     *
     * @param array $edd_settings
     *
     * @return array EDD Settings
     */
function edd_settings_prod_update ( $edd_settings ) {
        $products = array();

        $downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

	    if ( !empty( $downloads ) ) {
	        foreach ( $downloads as $download ) {
	        	
	            $products[ $download->ID ] = get_the_title( $download->ID );

	        }
	    }

        $settings = array(
            array(
                'id' => 'prod_updates',
                'name' => '<strong>' . __( 'Product Update Settings', 'edd-prod-updates' ) . '</strong>',
                'desc' => __( 'Configure the Product Update settings', 'edd-prod-updates' ),
                'type' => 'header'
            ),
            array(
                'id' => 'prod_updates_products',
                'name' => __( 'Choose products being updated', 'edd-prod-updates' ),
                'desc' => __( 'Which products are being updated?', 'edd-prod-updates' ),
                'type' => 'multicheck',
                'options' => $products
            ),
			array(
				'id' => 'prod_updates_email_template',
				'name' => __( 'Email Template', 'edd' ),
				'desc' => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'edd' ),
				'type' => 'select',
				'options' => edd_get_email_templates()
				),
			array(
				'id' => 'prod_updates_from_name',
				'name' => __( 'From Name', 'edd-prod-updates' ),
				'desc' => __( 'The name product updates are said to come from.', 'edd' ),
				'type' => 'text',
				'std'  => get_bloginfo( 'name' )
			),
			array(
				'id' => 'prod_updates_from_email',
				'name' => __( 'From Email', 'edd-prod-updates' ),
				'desc' => __( 'Email to send product updates from.', 'edd' ),
				'type' => 'text',
				'std'  => get_bloginfo( 'admin_email' )
			),
			array(
				'id' => 'prod_updates_subject',
				'name' => __( 'Product Update Subject', 'edd-prod-updates' ),
				'desc' => __( 'Enter the subject line for the product update email.', 'edd' ),
				'type' => 'text',
				'std'  => __( 'Update available for your product.', 'edd' )
			),
			array(
				'id' => 'prod_updates_message',
				'name' => __( 'Product Update Message', 'edd-prod-updates' ),
				'desc' => __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:', 'edd') . '<br><br>' . edd_get_emails_tags_list(),
				'type' => 'rich_editor',
				'std'  => __( "Dear", "edd" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n{download_list}\n\n{sitename}"
			),
			array(
				'id' => 'prod_updates_email_settings',
				'name' => '',
				'desc' => '',
				'type' => 'hook',
			)
        );

        return array_merge( $edd_settings, $settings );
}
add_filter( 'edd_settings_emails', 'edd_settings_prod_update' );

/**
 * Email Action Buttons
 *
 * @access private
 * @global $edd_options Array of all the EDD Options
 * @since 1.0.8.2
*/

function edd_prod_updates_email_template_buttons() {
	
	global $edd_options;
	
	$products = $edd_options['prod_updates_products'];
	$productlist = '';
	
	foreach ($products as $product) {
		$productlist .= '<li>'.$product.'</li>';
	}
	
	$customercount = edd_prod_updates_customer_count();
	
	$default_email_body = 'This is the default body';
	
	$email_body = isset( $edd_options['prod_updates_message'] ) ? stripslashes( $edd_options['prod_updates_message'] ) : $default_email_body;
	
	ob_start();
	?>
	<a href="#prod-updates-email-preview" id="prod-updates-open-email-preview" class="button-secondary" title="<?php _e( 'Product Update Email Preview', 'edd' ); ?> "><?php _e( 'Preview Email', 'edd' ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array( 'edd_action' => 'send_prod_update_test_email' ) ), 'edd-prod-update-test-email' ); ?>" title="<?php _e( 'This will send a demo product update email to the From Email listed above.', 'edd' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'edd' ); ?></a>
	<div style="margin:10px 0;">
	<?php echo submit_button('Send Product Update Emails', 'primary', 'send-prod-updates', false);?><span class="edd-pu-spin spinner"></span>
	</div>

	<div id="prod-updates-email-preview-wrap" style="display:none;">
		<div id="prod-updates-email-preview">
			<?php echo edd_apply_email_template( $email_body, null, null ); ?>
		</div>
	</div>
	<!-- Begin send email confirmation message -->
	<div id="prod-updates-email-preview-wrap-confirm" style="display:none;">
		<div id="prod-updates-email-preview-confirm">
				<div id="prod-updates-email-confirm-titles">
					<h2><strong>Almost Ready to Send!</strong></h2>
					<p>Please carefully check the information below before sending your emails.</p>
				</div>
					<div id="prod-updates-email-preview-message">
						<div id="prod-updates-email-preview-header">
							<h3>Email Message Preview</h3>
							<ul class="prod-updates-email-confirm-info">
								<li><strong>From:</strong> <?php echo $edd_options['prod_updates_from_name'];?> (<?php echo $edd_options['prod_updates_from_email'];?>)</li>
								<li><strong>Subject:</strong> <?php echo $edd_options['prod_updates_subject'];?></li>
							</ul>
						</div>
				<?php echo edd_apply_email_template( $email_body, null, null ); ?>
				<div id="prod-updates-email-preview-footer">
					<h3>Additional Information</h3>
						<ul class="prod-updates-email-confirm-info">
							<li><strong>Updated Products:</strong></li>
								<ul id="prod-updates-email-confirm-prod-list">
									<?php echo $productlist;?>
								</ul>
							<li><strong>Recipients:</strong> <?php echo $customercount;?> customers will receive this email and have their downloads reset</li>
						</ul>
						<a href="<?php echo wp_nonce_url( add_query_arg( array( 'edd_action' => 'prod_updates_send_emails' ) ), 'edd_prod_updates_send_emails' ); ?>" id="prod-updates-email-send" class="button-primary button" title="<?php _e( 'Confirm and Send Emails', 'edd' ); ?>"><?php _e( 'Confirm and Send Emails', 'edd' ); ?></a>
						<button class="closebutton button button-secondary">Close without sending</button>
					</div>
				</div><!-- end confirmation message -->
				
		</div>
	</div>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_prod_updates_email_settings', 'edd_prod_updates_email_template_buttons' );

function my_action_callback() {
	global $edd_options;
	
	$email_body = isset( $edd_options['prod_updates_message'] ) ? stripslashes( $edd_options['prod_updates_message'] ) : $default_email_body;	

    echo edd_apply_email_template( $email_body, null, null );

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_edd_prod_updates_confirm_ajax', 'edd_prod_updates_email_confirm_html' );

function edd_prod_updates_email_confirm_html(){

	global $edd_options;
	$products = $edd_options['prod_updates_products'];
	$productlist = '';
	
	foreach ($products as $product) {
		$productlist .= '<li>'.$product.'</li>';
	}
	
	$nonceurl = add_query_arg( array( 'edd_action' => 'prod_updates_send_emails' ), $_POST['url'] );
	
	$customercount = edd_prod_updates_customer_count();
	
	$default_email_body = 'This is the default body';
	
	$email_body = isset( $edd_options['prod_updates_message'] ) ? stripslashes( $edd_options['prod_updates_message'] ) : $default_email_body;
	
	ob_start();
	?>
		<!-- Begin send email confirmation message -->
			<div id="prod-updates-email-preview-confirm">
				<div id="prod-updates-email-confirm-titles">
					<h2><strong>Almost Ready to Send!</strong></h2>
					<p>Please carefully check the information below before sending your emails.</p>
				</div>
					<div id="prod-updates-email-preview-message">
						<div id="prod-updates-email-preview-header">
							<h3>Email Message Preview</h3>
							<ul class="prod-updates-email-confirm-info">
								<li><strong>From:</strong> <?php echo $edd_options['prod_updates_from_name'];?> (<?php echo $edd_options['prod_updates_from_email'];?>)</li>
								<li><strong>Subject:</strong> <?php echo $edd_options['prod_updates_subject'];?></li>
							</ul>
						</div>
				<?php echo edd_apply_email_template( $email_body, null, null ); ?>
				<div id="prod-updates-email-preview-footer">
					<h3>Additional Information</h3>
						<ul class="prod-updates-email-confirm-info">
							<li><strong>Updated Products:</strong></li>
								<ul id="prod-updates-email-confirm-prod-list">
									<?php echo $productlist;?>
								</ul>
							<li><strong>Recipients:</strong> <?php echo $customercount;?> customers will receive this email and have their downloads reset</li>
						</ul>
						<a href="<?php echo wp_nonce_url( $nonceurl, 'edd_prod_updates_send_emails' ); ?>" id="prod-updates-email-send" class="button-primary button" title="<?php _e( 'Confirm and Send Emails', 'edd' ); ?>"><?php _e( 'Confirm and Send Emails', 'edd' ); ?></a>
						<button class="closebutton button button-secondary">Close without sending</button>
					</div>
				</div>
			<!-- End send email confirmation message -->
	<?php
	echo ob_get_clean();
	
	die();
}

/**
 * Trigger the sending of a Product Update Test Email
 *
 * @since 1.5
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function edd_send_prod_update_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-prod-update-test-email' ) )
		return;

	// Send a test email
    edd_email_test_prod_update_receipt();

    // Remove the test email query arg
    wp_redirect( remove_query_arg( 'edd_action' ) ); exit;
}
add_action( 'edd_send_prod_update_test_email', 'edd_send_prod_update_test_email' );

/**
 * Email the download link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_email_test_prod_update_receipt() {
	global $edd_options;

	$default_email_body = __( "Dear", "edd" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "edd" ) . "\n\n";
	$default_email_body .= "{download_list}\n\n";
	$default_email_body .= "{sitename}";

	$email = isset( $edd_options['prod_updates_message'] ) ? $edd_options['prod_updates_message'] : $default_email_body;

	$message = edd_get_email_body_header();
	$message .= apply_filters( 'edd_prod_updates_message', edd_email_preview_template_tags( $email ), 0, array() );
	$message .= edd_get_email_body_footer();

	$from_name = isset( $edd_options['prod_updates_from_name'] ) ? $edd_options['prod_updates_from_name'] : get_bloginfo('name');
	$from_email = isset( $edd_options['prod_updates_from_email'] ) ? $edd_options['prod_updates_from_email'] : get_option('admin_email');

	$subject = apply_filters( 'edd_prod_updates_subject', isset( $edd_options['prod_updates_subject'] )
		? trim( $edd_options['prod_updates_subject'] )
		: __( 'Purchase Receipt', 'edd' ), 0 );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	//$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers = apply_filters( 'edd_test_purchase_headers', $headers );

	wp_mail( edd_get_admin_notice_emails(), $subject, $message, $headers );
}


/**
 * Trigger the sending of a Product Update Email
 *
 * @since 1.5
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function edd_prod_updates_send_emails( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd_prod_updates_send_emails' ) )
		return;

	// Send emails
    edd_email_product_update_loop();

    // Remove the test email query arg
    wp_redirect( remove_query_arg( 'edd_action' ) ); exit;
}
add_action( 'edd_prod_updates_send_emails', 'edd_prod_updates_send_emails' );

/**
 * Batch send the Product Update Emails
 *
 * @since 1.0
 * @param array $data Payment Data
 * @return void
 */
function edd_prod_updates_send_batch( $data ) {
	$purchase_id = $data['purchase_id'];
	edd_email_purchase_receipt( $purchase_id, false );

	// Grab all downloads of the purchase and update their file download limits
	$downloads = edd_get_payment_meta_downloads( $purchase_id );

	if ( is_array( $downloads ) ) {
		foreach ( $downloads as $download ) {
			$limit = edd_get_file_download_limit( $download['id'] );
			if ( ! empty( $limit ) ) {
				edd_set_file_download_limit_override( $download['id'], $purchase_id );
			}
		}
	}

	wp_redirect( add_query_arg( array( 'edd-message' => 'email_sent', 'edd-action' => false, 'purchase_id' => false ) ) );
	exit;
}
add_action( 'edd_email_links', 'edd_prod_updates_send_batch' );


/**
 * edd_email_product_update_loop function.
 * 
 * @access public
 * @return void
 */
function edd_email_product_update_loop(){
	global $edd_options;

	$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );
	$upgraded_products = $edd_options['prod_updates_products'];

	$thing = edd_get_payment_status($payments[0], true);

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
	
	$payments = get_posts($queryargs);
	
	foreach ($payments as $customer){
		
		// Don't send to customers who have unsubscribed from updates
		if (edd_prod_updates_user_send_updates($customer->ID)){
	
		$cart_items = edd_get_payment_meta_cart_details($customer->ID, true);
		
		$i = 0;
			
			foreach ($cart_items as $item){
				
				// Check to see if purchased products match updated products
				if ((array_key_exists($item['id'], $upgraded_products)) && ($i === 0)){
					
					edd_email_product_update($customer->ID);
					
					// Increment so only one email is sent per customer
					$i++;
	
				}
			}
		}
	}
}

function edd_prod_updates_customer_count(){
	global $edd_options;
	$customercount = 0;
	
	$upgraded_products = $edd_options['prod_updates_products'];

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
	
	$payments = get_posts($queryargs);
	
	foreach ($payments as $customer){
	
		if (edd_prod_updates_user_send_updates($customer->ID)){
		
		$cart_items = edd_get_payment_meta_cart_details($customer->ID, true);
		$i = 0;
			
			foreach ($cart_items as $item){
			
				if ((array_key_exists($item['id'], $upgraded_products)) && ($i === 0)){
					
					$customercount++;
					
					// Increment so each customer is only counted once
					$i++;
	
				}
			}
		}
	}
	
	return $customercount;
		
}

/**
 * Email the download link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true)
 * @return void
 */
function edd_email_product_update( $payment_id ) {
	global $edd_options;

	$payment_data = edd_get_payment_meta( $payment_id );
	$user_id      = edd_get_payment_user_id( $payment_id );
	$user_info    = maybe_unserialize( $payment_data['user_info'] );
	$email        = edd_get_payment_user_email( $payment_id );

	if ( isset( $user_id ) && $user_id > 0 ) {
		$user_data = get_userdata($user_id);
		$name = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$message = edd_get_email_body_header();
	$message .= apply_filters( 'edd_purchase_receipt', edd_email_template_tags( $edd_options['prod_updates_message'], $payment_data, $payment_id ), $payment_id, $payment_data );
	$message .= edd_get_email_body_footer();
	
	// Remove downloads that aren't updated
	// some code goes here

	$from_name = isset( $edd_options['prod_updates_from_name'] ) ? $edd_options['prod_updates_from_name'] : get_bloginfo('name');
	$from_name = apply_filters( 'edd_prod_updates_from_name', $from_name, $payment_id, $payment_data );

	$from_email = isset( $edd_options['prod_updates_from_email'] ) ? $edd_options['prod_updates_from_email'] : get_option('admin_email');
	$from_email = apply_filters( 'edd_purchase_from_address', $from_email, $payment_id, $payment_data );

	$subject = apply_filters( 'edd_purchase_subject', ! empty( $edd_options['prod_updates_subject'] )
		? wp_strip_all_tags( $edd_options['prod_updates_subject'], true )
		: __( 'New Product Update', 'edd' ), $payment_id );

	$subject = edd_do_email_tags( $subject, $payment_id );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: ". $from_email . "\r\n";
	//$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers = apply_filters( 'edd_receipt_headers', $headers, $payment_id, $payment_data );

	// Allow add-ons to add file attachments
	$attachments = apply_filters( 'edd_receipt_attachments', array(), $payment_id, $payment_data );
	if ( apply_filters( 'edd_email_purchase_receipt', true ) ) {
		wp_mail( $email, $subject, $message, $headers, $attachments );
	}
	
	// Update payment notes to log this email being sent
	$payment_note = 'Sent product update email "'. $subject .'"';
	
	edd_insert_payment_note($payment_id, $payment_note);
}