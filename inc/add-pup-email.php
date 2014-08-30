<?php
/**
 * View sent email page
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$products = array();
$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

if ( !empty( $downloads ) ) {
    foreach ( $downloads as $download ) {
    	
        $products[ $download->ID ] = get_the_title( $download->ID );

    }
}

?>
<h2><?php _e( 'Create New Product Update Email', 'edd-pup' ); ?></h2>
<br>
<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-receipts' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd-ppe' ); ?></a>
<form id="edd-add-pup-email" action="" method="POST">
	<?php do_action( 'edd_add_receipt_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="title"><?php _e( 'Email Name', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="title" id="title" value="" size="30" /></p>
					<p class="description"><?php _e( 'Used for internal use only to help organize product updates. Customers will not see this. (example: "2nd Edition eBook Update")' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="products"><?php _e( 'Choose products being updated', 'edd-pup' ) ; ?></label>
				</th>
				<td>
					<?php foreach ( $products as $product_id => $title ): ?>
						<input name="product[<?php echo $product_id; ?>]" id="product[<?php echo $product_id; ?>]" type="checkbox" value="<?php echo $title; ?>">
						<label for="product[<?php echo $product_id; ?>]"><?php echo $title; ?></label>
						<br>
					<?php endforeach; ?>
					
					<p class="description"><?php _e( 'Select which products and its customers you wish to update with this email', 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="from_name"><?php _e( 'From Name', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="from_name" id="from_name" value="" size="30" /></p>
					<p class="description"><?php _e( 'The name customers will see the product update coming from.' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="from_email"><?php _e( 'From Email', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="from_email" id="from_email" value="" size="30" /></p>
					<p class="description"><?php _e( 'The email address customers will receive the product update from.' , 'edd-pup' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="subject"><?php _e( 'Email Subject', 'edd-pup' ); ?></label>
				</th>
				<td>
					<p><input type="text" class="widefat" name="subject" id="subject" value="" size="30" /></p>
					<p class="description"><?php _e( 'Enter the email subject line for this product update. All template tags are available for use.' , 'edd-pup' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top">
					<label for="message"><?php _e( 'Email Message', 'edd-ppe' ); ?></label>
				</th>
				<td>
					<?php wp_editor( __( 'Enter the message to your customers here. All Easy Digital Downloads template tags are available (listed below) including {updated_products}, {updated_products_links}, and {unsubscribe_link}.', 'edd-pup' ), 'message' ); echo '<p><strong>Available template tags:</strong></p><p>' . edd_get_emails_tags_list() . '</p>'; ?>
				</td>
			</tr>
		
		</tbody>
	</table>
	<?php do_action( 'edd_add_receipt_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="view" value="add_pup_email" />
		<input type="hidden" name="edd-pup-email" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ); ?>" />
		<input type="hidden" name="edd-pup-email-add-nonce" value="<?php echo wp_create_nonce( 'edd-pup-email-add-nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Save Email', 'edd-pup' ); ?>" class="button-primary" />
	</p>
</form>