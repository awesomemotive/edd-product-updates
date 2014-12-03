<?php
/**
 * Sending emails popup page
 *
 * @since 0.9.3
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Don't allow emails that have already been processed or are being processed to send.
switch ( get_post_status( $_GET['id'] ) ) {
	case 'publish':
		printf( __( 'This email has already been sent. <a href="%s" target="_blank">View this email in the dashboard.</a>', 'edd-pup' ), admin_url('edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='. $_GET['id'] ) );
		return;
	case 'abandoned':
		printf( __( 'This email has been cancelled. <a href="%s" target="_blank">View this email in the dashboard.</a>', 'edd-pup' ), admin_url('edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='. $_GET['id'] ) );
		return;
	case 'pending':
		if ( edd_pup_is_processing( $_GET['id'] ) ) {
			_e( 'This email is processing.', 'edd-pup' );
			return;
		} else if ( empty( $_GET['restart'] ) ) {
			_e( 'This email is in the queue and has remaining messages to send. If you would like to send those messages now, click the send button below.', 'edd-pup' );
			break;		
		}
}

// Don't allow a user to send multiple emails at once
if ( false !== get_transient( 'edd_pup_sending_email_'. get_current_user_id() ) ) {
	_e( 'Cannot process multiple emails at once. Please pause the email you are currently sending or wait for it to finish before attempting to send another. <a href="#" onclick="window.close()">Close Window.</a>', 'edd-pup' );
	return;
}

?>
<div id="popup-wrap">
	<a href="#" class="progress-close" onclick="window.close()"><?php _e( 'Close Window', 'edd-pup'); ?></a>
	<h2><?php printf( __('Sending "%s"', 'edd-pup'), get_the_title( $_GET['id'] ) );?></h2>
		<p><strong><?php _e( 'WARNING: Do not refresh this page or close this window until sending is complete.', 'edd-pup' ); ?></strong></p>
		<div class="progress-start-wrap">
			<?php echo submit_button( __( 'Start Sending', 'edd-pup' ), 'primary', 'edd-pup-ajax', false, array( 'data-email'=> $_GET['id'], 'data-action' => 'start' ) );?>
			<span class="edd-pup-popup-spin spinner"></span>
		</div>
		<?php wp_nonce_field( 'edd_pup_ajax_start', 'edd_pup_sajax_nonce', false, true ); ?>
	<div class="progress-wrap">
		<p class="progress-status"><strong><?php _e( 'Status:', 'edd-pup' ); ?></strong> <span class="status-text"><?php _e( 'Preparing Emails to Send', 'edd-pup' ); ?></span></p>
		<div class="progress">
		  <div class="progress-bar red" data-complete="0" style="width: 0%;"></div>
		</div>
		<div class="progress-text">
			<p><span class="progress-clock badge">00:00:00</span></p>
			<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">0</span> <?php _e('emails processed', 'edd-pup');?></strong> (<span class="progress-percent">0%</span>)</p>
		</div><!-- end .progress-text -->
	</div><!-- end .progress-wrap -->
	<div id="completion" style="display:none">
		<h3><?php _e( 'Success!', 'edd-pup' );?></h3>
		<p><span class="success-total">0</span> <?php _e('emails processed in', 'edd-pup' );?> <span class="success-time-h" style="display:none;">0</span> <span class="success-time-m" style="display:none;">0</span> <span class="success-time-s">0</span></p>
		<p class="success-restart" style="display:none;"><span class="success-restart-p">0</span> <?php _e('emails previously sent.', 'edd-pup' );?></p>
		<p class="success-restart" style="display:none;"><span class="success-restart-t">0</span> <?php _e('emails sent total.', 'edd-pup' );?></p>
		<a class="button primary-button" href="<?php echo admin_url('edit.php?post_type=download&page=edd-prod-updates&view=view_pup_email&id='. $_GET['id'] ); ?>" target="_blank"><?php _e( 'View Sent Email', 'edd-pup' ); ?></a>
		<a class="button primary-button" href="<?php echo admin_url('edit.php?post_type=download&page=edd-prod-updates&view=add_pup_email'); ?>" target="_blank"><?php _e( 'Send Another Update Email', 'edd-pup' ); ?></a>
	</div><!-- end #completion -->
</div><!-- end #progress-wrap -->
<script type="text/javascript">
	eddPupAjaxEmails();
	
	jQuery(window).bind('beforeunload', function(){
		if ( jQuery('.progress-bar').data('complete') <= 99 && parseInt( jQuery('.progress-queue').html() ) > 0 ) {
			return '<?php _e( 'This will cause your emails to stop sending.', 'edd-pup' ); ?>';
		}
	});
</script>