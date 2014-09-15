<?php
/**
 * Edit email page
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$email_id  = absint( $_GET['id'] );
$email     = get_post( $email_id );
$emailmeta = get_post_custom( $email_id );
$updated_products = get_post_meta( $email_id, '_edd_pup_updated_products', TRUE );
$recipients = edd_pup_customer_count( $email_id, $updated_products );
$products = edd_pup_get_all_downloads();
$tags = edd_get_email_tags();

?>
<form id="edd-pup-email-edit" action="" method="POST">
<div id="edd-pup-admin-email" class="wrap">
<?php do_action( 'edd_add_receipt_form_top' ); ?>
<h2><?php _e( 'Edit Product Update Email', 'edd-pup' ); ?></h2>
<br>
<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-pup' ); ?></a>
	<div id="poststuff">
		<div id="edd-dashboard-widgets-wrap">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-1" class="postbox-container">
				<!-- actions -->
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div id="submitdiv" class="postbox">
						<h3 class="hndle"><span><?php _e( 'Email Actions', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<div class="submitbox" id="submitpost">
								<div id="minor-publishing-actions">
									<div id="save-action">
										<?php submit_button('Save Changes', 'secondary', 'edd-pup-save-email-changes', false);?>
									</div>
									<div id="preview-action">
										<a href="javascript:void(0);" id="edd-pup-open-preview" class="button-secondary" title="<?php _e( 'Product Update Email Preview', 'edd' ); ?> "><?php _e( 'Preview Email', 'edd' ); ?></a>
									</div>
									<div class="clear"></div>
								</div>
								<div id="test-action">
									<p><strong><?php _e( 'Send Test Email To' , 'edd-pup' );?>:</strong></p>
									<input type="text" class="test-email" name="test-email" id="test-email" placeholder="name@email.com" size="10" />
									<p class="description"><?php _e( 'Use a comma between multiple emails.' , 'edd-pup' ); ?></p>
									<a href="javascript:void(0);" id="edd-pup-send-test" class="button-secondary" title="<?php _e( 'Product Update Email Preview', 'edd' ); ?> "><?php _e( 'Send Test Email', 'edd-pup' ); ?></a>
									<input type="hidden" name="edd-pup-test-nonce" value="<?php echo wp_create_nonce( 'edd-pup-test-nonce' ); ?>" />
								</div>
								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete deletion" href="<?php echo wp_nonce_url( add_query_arg( 'edd_action' , 'pup_delete_email' ), 'edd-pup-delete-nonce' ); ?>" onclick="var result=confirm(<?php _e( 'Are you sure you want to permanently delete this email?', 'edd-pup' ); ?>);return result;"><span class="delete"><?php _e( 'Delete Email' , 'edd-pup'); ?></span></a>
									</div>
									<div id="publishing-action">
										<?php submit_button('Send Update Email', 'primary', 'send-prod-updates', false);?><span class="edd-pu-spin spinner"></span>
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- tags -->
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<div id="submitdiv" class="postbox">
						<h3 class="hndle"><span><?php _e( 'Template Tags', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<div class="tag-list">
							<?php foreach ( $tags as $tag): ?>
									<p class="template-tag"><strong>{<?php echo $tag['tag'];?>}</strong></p>
									<p class="tag-description"><?php echo $tag['description'];?></p>
							<?php endforeach;?>
							</div>
						</div>
					</div>
				</div>
			</div>	
			<div id="postbox-container-2" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Email Setup', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<strong><?php _e( 'Email Name', 'edd-pup' ); ?>:</strong>
							<input type="text" class="regular-text" name="title" id="title" value="<?php echo $email->post_title;?>" size="30" />
							<p class="description"><?php _e( 'For internal use only to help organize product updates – i.e. "2nd Edition eBook Update." Customers will not see this.' , 'edd-pup' ); ?></p>
							
							<!-- products -->
							<strong><?php _e( 'Choose products being updated', 'edd-pup' ) ; ?>:</strong>
							<br>
							<?php foreach ( $products as $product_id => $title ):
								if ( array_key_exists( $product_id, $updated_products ) ) {
									$checked = 'checked="checked"';
								} else { $checked = ''; }
							?>
							<input name="product[<?php echo $product_id; ?>]" id="product[<?php echo $product_id; ?>]" type="checkbox" value="<?php echo $title; ?>" <?php echo $checked; ?>>
							<label for="product[<?php echo $product_id; ?>]"><?php echo $title; ?></label>
							<br>
							<?php endforeach; ?>
							<p class="description"><?php _e( 'Select which products and its customers you wish to update with this email', 'edd-pup' ); ?></p>
							
							<!-- recipients -->
								<p><strong><?php _e( 'Recipients', 'edd-pup' ); ?>:</strong> <?php printf( __( '%s customers will receive this email', 'edd-pup' ), $recipients ); ?></p>
								<input type="hidden" name="recipients" value="<?php echo $recipients; ?>" />
						</div>
					</div>
					
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Product Update Email Message', 'edd-pup' ); ?></span></h3>
						<div class="inside">
							<!-- from name  -->
							<strong><?php _e( 'From Name', 'edd-pup' ); ?>:</strong>
							<input type="text" class="regular-text" name="from_name" id="from_name" value="<?php echo $emailmeta['_edd_pup_from_name'][0]; ?>" />
							<p class="description"><?php _e( 'The name customers will see the product update coming from.' , 'edd-pup' ); ?></p>
							<!-- from email -->
							<strong><?php _e( 'From Email', 'edd-pup' ); ?>:</strong>
							<input type="text" class="regular-text" name="from_email" id="from_email" value="<?php echo $emailmeta['_edd_pup_from_email'][0]; ?>" />
							<p class="description"><?php _e( 'The email address customers will receive the product update from.' , 'edd-pup' ); ?></p>
							<!-- subject    -->
							<strong><?php _e( 'Subject', 'edd-pup' ); ?>:</strong>
							<input type="text" class="widefat" name="subject" id="subject" value="<?php echo $email->post_excerpt;?>" size="30" />
							<p class="description"><?php _e( 'Enter the email subject line for this product update. Template tags can be used (see sidebar).' , 'edd-pup' ); ?></p>
							
							<!-- message    -->
							<?php wp_editor( $email->post_content, 'message' ); ?>
						</div>
					</div>				
					
				</div>
			</div>
		</div>
	</div>
	</div>
	<?php do_action( 'edd_add_receipt_form_bottom' ); ?>
	<div class="submit">
		<input type="hidden" name="edd-action" value="edit_pup_email" />
		<input type="hidden" name="email-id" value="<?php echo absint( $_GET['id'] ); ?>" />
		<input type="hidden" name="edd-pup-email" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-prod-updates&view=edit_pup_email&id='.$email_id ) ); ?>" />
		<input type="hidden" name="edd-pup-email-add-nonce" value="<?php echo wp_create_nonce( 'edd-pup-email-add-nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Save Email Changes', 'edd-pup' ); ?>" class="button-primary" />
	</div>
	<div class="edit-buttons">
		<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-pup' ); ?></a>
	</div>
	</div>
</form>