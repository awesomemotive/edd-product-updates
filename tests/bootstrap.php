<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Edd_Product_Updates
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	global $argv;
	
	require dirname( dirname( __FILE__ ) ) . '/edd-product-updates.php';
	
	if( is_array( $argv ) &&  in_array( 'edd_2' , $argv ) ){
		if( !defined( 'WP_CLI' ) ){
			define( 'WP_CLI', true );
		}
		require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/easy-digital-downloads-2.0/easy-digital-downloads.php';
	} else {
		require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/easy-digital-downloads/easy-digital-downloads.php';
	}
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
