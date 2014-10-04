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

function edd_pup_notices() {
	$style = 'updated';
	$supporturl = 'https://easydigitaldownloads.com/support/';
	
	if ( $_GET['post_type'] != 'download' && $_GET['page'] != 'edd-prod-updates' ) {
		return;
	}
	
	if ( isset( $_GET['edd_pup_notice'] ) ) {
		
		switch ( $_GET['edd_pup_notice'] ) {
			case 1:
				$message = __( 'Product Update Email changes saved successfully.', 'edd-pup' );
				break;
			case 2:
				$message = sprintf( __( 'Product Update Email changes <strong>did not save successfully</strong>. If the issue continues, please <a href="%s" target="_%s">contact Easy Digital Downloads support</a> for help.', 'edd-pup' ), $supporturl, '_blank' );
				$style = 'error';
				break;
			case 3:
				$message = sprintf( __( 'Email was <strong>not deleted successfully</strong>. If the issue continues, please <a href="%s" target="_%s">contact Easy Digital Downloads support</a> for help.', 'edd-pup' ), $supporturl, '_blank' );
				$style = 'error';
				break;
			case 4:
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
add_action( 'admin_notices', 'edd_pup_notices', 10);