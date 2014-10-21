<?php
/**
 * Plugin Name: Easy Digital Downloads - Product Update Emails
 * Description: Batch send product update emails to EDD customers
 * Author: Evan Luzi
 * Author URI: http://evanluzi.com
 * Version: 0.9.3.1
 * Text Domain: edd-pup
 *
 * @package EDD_PUP
 * @author Evan Luzi
 * @version 0.9.3.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Includes
//require( 'inc/admin/submenu.php');
require( 'inc/ajax.php');
require( 'inc/misc-actions.php');
require( 'inc/misc-functions.php');
require( 'inc/notices.php');
require( 'inc/payment.php');
require( 'inc/post-types.php');
require( 'inc/tags.php');

/**
 * Register custom database table name into $wpdb global
 * 
 * @access public
 * @return void
 * @since 0.9.2
 */
function edd_pup_register_table() {
    global $wpdb;
    $wpdb->edd_pup_queue = "{$wpdb->prefix}edd_pup_queue";
    
    update_option( 'edd_pup_version', '0.9.3.1' );
}
add_action( 'init', 'edd_pup_register_table', 1 );
add_action( 'switch_blog', 'edd_pup_register_table' );


/**
 * Create custom database table for email send queue
 * 
 * @access public
 * @return void
 * @since 0.9.2
 */
function edd_pup_create_tables() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
	global $wpdb;
	global $charset_collate;
	
	edd_pup_register_table();
	
	$sql_create_table = "CREATE TABLE {$wpdb->edd_pup_queue} (
          eddpup_id bigint(20) unsigned NOT NULL auto_increment,
          customer_id bigint(20) unsigned NOT NULL default '0',
          email_id bigint(20) unsigned NOT NULL default '0',
          products longtext NOT NULL,
          sent bool NOT NULL default '0',
          sent_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
          PRIMARY KEY  (eddpup_id),
          KEY customer_id (customer_id)
     ) $charset_collate; ";
 
	 dbDelta( $sql_create_table );
}
register_activation_hook( __FILE__, 'edd_pup_create_tables' );

/**
 * Create wp_cron job for auto-clear of queue
 * 
 * @access public
 * @return void
 * @since 0.9.3.1
 */
function edd_pup_create_cron_schedule(){

	if( wp_next_scheduled( 'edd_pup_cron_clear_queue' ) == false ){
	  
		wp_schedule_event( time(), 'daily', 'edd_pup_cron_clear_queue' );  
	  
	}
}
register_activation_hook( __FILE__, 'edd_pup_create_cron_schedule' );


/**
 * Checks the email queue every day for emails older than 48 hours and clears them.
 * 
 * @access public
 * @return void
 * @since 0.9.3.1
 */
function edd_pup_cron_clear(){
	global $edd_options;
	
	if ( $edd_options['edd_pup_auto_del'] == false ) {
	
		global $wpdb;
		
		$emails = $wpdb->get_results( "SELECT DISTINCT email_id FROM $wpdb->edd_pup_queue WHERE sent = 0 AND HOUR( TIMEDIFF( NOW(), sent_date)) >= 48" , ARRAY_A );
		
		if ( !empty( $emails ) ) {
			
			foreach ( $emails as $email ) {
				
				$recipients = edd_pup_check_queue( $email['email_id'] );
				
				$query = $wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $email['email_id'] ), array( '%d' ) );
				
				if ( is_numeric( $query ) ) {
					$post = wp_update_post( array( 'ID' => $email['email_id'], 'post_status' => 'abandoned' ) );
					update_post_meta ( $post, '_edd_pup_recipients', $recipients );
				}
				
			}
		}
	}
}
add_action( 'edd_pup_cron_clear_queue', 'edd_pup_cron_clear' );


/**
 * Clear wp_cron schedule on plugin deactivation
 * 
 * @access public
 * @return void
 * @since 0.9.3.1
 */
function edd_pup_delete_cron_schedule(){

	wp_clear_scheduled_hook( 'edd_pup_cron_clear_queue' );
	
}
register_deactivation_hook( __FILE__, 'edd_pup_delete_cron_schedule' );


/**
 * Removes ALL data on plugin uninstall including custom db table,
 * all transients, and all saved email sends (custom post type)
 * 
 * @access public
 * @return void
 * @since 0.9.2
 */
function edd_pup_uninstall(){
    global $wpdb;
    
    //Remove our table (if it exists)
    $wpdb->query("DROP TABLE IF EXISTS $wpdb->edd_pup_queue");
    
    //Remove all email posts
    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'edd_pup_email'");
    
    //Remove all custom metadata from postmeta table
    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key IN ( '_edd_pup_from_name' , '_edd_pup_from_email' , '_edd_pup_subject' , '_edd_pup_message' , '_edd_pup_headers' , '_edd_pup_updated_products' )");
         
    //Remove the database version
    delete_option('wptuts_activity_log_version');
 
    //Remove any leftover transients
	delete_transient( 'edd_pup_email_id' );
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_subject' );	
	delete_transient( 'edd_pup_email_body_header' );
	delete_transient( 'edd_pup_email_body_footer' );
	delete_transient( 'edd_pup_preview_email' );
	delete_transient( 'edd_pup_sending_email' );
}
register_uninstall_hook(__FILE__,'edd_pup_uninstall');

