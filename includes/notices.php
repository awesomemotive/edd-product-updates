<?php
/**
 * EDD Product Updates Post TYpes
 *
 * Install certain post types
 *
 *
 * @package    EDD_PUP
 * @author     Evan Luzi
 * @copyright  Copyright 2014 Evan Luzi, The Black and Blue, LLC
 * @since      0.9.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Generates admin notices based on edd_pup_notice parameter value
 * 
 * @access public
 * @return void
 */
function edd_pup_notices() {
	$style = 'updated';
	$supporturl = 'https://easydigitaldownloads.com/support/';
	
	if ( isset( $_GET['post_type'] ) && ( $_GET['post_type'] == 'download' ) && isset( $_GET['page'] ) && ( $_GET['page'] == 'edd-prod-updates' ) ) {

		if ( isset( $_GET['edd_pup_notice'] ) ) {
			
			switch ( $_GET['edd_pup_notice'] ) {
				case 1:
					$message = __( 'Product Update Email changes saved successfully.', 'edd-pup' );
					break;
				case 2:
					$message = __( 'Product Update Email saved successfully.', 'edd-pup' );
					break;
				case 3:
					$message = sprintf( __( 'Product Update Email changes <strong>did not save successfully</strong>. If the issue continues, please <a href="%s" target="_%s">contact Easy Digital Downloads support</a> for help.', 'edd-pup' ), $supporturl, '_blank' );
					$style = 'error';
					break;
				case 4:
					$message = sprintf( __( 'Email was <strong>not deleted successfully</strong>. If the issue continues, please <a href="%s" target="_%s">contact Easy Digital Downloads support</a> for help.', 'edd-pup' ), $supporturl, '_blank' );
					$style = 'error';
					break;
				case 5:
					$message = __( '1 email successfully deleted.', 'edd-pup' );
					break;
			}
	    ?>
	    <div class="<?php echo $style; ?> edd-pup-message">
	        <p><?php echo $message; ?></p>
	    </div>
	    <?php
	    }
    }
}
add_action( 'admin_notices', 'edd_pup_notices', 10);


/**
 * Generates admin alert if there are unsent emails in the queue that aren't being processed.
 * 
 * @access public
 * @return void
 */
function edd_pup_queue_alert(){	
	
	if ( (! empty( $_GET['post_type'] ) ) && ( $_GET['post_type'] == 'download') && ( false === get_transient( 'edd_pup_sending_email_'. get_current_user_id() ) ) ){
		$remaining = edd_pup_check_queue_total();
			
		if ( $remaining == 0 ) {
			return;
		
		} else {
			$email_list = edd_pup_emails_processing();	
			$number = empty( $email_list['processing'] ) ? number_format( $remaining, 0, '.', ',' ) : '' ;
			
			if ( !empty( $email_list['queued'] ) ) {
				ob_start();?>
				
				<div class="update-nag">
				<?php printf( __( 'There are %s product update emails that have not been sent.', 'edd-pup'), $number ); ?> <a id="edd-pup-view-queue-alert" href="#edd-pup-queue-details"><?php _e( 'View Details', 'edd-pup' ); ?></a>.
				</div>
				
				<?php
				echo ob_get_clean();
				add_action('admin_footer', 'edd_pup_queue_details');
			}
		}
	}
}

add_action('admin_notices', 'edd_pup_queue_alert', 10);

/**
 * Generates HTML for the "View Details" popup on emails in queue alert
 * 
 * @access public
 * @return void
 */
