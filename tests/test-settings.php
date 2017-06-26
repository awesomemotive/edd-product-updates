<?php
/**
 * Class TestSettings
*/

class TestSettings extends WP_UnitTestCase {

	public function __get( $name ) {
		switch ( $name ){
			case 'plugin' :
				if( empty( $this->plugin ) ){
					$this->plugin = EDD_Product_Updates::instance();
				}
				return $this->plugin;
			case 'importer':
				if(file_exists( dirname( dirname( dirname( __FILE__ ) ) ) . '/wordpress-importer/wordpress-importer.php' )){
					if( !defined( 'WP_LOAD_IMPORTERS' ) ){
						define( 'WP_LOAD_IMPORTERS' , true );
					}
					require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/wordpress-importer/wordpress-importer.php' );
					$this->importer = new WP_Import();
					return $this->importer;
				}
		}
		return isset( $this->$name );
	}

	public function load_data() {
		global $wpdb;
		if( !$this->is_edd2() ){
			edd_run_install();
			EDD_Product_Updates::activation( false);
		} else {
			edd_install();
			EDD_Product_Updates::activation( false);
		}
		$result = array();
		if( is_dir( __DIR__ . '/data' ) ){
			foreach ( glob( __DIR__ . '/data/*.json' ) as $table_data ){
				if( file_exists( $table_data ) ){
					$data = json_decode( file_get_contents( $table_data ) );
					$table =  str_replace( '.json', '', basename( $table_data ) );
					if( $this->is_edd2() && ( 'edd_customermeta' == $table || 'edd_customers' == $table ) ){
						continue;
					}
					$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}{$table};" );
					if(is_array( $data ) ){
						foreach ( $data as $item ){
							$wpdb->insert( $wpdb->prefix . $table, (array)$item );
							if( $wpdb->last_error ){
								$result[] = $wpdb->last_error;
							}
						}
					}
				}
			}
		}
	}

	public function is_edd2() {
		global $argv;
		if(in_array( 'edd_2' , $argv) ){
			return true;
		}
		return false;
	}

	/**
	 * The initiall load of the plugin , without the data
	 *
	 * @dataProvider postProvider
	 */
	public function test_initial_load( $data ){
		if( !$this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}

	}

	/**
	 *
	 * @dataProvider variousProducts
	 */
	public function test_various_products( $data ) {
		if( !$this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProductsUniqueClient
	 */
	public function test_various_products_unique_client( $data ) {
		if( !$this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 * The initiall load of the plugin , without the data
	 *
	 * @dataProvider postProvider
	 */
	public function test_edd_2_0_initial_load( $data ){
		if( $this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProducts
	 */
	public function test_edd_2_0_various_products( $data ) {
		if( $this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProductsUniqueClient
	 */
	public function test_edd_2_0_various_products_unique_client( $data ) {
		if( $this->is_edd2() ){
			$this->send_mail_test_case( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 * The initiall load of the plugin , without the data
	 *
	 * @dataProvider postProvider
	 */
	public function test_initial_load_ajax_callback( $data ){
		if( !$this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}

	}

	/**
	 *
	 * @dataProvider variousProducts
	 */
	public function test_various_products_ajax_callback( $data ) {
		if( !$this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProductsUniqueClient
	 */
	public function test_various_products_unique_client_ajax_callback( $data ) {
		if( !$this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 * The initiall load of the plugin , without the data
	 *
	 * @dataProvider postProvider
	 */
	public function test_edd_2_0_initial_load_ajax_callback( $data ){
		if( $this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProducts
	 */
	public function test_edd_2_0_various_products_ajax_callback( $data ) {
		if( $this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	/**
	 *
	 * @dataProvider variousProductsUniqueClient
	 */
	public function test_edd_2_0_various_products_unique_client_ajax_callback( $data ) {
		if( $this->is_edd2() ){
			$this->send_mail_test_case_ajax_callback( $data );
		} else {
			$this->assertTrue( true );
		}
	}

	public function send_mail_test_case( $data ) {
		global $wpdb;
		//var_dump($data);
		$this->load_data();
		$email_id = edd_pup_sanitize_save( $data );
		//var_dump($email_id);
		$products = edd_pup_get_products($email_id);
		$licenseditems = $wpdb->get_results( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_sl_enabled' AND meta_value = 1", OBJECT_K );
		//var_dump( $products );
		$limit = 1000;
		$unique_users = isset( $data['edd_pup_unique_client'] )? (bool)$data['edd_pup_unique_client']: false;
		$customers = edd_pup_user_send_updates( $products, true, $limit, 0, $unique_users );
		//var_dump($customers);
		$query = edd_pup_build_queue( $customers, $products, $email_id, $limit  );
		$queueinsert = implode( ',', $query );
		$wpdb->query( "INSERT INTO $wpdb->edd_pup_queue (eddpup_id, customer_id, email_id, products, sent) VALUES $queueinsert ON DUPLICATE KEY UPDATE eddpup_id = eddpup_id" );
		$query = "SELECT * FROM $wpdb->edd_pup_queue WHERE email_id = $email_id AND sent = 0 LIMIT $limit";
		$customers = $wpdb->get_results( $query , ARRAY_A);
		$result = array();
		foreach ( $customers as $customer ){
			$payment_ids = array();
			$payment_id = 0;
			if( $unique_users ){
				$customer_products = maybe_unserialize( $customer['products'] );
				if( is_array( $customer_products ) ){
					foreach ( $customer_products as $payment_id => $customer_products_item ){
						$payment_ids[] = $payment_id;
					}
				}
			} else {
				$payment_id = $customer['customer_id'];
			}
			$email = edd_pup_ajax_send_email( $payment_id, $email_id, true, $payment_ids );
			//var_dump($email);
			if( $email && 'nothig' !== $email ){
				$payment_key = $payment_id;
				if( !empty( $payment_ids ) ){
					$payment_key = implode( ',', $payment_ids );
				}
				$result[ $payment_key ] = $email['stripped_message'];
			}
		}
		//var_dump($result);
		foreach ( $result as $client => $item ){
			$payment_id = $client;
			if( count( $payment_key = explode( ',', $client ) ) > 1 ){
				$payment_id = $payment_key;
			}
			$links = $this->get_products_links( $item );
			$this->assertNotEmpty( $links );
			//var_dump($links);
			$this->assertTrue( $this->is_links_own_customer( $payment_id, $products, $licenseditems, $email_id , $links) );
			$this->assertTrue( $this->is_all_links( $payment_id, $products, $licenseditems, $email_id , $links) );
		}
		if( !empty( $products ) ){
			$this->assertNotEmpty( $result );
			$this->assertTrue( count( $customers ) == count( $result ) );
		} else {
			$this->assertTrue( true );
		}
	}

	public function send_mail_test_case_ajax_callback( $data ) {
		global $wpdb;
		//var_dump($data);
		$this->load_data();
		$email_id = edd_pup_sanitize_save( $data );
		$_POST = array(
			'nonce'		=> wp_create_nonce( 'edd_pup_ajax_start' ),
			'email_id'	=> $email_id,
			'iteration' => 0,
			'sent'		=> 0,
		);
		$ajax_start = edd_pup_ajax_start( true );
		$_ajax_start = json_decode( $ajax_start );

		$this->assertTrue( 'new' == $_ajax_start->status );
		$ajax_trigger = edd_pup_ajax_trigger( true );
		$_ajax_trigger = json_decode( $ajax_trigger );

		$this->assertTrue( $_ajax_trigger == $_ajax_start->total );
		$products = edd_pup_get_products($email_id);
		$unique_users = isset( $data['edd_pup_unique_client'] )? (bool)$data['edd_pup_unique_client']: false;
		$customers = edd_pup_user_send_updates( $products, true, 1000, 0, $unique_users );
		$this->assertTrue( count( $customers ) == $_ajax_start->total );
	}

	public function is_links_own_customer( $client, $products, $licenseditems, $email_id , $links ) {
		$client_prods = array();
		if( is_array( $client ) ){
			foreach ( $client as $client_item ){
				$updates = edd_pup_eligible_updates( $client_item, $products, true, $licenseditems, $email_id );
				$_client_prods = $this->get_download_links( $client_item, $updates );
				$client_prods = array_merge( $client_prods, $_client_prods );
			}
		} else {
			$updates = edd_pup_eligible_updates( $client, $products, true, $licenseditems, $email_id );
			$client_prods = $this->get_download_links( $client, $updates );
		}
		//var_dump($client_prods);
		$own_links = array();
		foreach ( $links as $link ){
			$link_parts = parse_str( $link );
			foreach ( $client_prods as $client_prods_item ){
				$client_prods_parts = parse_str( $client_prods_item );
				if( $client_prods_parts['eddfile'] == $link_parts['eddfile'] ){
					$own_links[ $link ] = true;
				}
			}
			if( !isset( $own_links[ $link ] ) ){
				return false;
			}
		}
		return true;
	}

	public function is_all_links( $client, $products, $licenseditems, $email_id , $links ) {
		$client_prods = array();
		if( is_array( $client ) ){
			foreach ( $client as $client_item ){
				$updates = edd_pup_eligible_updates( $client_item, $products, true, $licenseditems, $email_id );
				$_client_prods = $this->get_download_links( $client_item, $updates );
				$client_prods = array_merge( $client_prods, $_client_prods );
			}
		} else {
			$updates = edd_pup_eligible_updates( $client, $products, true, $licenseditems, $email_id );
			$client_prods = $this->get_download_links( $client, $updates );
		}
		if( count( $client_prods ) == count( $links ) ){
			return true;
		}
		return false;
	}

	public function get_download_links( $payment_id, $updates ) {
		$download_links = array();
		if ( $updates ) {
			$payment_data  = edd_get_payment_meta( $payment_id );
			// Set email to most recent email if it's been changed from initial email
			if ( isset( $payment_data['user_info']['email'] ) && $payment_data['user_info']['email'] != $payment_data['email'] ) {
				$payment_data['email'] = $payment_data['user_info']['email'];
			}

			foreach ( $updates as $item ) {

				$bundle = edd_is_bundled_product( $item['id'] );
				$price_id = edd_get_cart_item_price_id( $item );
				$files = edd_get_download_files( $item['id'], $price_id );
				if ( $files ) {
					foreach ( $files as $filekey => &$file ) {
						$download_links[] = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $item['id'], $price_id );
					}
				}
				if ( $bundle ) {
					$bundled_products = edd_get_bundled_products( $item['id'] );
					foreach ( $bundled_products as $bundle_item ) {
						$bundlefiles = edd_get_download_files( $bundle_item );
						foreach ( $bundlefiles as $bundlefilekey => $bundlefile ) {
							$download_links[] = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $bundlefilekey, $bundle_item, $price_id );
						}
					}
				}

					
			}
		}
		return $download_links;

	}

	public function get_products_links( $message ) {
		$product_links = array();
		$links = strip_tags( $message, '<a>' );
		$html = preg_replace('#&(?=[a-z_0-9]+=)#', '&amp;', $links );
		$xml = simplexml_load_string('<div>'.$html.'</div>');
		$list = $xml->xpath("//@href");
		$links_data = json_decode( json_encode( $list ),true);
		foreach ( $links_data as $links_data_item ){
			$link = isset( $links_data_item['@attributes']['href'] )? $links_data_item['@attributes']['href']: '';
			if($this->is_product( $link ) ){
				$product_links[] = $link;
			}
		}
		return $product_links;
	}

	public function is_product( $link ) {
		$link_parts = array();
		parse_str( $link, $link_parts );
		$action = isset( $link_parts['edd_action'] )? $link_parts['edd_action'] : '';
		if( 'prod_update_unsub' == $action ){
			return false;
		}

		return true;
	}

	public function filter_query( &$str, $start_end = false ) {
		$str = str_replace( ' ', '', $str );
		if( $start_end ){
			$str = str_replace( array( '(', ')' ), '', $str );
		}
		return $str;
	}


	public function is_assoc($array){
		if( !is_array( $array ) ){
			return false;
		}
		$keys = array_keys($array);
		return array_keys($keys) !== $keys;
	}

	public function uniqueCombination( $in, $minLength = 1, $max = 2000 ) {
		$count = count($in);
		$members = pow(2, $count);
		$return = array();
		for($i = 0; $i < $members; $i ++) {
			$b = sprintf("%0" . $count . "b", $i);
			$out = array();
			for($j = 0; $j < $count; $j ++) {
				if( $b{$j} == '1' ){
					if( $this->is_assoc( $in ) ){
						$keys = array_keys( $in );
						$out[$keys[ $j ]] = $in[ $keys[ $j ] ];
					} else {
						$out[] = $in[$j];
					}
				}
			}
			if( $this->is_assoc( $in ) ){
				count($out) >= $minLength && count($out) <= $max and $return[implode( '+', array_keys( $out ) ) ] = $out;
			} else {
				count($out) >= $minLength && count($out) <= $max and $return[implode( '+', $out ) ] = $out;
			}
		}
		return $return;
	}

	public function dynamicSettings() {
		$different_settings = array();
		$all_settings = $this->settingsProvider()['full data set'][0];

		$edd_pup_log_notes = $all_settings['edd_pup_log_notes'];
		unset( $all_settings['edd_pup_log_notes'] );

		$edd_pup_log_notes_combo = $this->uniqueCombination($edd_pup_log_notes);
		$all_settings_combo = $this->uniqueCombination( $all_settings );

		$combined_combo_settings = array();
		foreach ( $all_settings_combo as $all_key => $all_settings_combo_item ){
			foreach ( $edd_pup_log_notes_combo as $edd_pup_log_notes_combo_key => $edd_pup_log_notes_combo_item ){
				$new_item = array_merge( $all_settings_combo_item, array( 'edd_pup_log_notes'=> $edd_pup_log_notes_combo_item ) ) ;
				$combined_combo_settings[ $all_key .'+'. $edd_pup_log_notes_combo_key ] = $new_item;
			}
		}
		return $combined_combo_settings;
	}


	public function variousProducts() {
		$post = $this->postProvider();
		$all_post = $post['full post data'][0];
		$products = $all_post['products'];
		unset( $all_post['products'] );

		$products_combo = $this->uniqueCombination($products);
		$all_products_combo = array();
		foreach ( $products_combo  as $products_combo_key =>  $products_combo_item ){
			$new_item = array_merge( $all_post, array( 'products'=> $products_combo_item ) ) ;
			$all_products_combo[ 'all settings with+'. $products_combo_key ][] = $new_item;
		}

		return $all_products_combo;
	}

	public function variousProductsUniqueClient() {
		$post = $this->postProvider();
		$all_post = $post['full post data unique client'][0];
		$products = $all_post['products'];
		unset( $all_post['products'] );

		$products_combo = $this->uniqueCombination($products);
		$all_products_combo = array();
		foreach ( $products_combo  as $products_combo_key =>  $products_combo_item ){
			$new_item = array_merge( $all_post, array( 'products'=> $products_combo_item ) ) ;
			$all_products_combo[ 'all settings with+'. $products_combo_key ][] = $new_item;
		}

		return $all_products_combo;
	}


	public function postProvider() {
		return array(
			'full post data' => array(
				array(
					'test-email'	=>	"",
					'recipients'	=>	"5",
					'title'	=>	"bundle update",
					//					'products' =>	array(
						//						'493','488'
						//					),
					//'products' =>array('152', '488', '11'),
					'products' =>	array(
						'43','93','67','327','22',
						'480', '27','152','134',
						'493','488', '11','56',
					),
					'bundle_1'	=>	"all",
					//'bundle_2'	=>	"0"	,
					'from_name'	=>	"Edd email updates",
					'from_email'	=>	"skylineflash@yandex.ru",
					'subject'	=>	"Product updates",
					'message'	=>	"Hello {name},

					There are updates available for the following products:

					{updated_products}

					You can download these updates from the following links:

					{updated_products_links}

					Thank you for being a customer of {sitename}!

					<small>To no longer receive product update emails, please click here: {unsubscribe_link}</small>",
					'edd-action'	=>	"edit_pup_email",
					'email-id'	=>	"",
					'edd_pup_tinymce_status'	=>	"true",
				),
			),
			'full post data unique client' => array(
				array(
					'test-email'	=>	"",
					'recipients'	=>	"5",
					'title'	=>	"bundle update",
					//					'products' =>	array(
						//						'493','488'
						//					),
					//'products' =>array('152', '488', '11'),
					'products' =>	array(
						'43','93','67','327','22',
						'480', '27','152','134',
						'493','488', '11','56',
					),
					'bundle_1'	=>	"all",
					'edd_pup_unique_client'			=> '1',
					//'bundle_2'	=>	"0"	,
					'from_name'	=>	"Edd email updates",
					'from_email'	=>	"skylineflash@yandex.ru",
					'subject'	=>	"Product updates",
					'message'	=>	"Hello {name},

					There are updates available for the following products:

					{updated_products}

					You can download these updates from the following links:

					{updated_products_links}

					Thank you for being a customer of {sitename}!

					<small>To no longer receive product update emails, please click here: {unsubscribe_link}</small>",
					'edd-action'	=>	"edit_pup_email",
					'email-id'	=>	"",
					'edd_pup_tinymce_status'	=>	"true",
				),
			),
		);
	}

	public function settingsProvider(){
		return [
			'full data set'  => array(
				array(
					'edd_pup_template'				=> 'inherit',
					'edd_pup_default_from_name'		=> 'Edd email updates',
					'edd_pup_default_from_email'	=> 'skylineflash@rambler.ru',
					'edd_pup_default_subject'		=> 'Product updates',
					'edd_pup_test'					=> '1',
					'edd_pup_auto_del'				=> '1',
					'edd_pup_log_notes'				=> array(
						'sent'			=> 'Sent email log notes',
						'unsubscribe'	=> 'Unsubscribe log notes',
						'resubscribe'	=> 'Resubscribe log notes',
					),
					'edd_pup_default_message'		=> 'Hello {name},

There are updates available for the following products:

{updated_products}

You can download these updates from the following links:

{updated_products_links}

Thank you for being a customer of {sitename}!

<small>To no longer receive product update emails, please click here: {unsubscribe_link}</small>',
						
				)
			),
		];
	}

}
