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

/**
 * Register custom edd_pup_email post type for storing sent updates
 * 
 * @access public
 * @return void
 */
function edd_pup_post_types() {

	$edd_pup_email_labels = array(
		'name' 				=> _x('Product Update Emails', 'post type general name', 'edd' ),
		'singular_name' 	=> _x('Product Update Email', 'post type singular name', 'edd' ),
		'add_new' 			=> __( 'Add New', 'edd' ),
		'add_new_item' 		=> __( 'Add New Update Email', 'edd' ),
		'edit_item' 		=> __( 'Edit Update Email', 'edd' ),
		'new_item' 			=> __( 'New Update Email', 'edd' ),
		'all_items' 		=> __( 'All Update Emails', 'edd' ),
		'view_item' 		=> __( 'View Update Email', 'edd' ),
		'search_items' 		=> __( 'Search Update Emails', 'edd' ),
		'not_found' 		=>  __( 'No Update Email found', 'edd' ),
		'not_found_in_trash'=> __( 'No update emails found in Trash', 'edd' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Product Updates', 'edd' )
	);

	$edd_pup_email_args = array(
		'labels' 			=> apply_filters( 'edd_pup_email_labels', $edd_pup_email_labels ),
		'public' 			=> false,
		'query_var' 		=> false,
		'rewrite' 			=> false,
		'show_ui'			=> false,
		'capability_type' 	=> 'manage_shop_settings',
		'map_meta_cap'      => true,
		'supports' 			=> array( 'title' ),
		'can_export'		=> true
	);
	
	register_post_type( 'edd_pup_email', $edd_pup_email_args );
}

add_action( 'init', 'edd_pup_post_types' );