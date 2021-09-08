<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Cms_License_Management_List_Licenses extends WP_List_Table {

	function __construct() {
		global $status, $page;

		parent::__construct(
			array(
				'singular' => 'item',
				'plural'   => 'items',
				'ajax'     => false,
			)
		);

	}

	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	function column_id( $item ) {
		$row_id  = $item['id'];
		$actions = array(
			'delete' => sprintf( '<a href="admin.php?page=cms-license-page&action=cms_delete_license&id=%s" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</a>', $row_id ),
		);
		return sprintf(
			'%1$s <span style="color:silver"></span>%2$s',
			/*$1%s*/ $item['id'],
			/*$2%s*/ $this->row_actions( $actions )
		);
	}


	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item['id']
		);
	}

	function column_active( $item ) {
		if ( $item['active'] == 1 ) {
			return 'active';
		} else {
			return 'inactive';
		}
	}

	function get_columns() {
		$columns = array(
			'cb'                => '<input type="checkbox" />', // Render a checkbox.
			'id'                => 'ID',
			'res_product_id'    => 'Product Reference',
			'res_lic_key'       => 'License Key',
			'registered_domain' => 'Registered Domain',
			'res_lic_status'    => 'Status',
			'date_created'      => 'Date Created',
			'date_expiry'       => 'Expiry',
			
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'             => array( 'id', false ),
			'res_lic_key'    => array( 'res_lic_key', false ),
			'res_lic_status' => array( 'res_lic_status', false ),
			'date_created'   => array( 'date_created', false ),
			'date_expiry'    => array( 'date_expiry', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		if ( 'delete' === $this->current_action() ) {
			check_admin_referer( 'bulk-' . $this->_args['plural'] );
			//Process delete bulk actions
			if ( ! isset( $_REQUEST['item'] ) ) {
				$error_msg = '<p>' . __( 'Error - Please select some records using the checkboxes', 'slm' ) . '</p>';
				echo '<div id="message" class="error fade">' . $error_msg . '</div>';
				return;
			} else {
				$nvp_key                    = $this->_args['singular'];
				$records_to_delete          = $_GET[ $nvp_key ];
				$cms_license_management_tbl = CMS_LICENSE_MANAGEMENT_TABLE;
				foreach ( $records_to_delete as $row ) {
					$wpdb->delete( $cms_license_management_tbl, array( 'id' => $row ) );
				}
				echo '<div id="message" class="updated fade"><p>Selected records deleted successfully!</p></div>';
			}
		}
	}

	function delete_license_key( $key_row_id ) {
		global $wpdb;
		$cms_license_management_tbl = CMS_LICENSE_MANAGEMENT_TABLE;
		$wpdb->delete( $cms_license_management_tbl, array( 'id' => $key_row_id ) );
		$success_msg  = '<div id="message" class="updated"><p><strong>';
		$success_msg .= 'The selected entry was deleted successfully!';
		$success_msg .= '</strong></p></div>';
		echo $success_msg;
	}

	function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
		<div class="postbox">
			<h3 class="hndle"><label for="title"><?php echo __('License Search', 'cms-license-management'); ?></label></h3>
			<div class="inside">
				<p><?php echo __('Search for a license by using email, name, key, domain or product ID', 'cms-license-management'); ?></p>
				<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
				<input type="search" id="<?php echo $input_id; ?>" name="s" size="40" value="<?php _admin_search_query(); ?>" />
				<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
			</div>
		</div>
		<?php
	}

	function prepare_items() {
		
		$per_page     = 50;
		$current_page = $this->get_pagenum();
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		global $wpdb;
		$cms_license_management_tbl = CMS_LICENSE_MANAGEMENT_TABLE;

		$orderby = ! empty( $_GET['orderby'] ) ? strip_tags( $_GET['orderby'] ) : 'id';
		$order   = ! empty( $_GET['order'] ) ? strip_tags( $_GET['order'] ) : 'DESC';

		$order_str = sanitize_sql_orderby( $orderby . ' ' . $order );

		$limit_from = ( $current_page - 1 ) * $per_page;

		if ( ! empty( $_REQUEST['s'] ) ) {
			$search_term = trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) );
			$placeholder = '%' . $wpdb->esc_like( $search_term ) . '%';

			$select = "SELECT `rd` . * ";

			$after_select = "FROM `$cms_license_management_tbl` `rd`
			WHERE `rd`.`res_product_id` LIKE %s
			OR `rd`.`res_lic_key` LIKE %s
			OR `rd`.`registered_domain` LIKE %s
			OR `rd`.`res_lic_status` LIKE %s";

			$after_query = "GROUP BY `rd` . `id` ORDER BY $order_str
			LIMIT $limit_from, $per_page";

			$q = "$select $after_select $after_query";

			$data = $wpdb->get_results(
				$wpdb->prepare(
					$q,
					$placeholder,
					$placeholder,
					$placeholder,
					$placeholder
				),
				ARRAY_A
			);

			$found_rows_q = $wpdb->prepare(
				"SELECT COUNT( * )
				$after_select",
				$placeholder,
				$placeholder,
				$placeholder,
				$placeholder
			);

			$total_items = intval( $wpdb->get_var( $found_rows_q ) );
		} else {
			$after_select = "FROM `$cms_license_management_tbl` `rd`";

			$after_query = "GROUP BY `rd` . `id`
			ORDER BY $order_str
			LIMIT $limit_from, $per_page";

			$q = "SELECT `rd` . * 
				$after_select$after_query";

			$data = $wpdb->get_results( $q, ARRAY_A );

			$found_rows_q = "SELECT COUNT( * )
			$after_select";

			$total_items = intval( $wpdb->get_var( $found_rows_q ) );
		}

		$this->items = $data;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
