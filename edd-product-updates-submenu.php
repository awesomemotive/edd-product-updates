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
	global $edd_prod_updates_page;

	$edd_prod_updates_page = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'install_plugins', 'edd-prod-updates', 'edd_prod_updates_page' );
}
add_action( 'admin_menu', 'edd_add_prod_update_submenu', 10 );

function edd_prod_update_register_settings(){
  register_setting( 'edd_prod_updates_settings', 'new_option_name' );
}

function edd_prod_updates_page() {
			
    $products = array();

    $downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

    if ( !empty( $downloads ) ) {
        foreach ( $downloads as $download ) {
        
        	if(edd_get_download_type( $download->ID ) == 'bundle') {
        	
            	$bundleprods = edd_get_bundled_products( $download->ID );
            	
            		foreach($bundleprods as $bundleprod){
						$products2[ $bundleprod ] = 'bund'. get_the_title( $bundleprod );		            		
            		}
        	}
        	
            $products[ $download->ID ] = get_the_title( $download->ID );
        }
    }
edd_get_registered_settings();
	ob_start(); ?>
	
	<form method="post" action="options.php">
		<div class="wrap" id="edd-prod-update-emails">
		<p><?php var_dump($products);?></p>
		<p><?php var_dump($products2);?></p>
		<h3>Product Update Message</h3>
		<?php wp_editor('Default', 'prod_update_editor', 'edd_prod_update_emails_message');?>
		<p>Enter the email that is sent to users alerting them of the product updates. HTML is accepted. Available template tags:<br/><?php echo edd_get_emails_tags_list()?></p>
		</div>
		<?php submit_button(); ?>
	</form>
	<?php
	echo ob_get_clean();
}

/* function edd_prod_updates_page() {
			
    $products = array();

    $downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

    if ( !empty( $downloads ) ) {
        foreach ( $downloads as $download ) {
            $products[ $download->ID ] = get_the_title( $download->ID );
        }
    }
        
	ob_start(); ?>
	
	<form method="post" action="options.php">
		<div class="wrap" id="edd-prod-update-emails">
		<h3>Product Update Message</h3>
		<?php wp_editor($value, 'prod_update_editor', 'edd_prod_update_emails_message');?>
		<p>Enter the email that is sent to users alerting them of the product updates. HTML is accepted. Available template tags:<br/><?php echo edd_get_emails_tags_list()?></p>
		</div>
		<?php submit_button(); ?>
	</form>
	<?php
	echo ob_get_clean();
} */