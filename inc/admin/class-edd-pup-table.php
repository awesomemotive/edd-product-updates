<?php
/**
 * Product Update Emails Table Class
 * 
 * @since 0.9.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Pup_Table Class
 *
 * Renders the Product Update Emails table on the Product Updates submenu page
 *
 * @since 0.9.3
 */
class EDD_Pup_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 0.9.3
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of emails stored
	 * @var string
	 * @since 0.9.3
	 */
	public $total_count;

	/**
	 * Number of sent emails
	 *
	 * @var string
	 * @since 0.9.3
	 */
	public $sent_count;

	/**
	 * Number of pending emails
	 *
	 * @var string
	 * @since 0.9.3
	 */
	public $queue_count;
	
	/**
	 * Number of draft emails
	 *
	 * @var string
	 * @since 0.9.3
	 */
	public $draft_count;
	
	/**
	 * Number of cancelled/abandoned emails
	 *
	 * @var string
	 * @since 0.9.3
	 */
	public $abandon_count;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 0.9.3
	 * @uses EDD_Pup_Table::get_email_counts()
	 * @see WP_List_Table::__construct()
	 * @return void
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular'  => 'Email',    // Singular name of the listed records
			'plural'    => 'Emails',    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		$this->get_email_counts();
	}
  
	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 0.9.3
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
	<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 0.9.3
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url('edit.php?post_type=download&page=edd-prod-updates');

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$sent_count   = '&nbsp;<span class="count">(' . $this->sent_count . ')</span>';
		$queue_count = '&nbsp;<span class="count">(' . $this->queue_count  . ')</span>';
		$draft_count = '&nbsp;<span class="count">(' . $this->draft_count  . ')</span>';
		$abandon_count = '&nbsp;<span class="count">(' . $this->abandon_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd-ppe') . $total_count ),
			'sent'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'publish', $base ), $current === 'publish' ? ' class="current"' : '', __('Sent', 'edd-pup') . $sent_count ),
			'queue'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __('In Queue', 'edd-pup') . $queue_count ),
			'draft'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'draft', $base ), $current === 'draft' ? ' class="current"' : '', __('Drafts', 'edd-pup') . $draft_count ),
			'abandoned'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'abandoned', $base ), $current === 'abandoned' ? ' class="current"' : '', __('Cancelled', 'edd-pup') . $abandon_count )
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 0.9.3
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'      		=> '<input type="checkbox" />',
			'email'			=> __( 'Email Name', 'edd-pup' ),
			'status'  		=> __( 'Status', 'edd-pup' ),
			'subject'		=> __( 'Subject', 'edd-pup' ),
			'recipients'	=> __( 'Recipients', 'edd-pup' ),
			'date'			=> __( 'Last Modified / Date Sent', 'edd-pup' )
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 0.9.3
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'email'      => array( 'email', false ),
			'subject'    => array( 'subject', false ),
			'recipients' => array( 'recipients', false ),
			'date'       => array( 'date', true )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 0.9.3
	 *
	 * @param array $item Contains all the data of the email
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $item, $column_name ) {
		switch( $column_name ){
			default:
			return $item[ $column_name ];
		}
	}
	
	/**
	 * Render the Status Column
	 *
	 * @access public
	 * @since 0.9.3
	 *
	 * @param array $item Contains all the data of the email
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_status( $item ) {
		switch ( $item[ 'status' ] ){
			case 'Publish':
				return '<span class="'.strtolower($item[ 'status' ]).'">'. __( 'Sent', 'edd-pup' ) .'</span>';
			case 'Pending':
				if ( edd_pup_is_processing( $item['ID'] ) ) {
				return '<span class="'.strtolower($item[ 'status' ]).'">'. __( 'Processing', 'edd-pup' ) .'</span>';				
				} else {
				return '<span class="'.strtolower($item[ 'status' ]).'">'. __( 'In Queue', 'edd-pup' ) .'</span>';
				}
			case 'Abandoned':
				return '<span class="'.strtolower($item[ 'status' ]).'">'. __( 'Cancelled', 'edd-pup' ) .'</span>';
			default:
				return '<span class="'.strtolower($item[ 'status' ]).'">'. $item[ 'status' ] .'</span>';
		}
	}



	/**
	 * Render the Email Column
	 *
	 * @access public
	 * @since 0.9.3
	 * @param array $item Contains all the data of the email
	 * @return string Data shown in the Name column
	 */
	function column_email( $item ) {
		$email        = get_post( $item['ID'] );
		$status       = strtolower ( $item [ 'status' ] );
		$view	      = $status == 'draft' ? 'edit_pup_email' : 'view_pup_email';
		$emailname    = !empty( $email->post_title ) ? $email->post_title : __( '(no title)', 'edd-pup' );
		$baseurl	  = admin_url( 'edit.php?post_type=download&page=edd-prod-updates' );
		$row_actions  = array();
				
		if ( $status == 'draft' ) {
			$row_actions['edit'] = '<a href="' . add_query_arg( array( 'view' => $view, 'id' => $email->ID, 'edd_pup_notice' => false ), $baseurl ) . '">' . __( 'Edit', 'edd-pup' ) . '</a>';
		} else {
			$row_actions['edit'] = '<a href="' . add_query_arg( array( 'view' => $view, 'id' => $email->ID, 'edd_pup_notice' => false), $baseurl ) . '">' . __( 'View', 'edd-pup' ) . '</a>';
		}

		if ( $status == 'pending' && edd_pup_is_processing( $item['ID'] ) == false ) {
			$row_actions['send'] = '<a href="javascript:void(0);" class="edd-pup-queue-button" data-action="edd_pup_send_queue" data-email="'. $email->ID .'" data-url="'.add_query_arg( array( 'view' => 'send_pup_ajax', 'id' => $email->ID, 'restart' => 1 ), $baseurl ).'">' . __( 'Send Remaining', 'edd-pup' ) . '</a>';
			$row_actions['clear'] = '<a href="javascript:void(0);" class="edd-pup-queue-button" data-action="edd_pup_clear_queue" data-email="'. $email->ID .'" data-nonce="'.wp_create_nonce( 'clear-queue-' . $email->ID ).'" >' . __( 'Clear from Queue', 'edd-pup' ) . '</a>';			
		}
	
		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd_action' => 'pup_delete_email', 'id' => $email->ID ), $baseurl ), 'edd-pup-delete-nonce' ) . '" onclick="var result=confirm(\''. __( 'Are you sure you want to permanently delete this email?', 'edd-pup' ).'\');return result;">' . __( 'Delete', 'edd-pup' ) . '</a>';

		$row_actions = apply_filters( 'edd_pup_row_actions', $row_actions, $email );

		return '<strong><a class="row-title" href="' . add_query_arg( array( 'view' => $view, 'id' => $email->ID, 'edd_pup_notice' => false ), $baseurl ) . '">' . $emailname . '</a></strong>' . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 0.9.3
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'email',
			/*$2%s*/ $item['ID']
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 0.9.3
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'edd-pup' )
		);

		return $actions;
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 0.9.3
	 * @access public
	 */
	function no_items() {
		_e( 'No product update emails found.', 'edd-pup' );
	}


	/**
	 * Process the bulk actions
	 * @access public
	 * @since 0.9.3
	 * @return void
	 */


	public function process_bulk_action() {
	    if( empty( $_REQUEST['_wpnonce'] ) ) {
	      return;
	    }
	
	    if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-emails' ) ) {
	      return;
	    }
		
		$ids = isset( $_GET[ 'email' ] ) ? $_GET[ 'email' ] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {
			
				// Clear instances of this email in the queue
				if ( false !== edd_pup_check_queue( $id ) ) {
					global $wpdb;
					$wpdb->delete( "$wpdb->edd_pup_queue", array( 'email_id' => $id ), array( '%d' ) );
				}
				
				// Remove transient if it had been set by this email
				if ( get_transient( 'edd_pup_sending_email' ) == $id ) {
					delete_transient( 'edd_pup_sending_email' );
				}
				
				// Delete the email
				$goodbye = wp_delete_post( $id , true );
			}
		}
	}

	/**
	 * Retrieve the email counts
	 *
	 * @access public
	 * @since 0.9.3
	 * @return void
	 */
	public function get_email_counts() {
		$emails_count  = wp_count_posts( 'edd_pup_email', 'readable' );
		$this->sent_count   = $emails_count->publish; // related to post status
		$this->queue_count = $emails_count->pending;
		$this->draft_count = $emails_count->draft;
		$this->abandon_count = $emails_count->abandoned;
		$this->total_count    = $emails_count->publish + $emails_count->draft + $emails_count->pending + $emails_count->abandoned;
	}

	/**
	 * Retrieve all the data for all the emails
	 *
	 * @access public
	 * @since 0.9.3
	 * @return array $email_data Array of all the data for the email
	 */
	public function pup_emails_data() {
		$email_data = array();

		$per_page = $this->per_page;

		$mode = edd_is_test_mode() ? 'test' : 'live';

		$orderby 		= isset( $_GET['orderby'] )  ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )    ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'           ? 'ASC'                             : 'DESC';
		$status 		= isset( $_GET['status'] )   ? $_GET['status']                   : array( 'draft', 'pending', 'publish', 'abandoned' );
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key']                 : null;
		$search         = isset( $_GET['s'] )        ? sanitize_text_field( $_GET['s'] ) : null;
		$order_class 	= strtolower( $order_inverse );
		$dateformat 	= get_option( 'date_format' ). ' ' . get_option( 'time_format' );
			
		$args = array(
			'posts_per_page' => $per_page,
			'paged'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'        => $orderby,
			'order'          => $order,
			'post_status'    => $status,
			'meta_key'       => $meta_key,
			's'              => $search
		);
		
		$defaults = array(
			'post_type'      => 'edd_pup_email',
			'posts_per_page' => 30,
			'paged'          => null,
		);
		
		$args = wp_parse_args( $args, $defaults );
	
		$emails = get_posts( $args );

		if ( $emails ) {
			foreach ( $emails as $email ) {

				$updated_products = get_post_meta( $email->ID, '_edd_pup_updated_products', TRUE );
				
				$recipients = get_post_meta( $email->ID, '_edd_pup_recipients', true );
				$subject = get_post_meta( $email->ID, '_edd_pup_subject', true );				

				$email_data[] = array(
					'ID' 			=> $email->ID,
					'status'		=> ucwords( $email->post_status ),
					'subject'		=>	!empty( $subject ) ? $subject : __( '(no subject)', 'edd-pup' ),
					'date'			=>  get_the_time( $dateformat, $email->ID ),
					'recipients'	=>	is_array( $recipients ) ? number_format( absint( $recipients['total'] ) ) : number_format( absint( $recipients ) )
				);

			}
		}

		return $email_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 0.9.3
	 * @uses EDD_Pup_Table::get_columns()
	 * @uses EDD_Pup_Table::get_sortable_columns()
	 * @uses EDD_Pup_Table::process_bulk_action()
	 * @uses EDD_Pup_Table::email_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->pup_emails_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'publish':
				$total_items = $this->sent_count;
				break;
			case 'draft':
				$total_items = $this->draft_count;
				break;
			case 'pending':
				$total_items = $this->queue_count;
				break;
			case 'abandoned':
				$total_items = $this->abandon_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
}