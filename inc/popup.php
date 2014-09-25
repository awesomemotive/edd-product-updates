<?php //if (!defined('W3TC')) die();
	
?>
		<div id="progress-wrap">
		<h2><?php _e('Sending Emails', 'edd-pup');?></h2>
		<p><strong>WARNING:</strong> Do not refresh this page or close this window until sending is complete.</p>
		<?php echo submit_button('Start AJAX Test', 'primary', 'edd-pup-ajax', false, array( 'data-email'=> $_GET['id'], 'data-action' => 'start' ) );?>
		<div class="progress-wrap">
		<div class="progress">
		  <div class="progress-bar" data-complete="0"></div>
		</div>
		<div class="progress-text">
		<p><span class="progress-clock badge">00:00</span></p>
		<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">0</span> <?php _e('emails processed', 'edd-pup');?></strong> (<span class="progress-percent">0%</span>)</p>
		<div class="progress-log">							
		</div><!-- end .progress-log -->
		</div>
		</div>
		<div id="completion" style="display:none">
		<h3><?php _e( 'Success!', 'edd-pup' );?></h3>
		<p><span class="success-total">0</span> <?php _e('emails processed in', 'edd-pup' );?> <span class="success-time-h">0</span> hr. <span class="success-time-m">0</span> min.</p>
		<div class="button primary-button">View Sent Email</div><div class="button primary-button">Send Another Update Email</div>
		</div>
		</div>
	</body>
</html>