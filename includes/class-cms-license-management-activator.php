<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.cmsminds.com/
 * @since      1.0.0
 *
 * @package    Cms_License_Management
 * @subpackage Cms_License_Management/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cms_License_Management
 * @subpackage Cms_License_Management/includes
 * @author     cmsMinds <info@cmsminds.com>
 */
class Cms_License_Management_Activator {

	/**
	 * Create License management table on plugin activation
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$cms_license_management_tbl = CMS_LICENSE_MANAGEMENT_TABLE;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		else {
			$charset_collate = "DEFAULT CHARSET=utf8";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		$clmt_tbl_sql = "CREATE TABLE IF NOT EXISTS " .$cms_license_management_tbl. " (
			id INT NOT NULL AUTO_INCREMENT ,
			res_product_id INT NOT NULL ,
			res_lic_key varchar(255) NOT NULL ,
			registered_domain text NOT NULL ,
			res_lic_status ENUM('pending', 'active', 'deactive', 'blocked', 'expired') NOT NULL DEFAULT 'pending',
			date_created date NOT NULL DEFAULT '0000-00-00',
			date_expiry date NOT NULL DEFAULT '0000-00-00',
			PRIMARY KEY ( id )
			)" . $charset_collate . ";";
		dbDelta( $clmt_tbl_sql );

	}

}
