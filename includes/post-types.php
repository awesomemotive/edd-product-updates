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
		'name' 				=> __('Product Update Emails', 'post type general name', 'edd-pup' ),
		'singular_name' 	=> __('Product Update Email', 'post type singular name', 'edd-pup' ),
		'add_new' 			=> __( 'Add New', 'edd-pup' ),
		'add_new_item' 		=> __( 'Add New Update Email', 'edd-pup' ),
		'edit_item' 		=> __( 'Edit Update Email', 'edd-pup' ),
		'new_item' 			=> __( 'New Update Email', 'edd-pup' ),
		'all_items' 		=> __( 'All Update Emails', 'edd-pup' ),
		'view_item' 		=> __( 'View Update Email', 'edd-pup' ),
		'search_items' 		=> __( 'Search Update Emails', 'edd-pup' ),
		'not_found' 		=>  __( 'No Update Email found', 'edd-pup' ),
		'not_found_in_trash'=> __( 'No update emails found in Trash', 'edd-pup' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Product Updates', 'edd-pup' )
	);

	$edd_pup_email_args = array(
		'labels' 			=> apply_filters( 'edd_pup_email_labels', $edd_pup_email_labels ),
		'public' 			=> true,
		'exclude_from_search' => true,
		'publicly_queryable' => false,
		'query_var' 		=> false,
		'rewrite' 			=> false,
		'show_ui'			=> true,
		'show_in_menu'		=> 'edit.php?post_type=download&page=edd-prod-updates',
		'capability_type' 	=> 'install_plugins',
		//'map_meta_cap'      => true,
		'supports' 			=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'can_export'		=> true
	);
	
	register_post_type( 'edd_pup_email', $edd_pup_email_args );
}

add_action( 'init', 'edd_pup_post_types' );