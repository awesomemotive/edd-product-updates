<?php
/*
Author: Evan Luzi
Author URI: http://evanluzi.com
Contributors: Evan Luzi
*/

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_add_ons_page
 * @global $edd_settings_export
 * @global $edd_upgrades_screen
 * @return void
 */
function edd_add_prod_update_submenu() {

	add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'install_plugins', 'edd-prod-updates', 'edd_pup_progress_html' );
}
add_action( 'admin_menu', 'edd_add_prod_update_submenu', 10 );

function edd_pup_progress_html() {
						
	if ( isset( $_GET['edd_action'] ) ) {

		if ( ! $_GET['edd_action'] == 'pup_send_ajax' ) {
			return;
		}

		ob_start(); ?>
			<div id="progress-wrap">
			<h2>Sending Emails</h2>
			<p><strong>WARNING:</strong> Do not refresh this page or close this window until sending is complete.</p>
			<?php echo submit_button('Start AJAX Test', 'primary', 'prod-updates-email-ajax-start', false);?>
			<div class="progress-wrap">
			<div class="progress">
			  <div class="progress-bar" data-complete="0"></div>
			</div>
			<div class="progress-text">
			<p><span class="progress-clock badge">00:00</span></p>
			<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">100</span> emails processed</strong> (<span class="progress-percent">0%</span>)</p>
			<p class="progress-log"></p>
			</div>
			</div>
			<div id="completion" style="display:none">
			<h3>Success!</h3>
			<p><span class="success-total">100</span> emails processed in <span class="success-time">1 hr 25 min.</span></p>
			<div class="button primary-button">Send Another Product Update</div>
			</div>
			</div>
		<?php
		echo ob_get_clean();
	}
}