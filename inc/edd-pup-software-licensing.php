<?php
/**
 * EDD Product Updates Software Licensing
 *
 * Integrate with the Software License Extension if installed
 *
 *
 * @package    EDD_PUP
 * @author     Evan Luzi
 * @copyright  Copyright 2014 Evan Luzi, The Black and Blue, LLC
 * @since      0.9.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_pup_license_check($name, $license){

	$args = array(
		'item_name' => rawurldecode( $data['item_name'] ),
		'key'       => $license;
	);
			
	edd_software_licensing()->check_license($args);
	
	$checkurl = 'http://YOURSITE.com/?edd_action=check_license&item_name=EDD+Product+Name&license=cc22c1ec86304b36883440e2e84cddff&url=http://licensedsite.com';
	
}

//do_action('edd_pup_license_check', $name, $license);