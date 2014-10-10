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

}
add_action( 'admin_menu', 'edd_add_prod_update_submenu', 10 );

function edd_pup_admin_page() {
	
	if ( isset( $_GET['view'] ) && $_GET['view'] == 'edit_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
		require 'edit-pup-email.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'view_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
		require 'view-pup-email.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'send_pup_ajax' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {	
		require 'popup.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'add_pup_email' ) {	
		require 'add-pup-email.php';
		
	} else {
		require_once ( 'class-edd-pup-table.php' );
		
		$pup_table = new EDD_Pup_Table();
		$pup_table->prepare_items();
			?>

			<div class="wrap edd-pup-list">	
				<h2><?php _e( 'Product Update Emails', 'edd-pup' ); ?><a href="<?php echo add_query_arg( array( 'view' => 'add_pup_email', 'edd-message' => false ) ); ?>" class="add-new-h2"><?php _e( 'Send New Email', 'edd-pup' ); ?></a></h2>
				<?php do_action( 'edd_pup_page_top' ); ?>
				<form id="edd-pup-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>">
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
 * Generates HTML for the "View Details" popup on emails in queue alert
 * 
 * @access public
 * @return void
 */
function edd_pup_queue_details() {
	$email_list = edd_pup_queue_emails();
	$n = count( $email_list );
		?>
		<div id="edd-pup-queue-details-wrap" style="display:none;">
		<div id="edd-pup-queue-details">
		<h2><?php _e( 'Product Updates Email Queue', 'edd-pup' ); ?></h2>
		<p><?php _e( 'The following emails have not finished sending. Please choose whether to finish sending them now or to clear the queue. Emails are automatically cleared from the queue after 48 hours.', 'edd-pup' ); ?></p>
		<?php foreach ( $email_list as $email ) : 
			$queue = edd_pup_check_queue( $email );
			$i = 1;
			$email_data = get_post_custom( $email );
		?>
			<div id="edd-pup-queue-email-<?php echo $i;?>" class="edd-pup-queue-email">
					<ul>
						<li><strong><?php _e( 'Email ID:', 'edd-pup' ); ?></strong> <?php echo $email;?></li>
						<li><strong><?php _e( 'Subject:', 'edd-pup' ); ?></strong> <?php echo $email_data['_edd_pup_subject'][0];?></li>
						<li><strong><?php _e( 'Total Recipients:', 'edd-pup' ); ?></strong> <?php echo $queue['total'];?></li>
						<li><strong><?php _e( 'Processed:', 'edd-pup' ); ?></strong> <?php echo $queue['sent'];?></li>
						<li><strong><?php _e( 'Queued:', 'edd-pup' ); ?></strong> <?php echo $queue['queue'];?></li>
						<li><strong><?php _e( 'Last Send Attempt:', 'edd-pup' ); ?></strong> <?php echo date( 'M jS, Y g:i A T', strtotime($queue['date']) );?></li>
						<li><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='. $email ); ?>"><?php _e( 'View Email Details', 'edd-pup' ); ?></a></li>				
					</ul>
					<?php if ( $n >= 1 ): ?>
					<div class="button primary-button edd-pup-queue-button" data-action="edd_pup_send_queue" data-email="<?php echo $email;?>"><?php _e( 'Send Remaining Emails', 'edd-pup' ); ?></div>
					<div class="button primary-button edd-pup-queue-button" data-action="edd_pup_clear_queue" data-email="<?php echo $email;?>"><?php _e( 'Clear From Queue', 'edd-pup' ); ?></div>
					<?php endif; ?>
				</div><!-- end #edd-pup-queue-email-<?php echo $i;?> -->
		<?php endforeach; ?>
			<div id="edd-pup-queue-buttons">
				<?php if ( $n > 1 ): ?>
				<input type="submit" name="edd-pup-send-queue-all" id="edd-pup-send-queue-all" class="button button-primary edd-pup-queue-button" value="<?php _e( 'Send All Emails', 'edd-pup' ); ?>" data-email="all" data-action="edd_pup_send_queue">
				<input type="submit" name="edd-pup-empty-queue-all" id="edd-pup-empty-queue-all" class="button edd-pup-queue-button" value="<?php _e( 'Clear All From Queue', 'edd-pup' ); ?>" data-email="all" data-action="edd_pup_clear_queue">
				<?php endif; ?>
				<button class="closebutton button button-secondary"><?php _e( 'Close Window', 'edd-pup' ); ?></button>
			</div><!-- end #edd-pup-queue-buttons -->
		</div><!-- end #edd-pup-queue-details -->
	</div><!-- end #edd-pup-queue-details-wrap -->';
<?php
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
			
		if ( $remaining == 0 ) {
				return;
		}
			
		ob_start();?>
		
		<div class="update-nag">
		<?php printf( __( 'There are %s product update emails that have not been sent.', 'edd-pup'), number_format( $remaining, 0, '.', ',' ) ); ?> <a id="edd-pup-view-queue-alert" href="#edd-pup-queue-details"><?php _e( 'View Details', 'edd-pup' ); ?></a>.
		</div>
		
		<?php
		echo ob_get_clean();
		add_action('admin_footer', 'edd_pup_queue_details');
	}
}

add_action('admin_notices', 'edd_pup_queue_alert', 10);