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

?>
<form id="edd-add-pup-email" action="" method="POST">
<h2><?php _e( 'Edit Product Update Email', 'edd-pup' ); ?></h2>
<br>
<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-pup' ); ?></a>
	<?php do_action( 'edd_add_receipt_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="title"><?php _e( 'Email Name', 'edd-pup' ); ?></label>
				</th>
				<td>
					<label for="title"><?php _e( 'Email Name', 'edd-pup' ); ?></label>
					<p><input type="text" class="regular-text" name="title" id="title" value="<?php echo $email->post_title;?>" size="30" /></p>
					<p class="description"><?php _e( 'Used for internal use only to help organize product updates. Customers will not see this. (example: "2nd Edition eBook Update")' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="products"><?php _e( 'Choose products being updated', 'edd-pup' ) ; ?></label>
				</th>
				<td>
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
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<?php _e( 'Recipients', 'edd-pup' ); ?>
				</th>
				<td>
					<?php printf( __( '%s customers will receive this email', 'edd-pup' ), $recipients ); ?>
					<input type="hidden" name="recipients" value="<?php echo $recipients; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="from_name"><?php _e( 'From Name', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="from_name" id="from_name" value="<?php echo $emailmeta['_edd_pup_from_name'][0]; ?>" size="30" /></p>
					<p class="description"><?php _e( 'The name customers will see the product update coming from.' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="from_email"><?php _e( 'From Email', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="from_email" id="from_email" value="<?php echo $emailmeta['_edd_pup_from_email'][0]; ?>" size="30" /></p>
					<p class="description"><?php _e( 'The email address customers will receive the product update from.' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="subject"><?php _e( 'Email Subject', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="widefat" name="subject" id="subject" value="<?php echo $email->post_excerpt;?>" size="30" /></p>
					<p class="description"><?php _e( 'Enter the email subject line for this product update. All template tags are available for use.' , 'edd-pup' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top">
					<label for="message"><?php _e( 'Email Message', 'edd-ppe' ); ?></label>
				</th>
				<td>
					<?php wp_editor( $email->post_content, 'message' ); echo '<p><strong>Available template tags:</strong></p><p>' . edd_get_emails_tags_list() . '</p>'; ?>
				</td>
			</tr>
		
		</tbody>
	</table>
	<?php do_action( 'edd_add_receipt_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="edd-action" value="edit_pup_email" />
		<input type="hidden" name="email-id" value="<?php echo absint( $_GET['id'] ); ?>" />
		<input type="hidden" name="edd-pup-email" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-prod-updates&edd-action=edit_pup_email&id='.$email_id ) ); ?>" />
		<input type="hidden" name="edd-pup-email-add-nonce" value="<?php echo wp_create_nonce( 'edd-pup-email-add-nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Save Email Changes', 'edd-pup' ); ?>" class="button-primary" />
	</p>
</form>