/**
 * Register and enqueue necessary JS and CSS files
 * 
 * @access public
 * @return void
 * @since 0.9
 */
function edd_pup_scripts() {
        wp_register_script( 'edd_prod_updates_js', plugins_url(). '/edd-product-updates/assets/edd-pup.js', false, '1.0.0' );
        wp_enqueue_script( 'edd_prod_updates_js' );

        wp_register_style( 'edd_prod_updates_css', plugins_url(). '/edd-product-updates/assets/edd-pup.css', false, '1.0.0' );
        wp_enqueue_style( 'edd_prod_updates_css' );
}
add_action( 'admin_enqueue_scripts', 'edd_pup_scripts' );

/**
 * Add Product Update Settings to EDD Settings -> Emails
 * 
 * @access public
 * @param mixed $edd_settings
 * @return array EDD Settings
 * @since 0.9
 */
function edd_pup_settings ( $edd_settings ) {
        $products = array();

        $downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

	    if ( !empty( $downloads ) ) {
	        foreach ( $downloads as $download ) {
	        	
	            $products[ $download->ID ] = get_the_title( $download->ID );

	        }
	    }

        $settings = array(
            array(
                'id' => 'edd_pup_settings_head',
                'name' => '<span id="edd_pup_settings"><strong>' . __( 'Product Updates Email Settings', 'edd-pup' ) . '</strong></span>',
                'desc' => __( 'Configure the Product Updates Email settings', 'edd-pup' ),
                'type' => 'header'
            ),
            array(
                'id' => 'edd_pup_auto_del',
                'name' => __( 'Disable automatic queue removal', 'edd-pup' ),
                'desc' => __( 'When checked, emails will remain in the queue indefinitely instead of being removed after 48 hours.', 'edd-pup' ),
                'type' => 'checkbox'
            )
       );
	            
       if ( is_plugin_active('edd-software-licensing/edd-software-licenses.php' ) ) {
       
        $settings[] =
            array(
                'id' => 'edd_pup_license',
                'name' => __( 'Easy Digital Downloads Software Licensing Integration', 'edd-pup' ),
                'desc' => __( 'If enabled, only customers with active software licenses will receive update emails', 'edd-pup' ),
                'type' => 'checkbox'
            );
	            
        }
        
        $settings2 = array(
			array(
				'id' => 'edd_pup_template',
				'name' => __( 'Email Template', 'edd-pup' ),
				'desc' => __( 'Choose a template to be used for the product update emails.', 'edd-pup' ),
				'type' => 'select',
				'options' => edd_get_email_templates()
			)
		);
		
        return array_merge( $edd_settings, $settings, $settings2 );
}
add_filter( 'edd_settings_emails', 'edd_pup_settings' );

/**
 * Adds Product Updates admin submenu page under the Downloads menu
 *
 * @since 0.9.2
 * @return void
 */
function edd_add_prod_update_submenu() {

	add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'install_plugins', 'edd-prod-updates', 'edd_pup_admin_page' );

}
add_action( 'admin_menu', 'edd_add_prod_update_submenu', 10 );


/**
 * Creates and filters the admin pages for Product Updates submenu
 * 
 * @access public
 * @return void
 */
function edd_pup_admin_page() {
	
	if ( isset( $_GET['view'] ) && $_GET['view'] == 'edit_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
		require 'inc/admin/edit-pup-email.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'view_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
		require 'inc/admin/view-pup-email.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'send_pup_ajax' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {	
		require 'inc/admin/popup.php';
		
	} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'add_pup_email' ) {	
		require 'inc/admin/add-pup-email.php';
		
	} else {
		require_once ( 'inc/admin/class-edd-pup-table.php' );
		
		$pup_table = new EDD_Pup_Table();
		$pup_table->prepare_items();
			?>

			<div class="wrap edd-pup-list">	
				<h2><?php _e( 'Product Update Emails', 'edd-pup' ); ?><a href="<?php echo add_query_arg( array( 'view' => 'add_pup_email', 'edd-message' => false ) ); ?>" class="add-new-h2"><?php _e( 'Send New Email', 'edd-pup' ); ?></a></h2>
				<?php do_action( 'edd_pup_page_top' ); ?>
				<form id="edd-pup-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ); ?>">
					<input type="hidden" name="post_type" value="download" />
					<input type="hidden" name="page" value="edd-prod-updates" />
					<?php $pup_table->views() ?>
					<?php $pup_table->display() ?>
				</form>
				<?php do_action( 'edd_pup_page_bottom' ); ?>
			</div>
		<?php
	}
}

/**
 * Helper function to retrieve template selected for product update emails
 * 
 * @access public
 * @return void
 */
function edd_pup_template(){
	global $edd_options;
	
	return $edd_options['edd_pup_template'];
}