function edd_pup_queue_details() {
	global $edd_options;
	$email_list = edd_pup_emails_processing();
	$n = count( $email_list['queued'] );
	$dateformat = get_option( 'date_format' ). ' ' . get_option( 'time_format' );
		?>
		<div id="edd-pup-queue-details-wrap" style="display:none;">
		<div id="edd-pup-queue-details">
		<h2><?php _e( 'Product Updates Email Queue', 'edd-pup' ); ?></h2>
		<p><?php _e( 'The emails listed below have not finished sending. Please choose whether to finish sending them now or to clear the queue. ', 'edd-pup'); if ( empty( $edd_options['edd_pup_auto_del'] ) ) { printf( __('Emails are automatically cleared from the queue after 48 hours (<a href="%s">Disable this on the settings page</a>.)', 'edd-pup' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=emails#edd_pup_settings' ) ); } ?></p>
		<?php foreach ( $email_list['queued'] as $email ) : 
				$queue = edd_pup_check_queue( $email );
				$i = 1;
				$email_data = get_post_custom( $email );
				$subject = isset( $email_data['_edd_pup_subject'][0] ) ? $email_data['_edd_pup_subject'][0] : '<em>Unable to find subject</em>';
			?>
				<div id="edd-pup-queue-email-<?php echo $i;?>" class="edd-pup-queue-email">
						<ul>
							<li><strong><?php _e( 'Email ID:', 'edd-pup' ); ?></strong> <?php echo $email;?></li>
							<li><strong><?php _e( 'Subject:', 'edd-pup' ); ?></strong> <?php echo $subject;?></li>
							<li><strong><?php _e( 'Total Recipients:', 'edd-pup' ); ?></strong> <?php echo number_format( $queue['total'] );?></li>
							<li><strong><?php _e( 'Processed:', 'edd-pup' ); ?></strong> <?php echo number_format( $queue['sent'] );?></li>
							<li><strong><?php _e( 'Queued:', 'edd-pup' ); ?></strong> <?php echo number_format( $queue['queue'] );?></li>
							<li><strong><?php _e( 'Last Send Attempt:', 'edd-pup' ); ?></strong> <?php echo mysql2date( $dateformat, strtotime($queue['date']) );?></li>
							<li><a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='. $email ); ?>"><?php _e( 'View Email Details', 'edd-pup' ); ?></a></li>				
						</ul>
						<?php if ( $n >= 1 ): ?>
						<div class="button primary-button edd-pup-queue-button" data-url="<?php echo add_query_arg( array( 'view' => 'send_pup_ajax', 'id' => $email, 'restart' => 1 ), admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ); ?>" data-action="edd_pup_send_queue" data-email="<?php echo $email;?>"><?php _e( 'Send Remaining Emails', 'edd-pup' ); ?></div>
						<div class="button primary-button edd-pup-queue-button" data-action="edd_pup_clear_queue" data-email="<?php echo $email;?>" data-nonce="<?php echo wp_create_nonce( 'clear-queue-' . $email ); ?>"><?php _e( 'Clear From Queue', 'edd-pup' ); ?></div>
						<?php endif; ?>
					</div><!-- end #edd-pup-queue-email-<?php echo $i;?> -->
		<?php endforeach; ?>
			<div id="edd-pup-queue-buttons">
				<?php if ( $n > 1 ): ?>
				<!--<input type="submit" name="edd-pup-send-queue-all" id="edd-pup-send-queue-all" class="button button-primary edd-pup-queue-button" value="<?php _e( 'Send All Emails', 'edd-pup' ); ?>" data-email="all" data-action="edd_pup_send_queue">-->
				<input type="submit" name="edd-pup-empty-queue-all" id="edd-pup-empty-queue-all" class="button button-primary edd-pup-queue-button" value="<?php _e( 'Clear All From Queue', 'edd-pup' ); ?>" data-email="all" data-action="edd_pup_clear_queue" data-nonce="<?php echo wp_create_nonce( 'clear-queue-all' ); ?>">
				<?php endif; ?>
				<button class="closebutton button button-secondary"><?php _e( 'Close Window', 'edd-pup' ); ?></button>
			</div><!-- end #edd-pup-queue-buttons -->
		</div><!-- end #edd-pup-queue-details -->
	</div><!-- end #edd-pup-queue-details-wrap -->
<?php
}
