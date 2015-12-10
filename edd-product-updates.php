<?php
/**
 * Plugin Name:     Easy Digital Downloads - Product Updates
 * Description:     Batch send product update emails to EDD customers
 * Version:         1.2.1
 * Author:          Evan Luzi
 * Author URI:      http://www.evanluzi.com/
 * Text Domain:     edd-pup
 *
 * @package         EDD\ProductUpdates
 * @author          Evan Luzi
 * @copyright       Copyright (c) 2014/2015
 *
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Product_Updates' ) ) {

    /**
     * Main EDD_Product_Updates class
     *
     * @since       1.0.0
     */
    class EDD_Product_Updates {

        /**
         * @var         EDD_Product_Updates $instance The one true EDD_Product_Updates
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Product_Updates
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Product_Updates();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_Product_Updates_VER', '1.2.1' );

            // Plugin path
            define( 'EDD_Product_Updates_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_Product_Updates_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once EDD_Product_Updates_DIR . 'includes/ajax.php';
			require_once EDD_Product_Updates_DIR . 'includes/misc-actions.php';
			require_once EDD_Product_Updates_DIR . 'includes/misc-functions.php';
			require_once EDD_Product_Updates_DIR . 'includes/notices.php';
			require_once EDD_Product_Updates_DIR . 'includes/payment.php';
			require_once EDD_Product_Updates_DIR . 'includes/post-types.php';
			require_once EDD_Product_Updates_DIR . 'includes/tags.php';
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Register settings
            add_filter( 'edd_settings_emails', array( $this, 'settings' ), 1 );

            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'EDD Product Updates', EDD_Product_Updates_VER, 'Evan Luzi' );
            }
            
            // Add submenu page
            add_action( 'admin_menu', array( $this, 'add_submenu' ), 10 );
            
            // Enqueue JS and CSS files
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
        }
        
		/**
		 * Adds Product Updates admin submenu page under the Downloads menu
		 *
		 * @access		public
		 * @since		0.9.2
		 * @return		void
		 */
		public function add_submenu() {
			add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Email Product Updates', 'edd' ), __( 'Product Updates', 'edd' ), 'manage_shop_settings', 'edd-prod-updates', array( $this, 'admin_page' ) );			
		}

		/**
		 * Creates and filters the admin pages for Product Updates submenu
		 *
		 * @access		public
		 * @since		0.9.2
		 * @return		void
		 */		
		public function admin_page() {
			if ( isset( $_GET['view'] ) && $_GET['view'] == 'edit_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
				require 'includes/admin/edit-pup-email.php';
				
			} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'view_pup_email' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
				require 'includes/admin/view-pup-email.php';
				
			} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'send_pup_ajax' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {	
				require 'includes/admin/popup.php';
				
			} else if ( isset( $_GET['view'] ) && $_GET['view'] == 'add_pup_email' ) {	
				require 'includes/admin/add-pup-email.php';
				
			} else {
				require_once ( 'includes/admin/class-edd-pup-table.php' );
				
				$pup_table = new EDD_Pup_Table();
				$pup_table->prepare_items();
					?>
		
					<div class="wrap edd-pup-list">	
						<h2><?php _e( 'Product Updates', 'edd-pup' ); ?><a href="<?php echo add_query_arg( array( 'view' => 'add_pup_email', 'edd-message' => false ), admin_url( 'edit.php?post_type=download&page=edd-prod-updates' ) ); ?>" class="add-new-h2"><?php _e( 'Add New Update Email', 'edd-pup' ); ?></a></h2>
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
		 * Register and enqueue necessary JS and CSS files as well as localize JS text
		 *
		 * @access		public
		 * @since		0.9
		 * @return		void
		 */			
		public function scripts() {
			// Localization of text used inside Javascript file
			$l18njs = array(
			
				// Confirm messages		
				'c1' => __( 'Are you sure you wish to continue clearing the queue?', 'edd-pup' ),
				
				// Alert messages		
				'a1' => __( 'Could not process emails. Please try again.', 'edd-pup' ),
				'a2' => __( 'Please enter a valid email address under "From Email."', 'edd-pup' ),		
				'a3' => __( 'Please choose at least one product whose customers will receive this email update.', 'edd-pup' ),
				'a4' => __( 'An issue occurred when attempting to send the email messages. Please try again later or contact support at https://easydigitaldownloads.com/support', 'edd-pup' ),
				'a5' => __( 'An issue occurred when preparing your email messages to send. Please try again later or contact support at https://easydigitaldownloads.com/support', 'edd-pup' ),
				'a6' => __( 'An issue occurred when attempting to start the email send. Please try again or contact support at https://easydigitaldownloads.com/support', 'edd-pup' ),
				'a7' => __( 'Invalid response received from server. Please try again or contact support at https://easydigitaldownloads.com/support.', 'edd-pup' ),
				'a8' => __( 'All of your emails have sent successfully, however, an issue occurred while finishing your email send.', 'edd-pup' ),
				'a9' => __( 'Unable to clear the queue. Please try again or contact support at https://easydigitaldownloads.com/support', 'edd-pup' ),
				'a10' => __( 'Test email successfully sent.', 'edd-pup' ),
				'a11' => __( 'The WordPress account you are logged into is already sending an email and cannot process multiple emails at once. Please pause the email your account is currently sending or wait for it to finish before attempting to send another.', 'edd-pup' ),
				'a12' => __( 'Please choose at least one product bundle or turn off the "Send only to bundle customers" option.', 'edd-pup' ),
				'a13' => __( 'With these settings, zero customers will receive this email. If you have software licensing integration enabled, all of your licenses may be expired for the products chosen.', 'edd-pup' ),
				
				// Status messages for sending popup	
				's1' => __( 'Sending emails.', 'edd-pup' ),
				's2' => __( 'Sending paused.', 'edd-pup' ),
				's3' => __( 'Preparing emails to send. ', 'edd-pup' ),
				's4' => __( ' emails added to the queue so far.', 'edd-pup' ),
				's5' => __( 'Attempting to re-establish connection with the server.', 'edd-pup' ),
				's6' => __( 'Connection re-established. Resuming email send.', 'edd-pup' ),
				's7' => sprintf( __( 'Trouble communicating with the server. Retrying in %s seconds.', 'edd-pup' ), '<span class="count">15</span>' ),
				's8' => __( 'Preparing emails paused. ', 'edd-pup' ),
		
				// Values for popup action button	
				'v1' => __( 'Start Sending', 'edd-pup' ),
				'v2' => __( 'Pause', 'edd-pup' ),
				'v3' => __( 'Resume', 'edd-pup' ),
				'v4' => __( 'Finished', 'edd-pup' ),
				
				// Values for bundle filters JS
				'bf1' => __( 'Show Bundle Filters', 'edd-pup' ),
				'bf2' => __( 'Hide Bundle Filters', 'edd-pup' )
				
			);
			
			// Plugin Javascript
		    wp_register_script( 'edd-pup-js', plugin_dir_url( __FILE__ ). 'assets/js/edd-pup.min.js', false, EDD_Product_Updates_VER );
		    wp_enqueue_script( 'edd-pup-js' );
		    wp_localize_script( 'edd-pup-js', 'eddPup', $l18njs );
		
			// Plugin CSS
		    wp_register_style( 'edd-pup-css', plugin_dir_url( __FILE__ ). 'assets/css/edd-pup.min.css', false, EDD_Product_Updates_VER );
		    wp_enqueue_style( 'edd-pup-css' );
		}
		
        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_Product_Updates_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_pup_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-pup' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-pup', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-product-updates/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-pup/ folder
                load_textdomain( 'edd-pup', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-product-updates/languages/ folder
                load_textdomain( 'edd-pup', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-pup', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
	
	        $eddpup_settings = array(
	            array(
	                'id'   => 'edd_pup_settings_head',
	                'name' => '<strong>' . __( 'Product Updates Settings', 'edd-pup' ) . '</strong>',
	                'desc' => __( 'Configure the Product Updates settings', 'edd-pup' ),
	                'type' => 'header'
	            ),
	            array(
	                'id'   => 'edd_pup_test',
	                'name' => __( 'Enable Test Mode', 'edd-pup' ),
	                'desc' => __( 'When checked, EDD Product Updates will simulate emails being sent without actually sending them.', 'edd-pup' ),
	                'type' => 'checkbox'
	            ),
	            /*array(
	                'id'   => 'edd_pup_throttle',
	                'name' => __( 'Enable Email Throttling', 'edd-pup' ),
	                'desc' => __( 'When checked, emails will be throttled based on your preferences below rather than sent immediately when they are processed.', 'edd-pup' ),
	                'type' => 'checkbox'
	            ),
	            array(
	                'id'   => 'edd_pup_throttle_batch',
	                'name' => __( 'Throttle Batch Number', 'edd-pup' ),
	                'desc' => __( 'When checked, emails will be throttled based on your preferences below rather than sent as soon as they are processed.', 'edd-pup' ),
	                'type' => 'number',
	                'size' => 'small',
	                'std'  => 10
	            ),
	            array(
	                'id'   => 'edd_pup_throttle_interval',
	                'name' => __( 'Throttle Batch Interval', 'edd-pup' ),
	                'desc' => __( 'In seconds.', 'edd-pup' ),
	                'type' => 'number',
	                'size' => 'small',
	                'std'  => 0
	            ),*/
	            array(
	                'id'   => 'edd_pup_auto_del',
	                'name' => __( 'Disable automatic queue removal', 'edd-pup' ),
	                'desc' => __( 'When checked, emails will remain in the queue indefinitely instead of being removed after 48 hours.', 'edd-pup' ),
	                'type' => 'checkbox'
	            )
			);
		            
	       if ( is_plugin_active('edd-software-licensing/edd-software-licenses.php' ) ) {
	       
	        $eddpup_settings[] =
	            array(
	                'id'   => 'edd_pup_license',
	                'name' => __( 'Easy Digital Downloads Software Licensing Integration', 'edd-pup' ),
	                'desc' => __( 'If enabled, only customers with active software licenses will receive update emails', 'edd-pup' ),
	                'type' => 'checkbox'
	            );
		            
	        }
	        
	        $eddpup_settings2 = array(
				array(
					'id'   => 'edd_pup_log_notes',
					'name' => __( 'Disable customer log notes', 'edd-pup' ),
					'desc' => __( 'If checked, log notes will not be generated on a customer\'s payment history page for the corresponding actions.', 'edd-pup' ),
					'type' => 'multicheck',
					'options' => array( 'sent' => __( 'Sent email log notes' , 'edd-pup' ), 'unsubscribe' => __( 'Unsubscribe log notes' , 'edd-pup' ), 'resubscribe' => __( 'Resubscribe log notes' , 'edd-pup' ) )
				),
				array(
					'id'   => 'edd_pup_template',
					'name' => __( 'Email Template', 'edd-pup' ),
					'desc' => __( 'Choose a template to be used for the product update emails.', 'edd-pup' ),
					'type' => 'select',
					'options' => array_merge( array( 'inherit' => __( 'Same Template as Purchase Receipts', 'edd-pup') ), edd_pup_get_email_templates() )
				),
				array(
					'id'   => 'edd_pup_default_from_name',
					'name' => __( 'Default Product Update From Name', 'edd' ),
					'desc' => __('Enter the default "From Name" for Product Update emails.', 'edd'),
					'type' => 'text',
					'std'  => get_bloginfo('name')
				),
				array(
					'id'   => 'edd_pup_default_from_email',
					'name' => __( 'Default Product Update From Email', 'edd' ),
					'desc' => __('Enter the default email address customers will receive Product Update emails from.', 'edd'),
					'type' => 'text',
					'std'  => get_bloginfo('admin_email')
				),
				array(
					'id'   => 'edd_pup_default_subject',
					'name' => __( 'Default Product Update Subject', 'edd' ),
					'desc' => __('Enter the default subject line for Product Update emails.', 'edd'),
					'type' => 'text',
					'std'  => __( 'New product update available', 'edd-pup' )
				),				
				array(
					'id'   => 'edd_pup_default_message',
					'name' => __( 'Default Product Update Message', 'edd' ),
					'desc' => __('Enter the default message for Product Update emails. HTML is accepted. Available template tags:', 'edd') . '<br>' . edd_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => '<p>'.__( 'Hello {name},', 'edd-pup').'</p><p>'.__( 'There are updates available for the following products:', 'edd-pup' ).'</p><p>{updated_products}</p><p>'.__( 'You can download these updates from the following links:', 'edd-pup' ).'</p><p>{updated_products_links}</p><p>'.__( 'Thank you for being a customer of {sitename}!', 'edd-pup' ).'</p><p><small>'.__( 'To no longer receive product update emails, please click here: {unsubscribe_link}', 'edd-pup' ).'</small></p>'
				)
			);
			
	        return array_merge( $settings, $eddpup_settings, $eddpup_settings2 );
        
        }

	    /*
		 * Create the custom database table for the email queue.
		 *
		 * @since 1.1.2
		 * @return void
		 */

		public static function create_table() {
			
	        /* Create custom database table for email send queue */
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
			 
			 /* Create wp_cron job for auto-clear of queue */
			if( wp_next_scheduled( 'edd_pup_cron_clear_queue' ) == false ){
	  
				wp_schedule_event( time(), 'daily', 'edd_pup_cron_clear_queue' );  
	  
			}
	    }
	    
	    
	    /**
	     * Triggers custom database creation on plugin activation for WPMU and single-site installs
	     * 
	     * @access public
	     * @static
	     * @param mixed $network_wide
	     * @return void
	     * @since 0.9.2
	     */
	    public static function activation( $network_wide ) {
		    
		    global $wpdb;
		    
		    if ( is_multisite() && $network_wide ) {
		        
		        // store the current blog id
		        $current_blog = $wpdb->blogid;
		        
		        // Get all blogs in the network and activate plugin on each one
		        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		        
		        foreach ( $blog_ids as $blog_id ) {
		            switch_to_blog( $blog_id );
					self::create_table();
		            restore_current_blog();
		        }
		        
		    } else {
			    
		        self::create_table();
		    
		    }   
	    }
        
        
    }


/**
 * The main function responsible for returning the one true EDD_Product_Updates
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Product_Updates The one true EDD_Product_Updates
 *
 */
function EDD_Product_Updates_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
        return EDD_Product_Updates::instance();
    } else {
        return EDD_Product_Updates::instance();
    }
}

