<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

?>
		<div id="progress-wrap">
			<h2><?php _e('Sending Emails', 'edd-pup');?></h2>
				<p><strong><?php _e( 'WARNING: Do not refresh this page or close this window until sending is complete.', 'edd-pup' ); ?></p>
				<?php echo submit_button( __( 'Start Sending', 'edd-pup' ), 'primary', 'edd-pup-ajax', false, array( 'data-email'=> $_GET['id'], 'data-action' => 'start' ) );?>
				<?php wp_nonce_field( 'edd_pup_ajax_start', 'edd_pup_sajax_nonce', false, true ); ?>
			<div class="progress-wrap">
				<div class="progress">
				  <div class="progress-bar" data-complete="0"></div>
				</div>
				<div class="progress-text">
					<p><span class="progress-clock badge">00:00</span></p>
					<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">0</span> <?php _e('emails processed', 'edd-pup');?></strong> (<span class="progress-percent">0%</span>)</p>
				</div><!-- end .progress-text -->
			</div><!-- end .progress-wrap -->
			<div id="completion" style="display:none">
				<h3><?php _e( 'Success!', 'edd-pup' );?></h3>
				<p><span class="success-total">0</span> <?php _e('emails processed in', 'edd-pup' );?> <span class="success-time-h">0</span> <?php _e( 'hr.', 'edd-pup' ); ?> <span class="success-time-m">0</span> <?php _e( 'min.', 'edd-pup' ); ?> <span class="success-time-s">0</span> <?php _e( 'sec.', 'edd-pup' ); ?></p>
				<div class="button primary-button"><?php _e( 'View Sent Email', 'edd-pup' ); ?></div>
				<div class="button primary-button"><?php _e( 'Send Another Update Email', 'edd-pup' ); ?></div>
			</div><!-- end #completion -->
		</div><!-- end #progress-wrap -->