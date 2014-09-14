<?php
/*
Author: Evan Luzi
Author URI: http://evanluzi.com
Contributors: Evan Luzi
*/

/**
 * Creates the admin submenu page under the Downloads menu
 *
 * @since 0.9.2
 * @return void
 */
function edd_add_prod_update_submenu() {

	add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'install_plugins', 'edd-prod-updates', 'edd_pup_admin_page' );
	//add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'install_plugins', 'edit.php?post_type=download&page=edd-prod-updates', 'edd_pup_admin_page');
}
add_action( 'admin_menu', 'edd_add_prod_update_submenu', 10 );

function edd_pup_admin_page() {
	if ( isset( $_GET['view'] ) && $_GET['view'] == 'edit_pup_email' ) {
		require 'edit-pup-email.php';
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'add_pup_email' ) {
		require 'add-pup-email.php';
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'view_pup_email' ) {
		require 'view-pup-email.php';
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'popup' ) {
		require 'popup.php';
	} else {
	require_once ( 'class-edd-pup-table.php' );
	$pup_table = new EDD_Pup_Table();
	$pup_table->prepare_items();
			?>

			<div class="wrap edd-pup-list">
				<h2><?php _e( 'Product Update Emails', 'edd-pup' ); ?><a href="<?php echo add_query_arg( array( 'view' => 'add_pup_email', 'edd-message' => false ) ); ?>" class="add-new-h2"><?php _e( 'Send New Email', 'edd-pup' ); ?></a></h2>
				<?php do_action( 'edd_pup_page_top' ); ?>
				<form id="edd-pup-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>">
					<?php $pup_table->search_box( __( 'Search', 'edd-pup' ), 'edd-pup' ); ?>
					<input type="hidden" name="post_type" value="download" />
					<input type="hidden" name="page" value="edd-prod-updates" />
					<?php $pup_table->views() ?>
					<?php $pup_table->display() ?>
				</form>
				<?php do_action( 'edd_pup_page_bottom' ); ?>
			</div>
		<?php
	}
}

/**
 * Temporary: Front end UI for AJAX batch sending of emails. Will be moved
 * to a popup window in future version.
 * 
 * @access public
 * @return void
 * @since 0.9.2
 */
function edd_pup_progress_html() {
						
	if ( isset( $_GET['edd_action'] ) ) {

		if ( ! $_GET['edd_action'] == 'pup_send_ajax' ) {
			return;
		}

		ob_start(); ?>
			<div id="progress-wrap">
				<h2>Sending Emails</h2>
				<p><strong>WARNING:</strong> Do not refresh this page or close this window until sending is complete.</p>
				<?php echo submit_button('Start', 'primary', 'edd-pup-ajax-btn', false, array('data-action' => 'start'));?>
				<div class="progress-wrap">
					<div class="progress">
					  <div class="progress-bar" data-complete="0"></div>
					</div><!--end .progress -->
					<div class="progress-text">
						<p><span class="progress-clock badge">00:00</span></p>
						<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">100</span> emails processed</strong> (<span class="progress-percent">0%</span>)</p>
						<div class="progress-log">
							<p class="plog-1">Building email queue</p>
							<p class="plog-2">Sending emails</p>
							<p class="plog-3">Finishing up</p>								
						</div><!-- end .progress-log -->
					</div><!-- end .progress-text -->
				</div><!-- end .progress-wrap -->
				<div id="completion" style="display:none">
					<h3>Success!</h3>
					<p><span class="success-total">100</span> emails processed in <span class="success-time">1 hr 25 min.</span></p>
					<div class="button primary-button">Send Another Product Update</div>
				</div><!-- end #completion -->
			</div><!-- end #progress-wrap -->
		<?php
		echo ob_get_clean();
	}
}