/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class hence, needs to be called outside and the
 * function also needs to be static.
 */
register_activation_hook( __FILE__, array( 'EDD_Product_Updates', 'activation' ) );


add_action( 'plugins_loaded', 'EDD_Product_Updates_load' );

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
    
    update_option( 'edd_pup_version', '1.1.3' );			
}
		
add_action( 'init', 'edd_pup_register_table', 1 );
add_action( 'switch_blog', 'edd_pup_register_table' );

/**
 * Create custom database table whenever a new blog is created for WPMU
 * 
 * @access public
 * @param mixed $blog_id
 * @param mixed $user_id
 * @param mixed $domain
 * @param mixed $path
 * @param mixed $site_id
 * @param mixed $meta
 * @return void
 * @since 1.1.2
 */
function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    if ( is_plugin_active_for_network( 'edd-product-updates/edd-product-updates.php' ) ) {
        switch_to_blog( $blog_id );
        EDD_Product_Updates::create_table();
        restore_current_blog();
    }
}
add_action( 'wpmu_new_blog', 'on_create_blog', 10, 6 );

/**
 * Delete the custom database table whenever a blog is deleted for WPMU
 * 
 * @access public
 * @param mixed $tables
 * @return void
 * @since 1.1.2
 */
function on_delete_blog( $tables ) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'edd_pup_queue';
    return $tables;
}
add_filter( 'wpmu_drop_tables', 'on_delete_blog' );

