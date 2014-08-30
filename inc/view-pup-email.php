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
$email_data = get_post_custom( $email_id );
$updated_products = get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );

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
							<p><strong><?php _e( 'Status', 'edd-pup' ); ?>:</strong> <span class="status-publish">Sent</span></p>
							<p><strong><?php _e( 'Recipients', 'edd-pup' ); ?>:</strong> 1,000</p>
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
			<div id="postbox-container-2" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Product Update Email Message', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<p><strong><?php _e( 'From Name', 'edd-pup' ); ?>:</strong> <?php echo $email_data['_edd_pup_from_name'][0]; ?></p>
							<p><strong><?php _e( 'From Email', 'edd-pup' ); ?>:</strong> <?php echo $email_data['_edd_pup_from_email'][0]; ?></p>
							<p><strong><?php _e( 'Subject', 'edd-pup' ); ?>:</strong> <?php echo $email_data['_edd_pup_subject'][0]; ?></p>
							<p><strong><?php _e( 'Message', 'edd-pup' ); ?>:</strong></p>
							<div id="message-preview">
								<?php echo edd_apply_email_template( $email_data['_edd_pup_message'][0], null, null ); ?>
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