function edd_pup_queue_details() {
	$email_list = edd_pup_queue_emails();
	$n = count($email_list);
		?>
		<div id="edd-pup-queue-details-wrap" style="display:none;">
		<div id="edd-pup-queue-details">
		<h2>Product Updates Email Queue</h2>
		<p>The following emails have not finished sending. Please choose whether to finish sending them now or to clear the queue. Emails are automatically cleared from the queue after 48 hours.</p>
		<?php foreach ( $email_list as $email ) : 
			$queue = edd_pup_check_queue( $email );
			$i = 1;
			$email_data = get_post_custom( $email );
		?>
			<div id="edd-pup-queue-email-<?php echo $i;?>" class="edd-pup-queue-email">
					<ul>
						<li><strong>Email ID:</strong> <?php echo $email;?></li>
						<li><strong>Subject:</strong> <?php echo $email_data['_edd_pup_subject'][0];?></li>
						<li><strong>Total Recipients:</strong> <?php echo $queue['total'];?></li>
						<li><strong>Processed:</strong> <?php echo $queue['sent'];?></li>
						<li><strong>Queued:</strong> <?php echo $queue['queue'];?></li>
						<li><strong>Last Send Attempt:</strong> <?php echo date( 'M jS, Y g:i A T', strtotime($queue['date']) );?></li>
						<li><a href="#">View Email Details</a></li>				
					</ul>
					<?php if ( $n > 1 ): ?>
					<div class="button primary-button edd-pup-queue-button" data-action="edd-pup-send-queue" data-email="<?php echo $email;?>">Send Remaining Emails</div>
					<div class="button primary-button edd-pup-queue-button" data-action="edd-pup-empty-queue" data-email="<?php echo $email;?>">Clear From Queue</div>
					<?php endif; ?>
				</div><!-- end #edd-pup-queue-email-<?php echo $i;?> -->
		<?php endforeach; ?>
			<div id="edd-pup-queue-buttons">
		<?php	
		echo submit_button('Send All Emails', 'primary', 'edd-pup-send-queue-all', false, array('data-email'=> 'all', 'data-action' => 'edd-pup-send-queue', 'class' => 'edd-pup-queue-button' ));
		echo submit_button('Clear the Queue', 'secondary', 'edd-pup-empty-queue-all', false, array('data-email'=> 'all', 'data-action' => 'edd-pup-empty-queue', 'class' => 'edd-pup-queue-button' ));
		echo '<button class="closebutton button button-secondary">Close Window</button>';
		echo '</div><!-- end #edd-pup-queue-buttons -->
			  </div><!-- end #edd-pup-queue-details -->
			  </div><!-- end #edd-pup-queue-details-wrap -->';

}

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

function edd_pup_check_queue_total() {

	if ( false === get_transient( 'edd_pup_sending' ) ){
		global $wpdb;
		
		$query = "SELECT COUNT(eddpup_id) FROM $wpdb->edd_pup_queue WHERE sent = 0";
		$total = $wpdb->get_results( $query , ARRAY_A);
	}

	return $total[0]['COUNT(eddpup_id)'];
}

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

function edd_pup_queue_alert(){	
	
	if ( ( false === get_transient( 'edd_pup_sending' ) ) && (! empty( $_GET['post_type'] ) ) && ( $_GET['post_type'] == 'download') ){
		$remaining = edd_pup_check_queue_total();
			
			if ($remaining == 0) {
				return;
			}
			
		ob_start();?>
		
		<div class="update-nag">
		There are <?php echo number_format($remaining,0,'.',',');?> product update emails that have not been sent. <a id="edd-pup-view-queue-alert" href="#edd-pup-queue-details">View Details</a> or <a href="#">send them now</a>.
		</div>
		
		<?php
		echo ob_get_clean();
		add_action('admin_footer', 'edd_pup_queue_details');
	}
}

add_action('admin_notices', 'edd_pup_queue_alert', 10);

/*function edd_pup_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-pup-test-email' ) )
		return;

	// Send a test email
    edd_pup_test_email();

    // Remove the test email query arg
    wp_redirect( remove_query_arg( 'edd_action' ) ); exit;
}
add_action( 'edd_pup_send_test_email', 'edd_pup_send_test_email' );

<a href="<?php echo wp_nonce_url( add_query_arg( array( 'edd_action' => 'pup_send_test_email' ) ), 'edd-pup-test-email' ); ?>" title="<?php _e( 'This will send a demo product update email to the From Email listed above.', 'edd-prod-updates' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'edd' ); ?></a>*/