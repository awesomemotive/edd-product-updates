<?php
/**
 * View sent email page
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd-pup' ), __( 'Error', 'edd-pup' ) );
}

$email_id  = absint( $_GET['id'] );
$email     = get_post( $email_id );
$emailmeta = get_post_custom( $email_id );
$updated_products = get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );
$recipients = get_post_meta( $email_id, '_edd_pup_recipients', TRUE );
$queue = edd_pup_check_queue( $email_id );
$processing = edd_pup_is_processing() ? true : false;

switch ( strtolower( $email->post_status ) ){
		case 'publish':
			$status = __( 'Sent', 'edd-pup' );
			break;
		case 'pending':
			$status = __( 'In Queue', 'edd-pup' );
			break;
		case 'abandoned':
			$status = __( 'Cancelled', 'edd-pup' );
			break;
		default:
			$status = ucfirst ( $email->post_status );
}

?>
<div id="edd-pup-admin-email" class="wrap">
	<h2><?php echo $email->post_title; ?></h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Product Update Email Info', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><strong><?php _e( 'Status', 'edd-pup' ); ?>:</strong> <span class="status-<?php echo $email->post_status; ?>"><?php echo $status; ?></span></p>							
							<?php if ( $queue['queue'] > 0 ): ?>
							<p><strong><?php _e( 'Queued', 'edd-pup' ); ?>:</strong> <?php echo $queue['queue']; ?></p>
							<p><strong><?php _e( 'Processed', 'edd-pup' ); ?>:</strong> <?php echo $queue['sent']; ?></p>
							<?php endif;?>
							<?php if ( is_array( $recipients ) ): ?>
							<p><strong><?php _e( 'Sent', 'edd-pup' ); ?>:</strong> <?php echo $recipients['sent']; ?></p>
							<p><strong><?php _e( 'Unsent', 'edd-pup' ); ?>:</strong> <?php echo $recipients['queue']; ?></p>
							<p><strong><?php _e( 'Total Recipients', 'edd-pup' ); ?>:</strong> <?php echo $recipients['total']; ?></p>
							<?php else: ?>
							<p><strong><?php _e( 'Total Recipients', 'edd-pup' ); ?>:</strong> <?php echo $recipients; ?></p>
							<?php endif; ?>
							<p><strong><?php _e( 'Updated Products', 'edd-pup' ); ?>:</strong></p>
								<ul id="updated-products">
								<?php foreach ( $updated_products as $id => $title ): ?>
									<li class="product"><a href="<?php echo get_edit_post_link( $id );?>" target="_blank" title="Edit <?php echo $title;?> Download"><?php echo $title; ?></a></li>
								<?php endforeach;?>
								</ul>
						</div>
					</div>
				</div>	
			</div>

			<?php if ( $queue['queue'] > 0 ) : ?>
			<div id="postbox-container-2" class="postbox-container edd-pup-view-alert">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Action Needed – Email in Send Queue', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'This email you are viewing still has messages to send and is currently not processing. Please choose to either:', 'edd-pup' ); ?></p>
							<p>1. <?php printf( __( '<a href="%s">Resume sending this email</a> to the remaining customers in the queue.', 'edd-pup' ), '#' ); ?></p>
							<p>2. <?php printf( __( '<a href="%s" class="%s" data-action="%s" data-email="%s">Clear the email from the queue</a> and cancel sending this email to the customers who have not received it.', 'edd-pup' ), '#', 'edd-pup-queue-button', 'edd_pup_clear_queue', $email_id ); ?></p>
							<p><strong><?php _e( 'If no action is taken within 48 hours, this email will be automatically removed from the queue.', 'edd-pup' ); ?></strong></p>
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
							<p><?php _e( 'This email is currently processing and being used to send messages to customers of the products listed on the sidebar.', 'edd-pup' ); ?></p>
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
							<div id="message-preview">
								<?php echo edd_apply_email_template( $emailmeta['_edd_pup_message'][0], null, null ); ?>
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