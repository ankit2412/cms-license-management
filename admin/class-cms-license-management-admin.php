<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.cmsminds.com/
 * @since      1.0.0
 *
 * @package    Cms_License_Management
 * @subpackage Cms_License_Management/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cms_License_Management
 * @subpackage Cms_License_Management/admin
 * @author     cmsMinds <info@cmsminds.com>
 */
class Cms_License_Management_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cms_License_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cms_License_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cms-license-management-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cms_License_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cms_License_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cms-license-management-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Add custom meta box for virtual and downloadable products
	 *
	 * @return void
	 */
	public function cms_license_management_add_custom_box() {
		global $post;
		$product = wc_get_product( $post->ID );
		if ( FALSE !== $product && $product->is_virtual( 'yes' ) && $product->is_downloadable('yes') ) {
			add_meta_box(
				'res_license_box',
				'License',
				array( $this, 'cms_license_management_custom_box_html' ),
				'product'
			);
		}
	}

	/**
	 * Custom field html for custom meta box
	 *
	 * @param object $post Holds the post object.
	 * @return void
	 */
	public function cms_license_management_custom_box_html( $post ) {
		$value = get_post_meta( $post->ID, '_res_license_key', true );
		?>
		<label for="res_license_key"><?php __('License Key', 'woocommerce'); ?></label>
		<input type='text' name='res_license_key' value='<?php echo $value; ?>' readonly />
		<?php
	}
	
	/**
	 * Save custom field meta 
	 *
	 * @param int $post_id Holds the ID of the post.
	 * @param object $post Holds the ID of the post.
	 * @param boolean $update Check if post updated.
	 * @return void
	 */
	public function cms_license_management_save_postdata( $post_id, $post, $update ) {
	
		if ( ( isset( $_POST['publish'] ) &&  'Publish' == $_POST['publish'] ) && ( isset( $_POST['_virtual'] ) &&  'on' == $_POST['_virtual'] ) && ( isset( $_POST['_downloadable'] ) &&  'on' == $_POST['_downloadable'] ) ) {
			$license_key = uniqid();
			
			add_post_meta (
				$post_id,
				'_res_license_key',
				$license_key,
				true
			);
		}
	}

	/**
	 * Display License key in email template if order status is completed
	 *
	 * @param object $order Holds the order object.
	 * @param int $sent_to_admin If this email is for administrator or for a customer
	 * @param boolean $plain_text HTML or Plain text (can be configured in WooCommerce > Settings > Emails)
	 * @return void
	 */
	public function cms_license_management_add_email_order_meta( $order, $sent_to_admin, $plain_text ){
	
		if ( 'completed' == $order->get_status() ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				if ( '' != get_post_meta( $item->get_product_id(), '_res_license_key', true ) ) { 
					if ( $plain_text === false ) {
						echo '<h2>License Key for ' . $item->get_name() . ' Product</h2>
						<ul>
						<li>' . get_post_meta( $item->get_product_id(), '_res_license_key', true ) . '</li>
						</ul>';
					} else {
						echo "License Key for " . $item->get_name() . " Product\n
						" . get_post_meta( $item->get_product_id(), '_res_license_key', true ) . "\n";
					}
				}
			}
		}
	}

	/**
	 * Display License key in my account order view page.
	 *
	 * @param object $order Holds the order object.
	 * @return void
	 */
	public function cms_license_management_display_license_key_my_account_order_veiw( $order ){

		if ( 'completed' == $order->get_status() ) { ?>
			<section class='woocommerce-license-details'>
				<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'License details', 'woocommerce' ); ?></h2>
				<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
	
					<thead>
						<tr>
							<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
							<th class="woocommerce-table__product-table product-license-key"><?php esc_html_e( 'License Key', 'woocommerce' ); ?></th>
						</tr>
					</thead>
	
					<tbody>
					<?php
					foreach ( $order->get_items() as $item_id => $item ) {
						if ( '' != get_post_meta( $item->get_product_id(), '_res_license_key', true ) ) { ?>
						<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
							<td class="woocommerce-table__product-name product-name"><?php echo $item->get_name(); ?></td>
							<td class="woocommerce-table__product-license-key product-license-key"><?php echo get_post_meta( $item->get_product_id(), '_res_license_key', true ); ?></td>
						</tr>
						<?php
						}
					} ?>
					</tbody>
				</table>
			</section>
			<?php
		}
	}

	/**
	 * Add license menu page
	 * 
	 * @since    1.0.0
	 */
	public function cms_license_management_license_menu() {
		add_menu_page('CMS License', 'CMS License', 'manage_options', 'cms-license-page', array( $this, 'cms_license_menu_page' ), 'dashicons-lock', 90);
    	add_submenu_page('cms-license-page', 'CMS License', 'CMS License', 'manage_options', 'cms-license-page');
	}

	/**
	 * License menu page HTML
	 * 
	 * @since    1.0.0
	 */
	public function cms_license_menu_page(){
		
		$cms_license_list = new Cms_License_Management_List_Licenses();
		// Do list table form row action tasks.
		if ( isset( $_REQUEST['action'] ) ) {
			// Delete link was clicked for a row in list table.
			if ( isset( $_REQUEST['action'] ) && 'cms_delete_license' === $_REQUEST['action'] ) {
				$cms_license_list->delete_license_key( sanitize_text_field( $_REQUEST['id'] ) );
			}
		}
		// Fetch, prepare, sort, and filter our data...
		$cms_license_list->prepare_items();

		?>
		<style>
		th#id {
			width: 100px;
		}

		th#license_key {
			width: 250px;
		}

		th#max_allowed_domains {
			width: 75px;
		}

		th#lic_status {
			width: 100px;
		}

		th#date_created {
			width: 125px;
		}

		th#date_renewed {
			width: 125px;
		}

		th#date_expiry {
			width: 125px;
		}
		</style>
		<div class="wrap">
			<h2><?php echo __('Manage Licenses', 'cms-license-management'); ?></h2>
			<div id="poststuff">
				<div id="post-body">
					<form id="tables-filter" method="get">

						<?php $cms_license_list->search_box( 'Search', 'cms_license_management_search' ); ?>

						<div class="postbox">
							<h3 class="hndle"><label for="title"><?php echo __('Software Licenses', 'cms-license-management'); ?></label></h3>
							<div class="inside">
								<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
								<?php $cms_license_list->display(); ?>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<script>
		jQuery('input#doaction').click(function(e) {
			return confirm('Are you sure you want to perform this bulk operation on the selected entries?');
		});
		</script>
			<?php
	}

	/**
	 * REST API request handler.
	 * 
	 * @param object $request The rest api request object.
	 * @since 1.0.0
	 */
	public function cms_license_management_license_handler( $request ) {
		global $wpdb;
	
		$cms_license_management_tbl = CMS_LICENSE_MANAGEMENT_TABLE;
		$res_action                 = trim( strip_tags( $request->get_param( 'res_action' ) ) );
		$license_key                = trim( strip_tags( $request->get_param( 'license_key' ) ) );
		$license_email              = trim( strip_tags( $request->get_param( 'license_email' ) ) );
		$registered_domain          = trim( wp_unslash( strip_tags( $request->get_param( 'registered_domain' ) ) ) );

		$product_args = array(
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'meta_key'     => '_res_license_key',
			'meta_value'   => $license_key,
			'meta_compare' => '='
		);
		$product_query = new WP_Query( $product_args );
		
		if ( $product_query->have_posts() ) {
			$product_id      = $product_query->post->ID;
			$product_obj     = wc_get_product( $product_id );
			$product         = $product_obj->get_data();
			$user_data       = get_user_by( 'email', $license_email );

			if ( empty( $user_data ) ) { 
				return new WP_REST_Response( [
					'message' => 'Please enter correct Registered email!',
				], 400 );
			}

			$response = wp_remote_get( 
				CMS_LICENSE_MANAGEMENT_STORE . 'wp-json/wc/v3/customers/' . $user_data->ID . '/downloads', 
				array( 
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( CMS_LICENSE_MANAGEMENT_CK . ':' . CMS_LICENSE_MANAGEMENT_CS )
					)
				) 
			);

			if ( is_wp_error( $response ) ) {
				return new WP_REST_Response( [
					'message' => $response->get_error_message(),
				], 400 );	
			}

			$downloads          = json_decode( wp_remote_retrieve_body( $response ) );
			$obj                = new stdClass();
			$obj->slug          = 'easy-reservations-pro.php';
			$obj->name          = 'Easy Reservations Pro';
			$obj->plugin_name   = 'easy-reservations-pro.php';
			$obj->new_version   = '1.0.1';
			$obj->url           = 'https://www.github.com/cmsminds';
			$obj->package       = $downloads[0]->download_url;
			$obj->requires      = '4.0';
			$obj->tested        = '5.8';
			$obj->downloaded    = 12540;
			$obj->last_updated  = '2021-09-03';
			$obj->sections      = array(
				                  'description' => 'The new version of the Auto-Update plugin',
				                  'another_section' => 'This is another section',
				                  'changelog' => 'Some new features'
			                     );
			$obj->download_link = $obj->package;
			
			switch ( $res_action ) {

				case 'res_get_remote_version':
					/**
					 * Get latest plugin version
					 */
					if ( ! $product_id ) {
						return new WP_REST_Response( [
							'message' => 'Please enter correct License key!',
						], 400 );
					}

					return new WP_REST_Response( [
						'message' => json_encode( $obj ),
					], 200 );
					break;

				case 'res_get_plugin_info':
					/**
					 * Get pro plugin informations
					 */
					if ( ! $product_id ) {
						return new WP_REST_Response( [
							'message' => 'Please enter correct License key!',
						], 400 );
					}

					return new WP_REST_Response( [
						'message' => json_encode( $obj ),
					], 200 );
					break;

				case 'res_get_license_status':
					/**
					 * Get current license status
					 */	
					if ( ! $product_id ) {
						return new WP_REST_Response( [
							'message' => 'Please enter correct License key!',
						], 400 );
					}
		
					$license_status_query = 'SELECT `res_lic_status` FROM `' . $cms_license_management_tbl . '` WHERE `res_product_id` = "' . $product_id . '" AND `res_lic_key` = "' . $license_key . '" AND `registered_domain` = "' . $registered_domain . '"';
			
					$license_status = $wpdb->get_var( $license_status_query );
		
					if( null == $license_status ) {
						return new WP_REST_Response( [
							'message' => 'Something went wrong! Please try after sometime.',
						], 400 );
					}
			
					return new WP_REST_Response( [
						'message' => $license_status,
					], 200 );
					break;  
				
				case 'res_activate':
					/**
					 * Activate request handler
					 */
					if ( ! $product_id ) {
						return new WP_REST_Response( [
							'message' => 'Please enter correct License key!',
						], 400 );
					}
			
					$current_date            = date ("Y-m-d");
					$current_date_plus_1year = date('Y-m-d', strtotime('+1 year'));
					$created_date            = $current_date;
					$expiry_date             = $current_date_plus_1year;
			
					$fields                      = array();
					$fields['res_product_id']    = $product_id;
					$fields['res_lic_key']       = $license_key;
					$fields['registered_domain'] = $registered_domain;
					$fields['res_lic_status']    = 'active';
					$fields['date_created']      = $created_date;
					$fields['date_expiry']       = $expiry_date;
			
					$result = $wpdb->insert( $cms_license_management_tbl, $fields);
					$id     = $wpdb->insert_id;
			
					if( false ===  $result && ! $id ) {
						return new WP_REST_Response( [
							'message' => 'Something went wrong! Please try after sometime.',
						], 400 );
					}
			
					return new WP_REST_Response( [
						'message' => 'Your license is activated.',
					], 200 );
					break;

				case 'res_deactivate':
					/**
					 * Deactivate request handler
					 */
					if ( ! $product_id ) {
						return new WP_REST_Response( [
							'message' => 'Something went wrong! Please try after sometime.',
						], 400 );
					}
			
					$where = array( 
						'res_product_id'    => $product_id,
						'res_lic_key'       => $license_key,
						'registered_domain' => $registered_domain
					);
			
					$result = $wpdb->delete( $cms_license_management_tbl, $where );
			
					if( false === $result ) {
						return new WP_REST_Response( [
							'message' => 'Something went wrong! Please try after sometime.',
						], 400 );
					}
			
					return new WP_REST_Response( [
						'message' => 'Your license is deactivated.',
					], 200 );
					break;
			}
		}
		else {
			return new WP_REST_Response( [
				'message' => 'Please enter correct License key!',
			], 400 );
		}

	}

	/**
	 * Initialize REST API to get license status, activate and deactivate pro plugin.
	 * 
	 * @since 1.0.0
	 */
	public function cms_license_management_rest_api() {
		
		/**
		 * Get plugin version
		 */
		register_rest_route( 'cms-license-management/v1', '/get-remote-version-info', array(
			'methods' => 'POST',
			'callback' => array( $this, 'cms_license_management_license_handler' ),
			'args' => array(
				  'res_action' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_key' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_email' => array(
					'required' => true,
					'type' => 'string',
				  ),
				  'registered_domain' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
			  ),
			  'permission_callback' => '__return_true'
		  ) );

		/**
		 * Get pro plugin information
		 */
		register_rest_route( 'cms-license-management/v1', '/get-pro-plugin-info', array(
			'methods' => 'POST',
			'callback' => array( $this, 'cms_license_management_license_handler' ),
			'args' => array(
				  'res_action' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_key' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_email' => array(
					'required' => true,
					'type' => 'string',
				  ),
				  'registered_domain' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
			  ),
			  'permission_callback' => '__return_true'
		  ) );

		/**
		 * Get License Status API
		 */
		register_rest_route( 'cms-license-management/v1', '/get-license-status', array(
			'methods' => 'POST',
			'callback' => array( $this, 'cms_license_management_license_handler' ),
			'args' => array(
				  'res_action' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_key' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
				  'license_email' => array(
					'required' => true,
					'type' => 'string',
				  ),
				  'registered_domain' => array(
					  'required' => true,
					  'type' => 'string',
				  ),
			  ),
			  'permission_callback' => '__return_true'
		  ) );

		/**
		 * Activate API
		 */
		register_rest_route( 'cms-license-management/v1', '/activate-license', array(
		  'methods' => 'POST',
		  'callback' => array( $this, 'cms_license_management_license_handler' ),
		  'args' => array(
				'res_action' => array(
					'required' => true,
					'type' => 'string',
				),
				'license_key' => array(
					'required' => true,
					'type' => 'string',
				),
				'license_email' => array(
				  'required' => true,
				  'type' => 'string',
				),
				'registered_domain' => array(
					'required' => true,
					'type' => 'string',
				),
			),
			'permission_callback' => '__return_true'
		) );
	
		/**
		 * Deactivate API
		 */
		register_rest_route( 'cms-license-management/v1', '/deactivate-license', array(
			'methods' => 'POST',
			'callback' => array( $this, 'cms_license_management_license_handler' ),
			'args' => array(
				'res_action' => array(
					'required' => true,
					'type' => 'string',
				),
				'license_key' => array(
					'required' => true,
					'type' => 'string',
				),
				'license_email' => array(
				  'required' => true,
				  'type' => 'string',
				),
				'registered_domain' => array(
					'required' => true,
					'type' => 'string',
				),
			),
			'permission_callback' => '__return_true'
		) );
	}
}