/**
 * Checks the email queue every day for emails older than 48 hours and clears them.
 * 
 * @access public
 * @return void
 * @since 0.9.3.1
 */
function edd_pup_cron_clear(){
	
	if ( edd_get_option( 'edd_pup_auto_del' ) == false ) {
	
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
    
    if ( edd_get_option( 'uninstall_on_delete' ) ) {
	    
	    //Remove custom database table
	    $wpdb->query("DROP TABLE IF EXISTS $wpdb->edd_pup_queue");
	    
	    //Remove all email posts
	    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'edd_pup_email'");
	    
	    //Remove all custom metadata from postmeta table
	    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key IN ( '_edd_pup_from_email' , '_edd_pup_from_name' , '_edd_pup_message' , '_edd_pup_recipients' , '_edd_pup_subject' , '_edd_pup_updated_products' )");
	    
	    //Remove all payment notes in customer payment histories
	    $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_type = 'edd_payment_note' AND comment_content LIKE '%Sent product update%'");
	             
	    //Remove the version option
	    delete_option( 'edd_pup_version' );
    } 
 
    //Remove any leftover transients
	delete_transient( 'edd_pup_all_customers' );
	delete_transient( 'edd_pup_all_downloads' );
	
	//Remove any author specific transients
	$emailauthors = $wpdb->get_results("SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'edd_pup_email'", ARRAY_N );
	
	foreach ( $emailauthors as $author ) {
		delete_transient( 'edd_pup_sending_email_'. $author );
		delete_transient( 'edd_pup_subject_'. $author );
		delete_transient( 'edd_pup_email_body_header_'. $author );
		delete_transient( 'edd_pup_email_body_footer_'. $author );
		delete_transient( 'edd_pup_preview_email_'. $author );
	}
}
register_uninstall_hook(__FILE__,'edd_pup_uninstall');

} // End if class_exists check