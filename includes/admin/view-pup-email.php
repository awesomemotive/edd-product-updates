<?php
/**
 * View Product Update Email Page
 *
 * @since 0.9.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd-pup' ), __( 'Error', 'edd-pup' ) );
}

$bundle				= 0;
$user				= get_current_user_id();
$email_id  			= absint( $_GET['id'] );
$email     			= get_post( $email_id );
$emailmeta			= get_post_custom( $email_id );
$updated_products 	= get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );
$recipients 		= get_post_meta( $email_id, '_edd_pup_recipients', TRUE );
$filters			= isset ( $emailmeta['_edd_pup_filters'][0] ) ? maybe_unserialize( $emailmeta['_edd_pup_filters'][0] ) : null;
$queue				= edd_pup_check_queue( $email_id );
$processing 		= edd_pup_is_processing( $email_id ) ? true : false;
$autodel			= edd_get_option( 'edd_pup_auto_del' );
$dateformat 		= get_option( 'date_format' ). ' ' . get_option( 'time_format' );
$baseurl			= admin_url( 'edit.php?post_type=download&page=edd-prod-updates' );
$restarturl 		= add_query_arg( array( 'view' => 'send_pup_ajax', 'id' => $email_id, 'restart' => 1 ), admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) );
$duplicateurl		= wp_nonce_url( add_query_arg( array( 'edd_action' => 'pup_duplicate_email', 'id' => $email_id, 'redirect' => 1 ), $baseurl ), 'edd-pup-duplicate-nonce' );

// Find if any products were bundles
foreach ( $updated_products as $prod_id => $prod_name ) {
	if ( edd_is_bundled_product( $prod_id ) ) {
		$bundle++;
	}
}

switch ( strtolower( $email->post_status ) ){
		case 'publish':
			$status = __( 'Sent', 'edd-pup' );
			break;
		case 'pending':
			if ( $processing ) {
				$status = __( 'Processing', 'edd-pup' );				
			} else {
				$status = __( 'In Queue', 'edd-pup' );
			}
			break;
		case 'abandoned':
			$status = __( 'Cancelled', 'edd-pup' );
			break;
		default:
			$status = ucfirst ( $email->post_status );
}

?>
<div id="edd-pup-single-email" class="wrap">
	<h2><?php echo $email->post_title; ?></h2>
	<br>
	<a href="<?php echo $baseurl ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-pup' ); ?></a>
	<a href="<?php echo $duplicateurl ?>" class="button-primary"><?php _e( 'Send another email like this', 'edd-pup');?></a>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Product Update Email Info', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><strong><?php _e( 'Status', 'edd-pup' ); ?>:</strong> <span class="status-<?php echo $email->post_status; ?>"><?php echo $status; ?></span></p>
							<p><strong><?php _e( 'Date Sent', 'edd-pup' );?>:</strong> <?php echo mysql2date( $dateformat, $email->post_date );?></p>
							<p><strong><?php _e( 'Sent By', 'edd-pup' );?>:</strong> <?php echo get_the_author_meta( 'display_name', $email->post_author );?></p>
							<?php if ( $queue['queue'] > 0 ): ?>
							<p><strong><?php _e( 'Queued', 'edd-pup' ); ?>:</strong> <?php echo number_format( $queue['queue'] ); ?></p>
							<p><strong><?php _e( 'Processed', 'edd-pup' ); ?>:</strong> <?php echo number_format( $queue['sent'] ); ?></p>
							<?php endif;?>
							<?php if ( is_array( $recipients ) ): ?>
							<p><strong><?php _e( 'Sent', 'edd-pup' ); ?>:</strong> <?php echo number_format( $recipients['sent'] ); ?></p>
							<p><strong><?php _e( 'Unsent', 'edd-pup' ); ?>:</strong> <?php echo number_format( $recipients['queue'] ); ?></p>
							<p><strong><?php _e( 'Total Recipients', 'edd-pup' ); ?>:</strong> <?php echo number_format( $recipients['total'] ); ?></p>
							<?php else: ?>
							<p><strong><?php _e( 'Total Recipients', 'edd-pup' ); ?>:</strong> <?php echo number_format( $recipients ); ?></p>
							<?php endif; ?>
							<p><strong><?php _e( 'Updated Products', 'edd-pup' ); ?>:</strong></p>
								<ul id="updated-products" class="edd_pup_email_info_list">
								<?php foreach ( $updated_products as $id => $title ): ?>
									<li class="product"><a href="<?php echo get_edit_post_link( $id );?>" target="_blank" title="Edit <?php echo $title;?> Download"><?php echo $title; ?></a></li>
								<?php endforeach;?>
								</ul>
							<?php if ( isset( $filters ) && ( $bundle > 0 ) ):?>
									<p><strong><?php _e( 'Bundle Filters', 'edd-pup'); ?>:</strong></p>
									<ul class="edd_pup_email_info_list">
								<?php if ( $filters['bundle_1'] == 'all' ):?>
									<li class="edd_pup_email_info_item"><?php _e( 'All bundled products links included', 'edd-pup'); ?></li>
								<?php else: ?>
									<li class="edd_pup_email_info_item"><?php _e( 'Only updated bundled products links included', 'edd-pup'); ?></li>								
								<?php endif; ?>
								<?php if ( $filters['bundle_2'] == 1 ):?>
									<li class="edd_pup_email_info_item"><?php _e( 'Sent only to bundle customers', 'edd-pup'); ?></li>
								<?php endif;?>
									</ul>
							<?php endif; ?>
							<?php if ( isset( $emailmeta['_edd_pup_licensing_status'][0] ) && $emailmeta['_edd_pup_licensing_status'][0] == 'active' ) : ?>
							<p><strong><?php _e( 'EDD Software Licensing Filter On', 'edd-pup'); ?></strong></p>
							<p><em><?php _e( 'Customers receive updates to products with software licensing enabled only if they have an active software license.', 'edd-pup' ); ?></em></p>
							<?php endif; ?>
						</div>
					</div>
				</div>	
			</div>

			<?php if ( $queue['queue'] > 0 && false == $processing ) : ?>
			<div id="postbox-container-2" class="postbox-container edd-pup-view-alert">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Action Needed – Email in Send Queue', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'This email you are viewing still has messages to send and is currently not processing. Please choose to either:', 'edd-pup' ); ?></p>
							<p>1. <?php printf( __( '<a href="%s" class="%s" data-action="%s" data-email="%s" data-url="%s">Resume sending this email</a> to the remaining customers in the queue.', 'edd-pup' ), '#', 'edd-pup-queue-button', 'edd_pup_send_queue', $email_id, $restarturl ); ?></p>
							<p>2. <?php printf( __( '<a href="%s" class="%s" data-action="%s" data-email="%s" data-nonce="%s">Clear the email from the queue</a> and cancel sending this email to the customers who have not received it.', 'edd-pup' ), '#', 'edd-pup-queue-button', 'edd_pup_clear_queue', $email_id, wp_create_nonce( 'clear-queue-' . $email_id ) ); ?></p>
							<?php if ( $autodel == false ) : ?>
							<p><strong><?php printf( __('If no action is taken within 48 hours, this email will be automatically removed from the queue.</strong> (<a href="%s">Disable automatic removal on the settings page</a>)', 'edd-pup' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=emails#edd_pup_settings' ) );?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
			<?php if ( $processing ) : ?>
			<div id="postbox-container-2" class="postbox-container edd-pup-view-processing">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Email Currently Processing', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<?php if ( $email->post_author == $user ): ?>
							<p><?php _e( 'You are currently processing this email to send messages to customers of the products listed on the sidebar.', 'edd-pup' ); ?></p>
							<?php else: ?>
							<p><?php printf( __( '%s (%s) is currently processing this email to send messages to customers of the products listed on the sidebar.', 'edd-pup' ), get_the_author_meta( 'display_name', $email->post_author ), get_the_author_meta( 'user_login', $email->post_author ) ); ?></p>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
			<div id="postbox-container-3" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Product Update Email Message', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><strong><?php _e( 'From Name', 'edd-pup' ); ?>:</strong> <?php echo $emailmeta['_edd_pup_from_name'][0]; ?></p>
							<p><strong><?php _e( 'From Email', 'edd-pup' ); ?>:</strong> <?php echo $emailmeta['_edd_pup_from_email'][0]; ?></p>
							<p><strong><?php _e( 'Subject', 'edd-pup' ); ?>:</strong> <?php echo $emailmeta['_edd_pup_subject'][0]; ?></p>
							<p><strong><?php _e( 'Message', 'edd-pup' ); ?>:</strong></p>
							<div class="message-toggle-wrap">
								<button class="message-toggle active" data-message="preview"><?php _e( 'Preview', 'edd-pup' ); ?></button> 
								<button class="message-toggle" data-message="original"><?php _e( 'Original', 'edd-pup' );?></button>
							</div>
							<div id="message-preview">
								<?php echo $emailmeta['_edd_pup_message'][0]; ?>
							</div>
							<div id="message-original" style="display:none;">
								<?php echo wpautop( $email->post_content ); ?>	
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="view-buttons">
		<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-pup' ); ?></a>
	</div>
</div>