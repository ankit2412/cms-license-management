<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.cmsminds.com/
 * @since             1.0.0
 * @package           Cms_License_Management
 *
 * @wordpress-plugin
 * Plugin Name:       CMS License Management
 * Plugin URI:        https://www.github.com/cmsminds
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            cmsMinds
 * Author URI:        https://www.cmsminds.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cms-license-management
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CMS_LICENSE_MANAGEMENT_VERSION', '1.0.0' );

/**
 * Cms License Management Table
 */
if ( ! defined( 'CMS_LICENSE_MANAGEMENT_TABLE' ) ) {
	global $wpdb;
	define( 'CMS_LICENSE_MANAGEMENT_TABLE', $wpdb->prefix . 'cms_license_management' );
}

/**
 * Cms License Management Store URL
 */
if ( ! defined( 'CMS_LICENSE_MANAGEMENT_STORE' ) ) {
	define( 'CMS_LICENSE_MANAGEMENT_STORE', site_url('/') );
}

/**
 * Woocommerce REST API keys
 */
if ( ! defined( 'CMS_LICENSE_MANAGEMENT_CK' ) ) {
 define( 'CMS_LICENSE_MANAGEMENT_CK', 'ck_c921a3c94d115a74e9c301d8d4314cefe83c514e' );
}
if ( ! defined( 'CMS_LICENSE_MANAGEMENT_CS' ) ) {
	define( 'CMS_LICENSE_MANAGEMENT_CS', 'cs_7e0b63f54ae4cb86d0efe85837f7d30fcdf83c45' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cms-license-management-activator.php
 */
function activate_cms_license_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cms-license-management-activator.php';
	Cms_License_Management_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cms-license-management-deactivator.php
 */
function deactivate_cms_license_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cms-license-management-deactivator.php';
	Cms_License_Management_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cms_license_management' );
register_deactivation_hook( __FILE__, 'deactivate_cms_license_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cms-license-management.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cms_license_management() {

	$plugin = new Cms_License_Management();
	$plugin->run();
}

/**
 * This initiates the plugin.
 * Checks for the required plugins to be installed and active.
 */
function cms_license_management_plugins_loaded_callback() {
	
	$active_plugins = get_option( 'active_plugins' );
	$is_wc_active   = in_array( 'woocommerce/woocommerce.php', $active_plugins, true );

	if ( current_user_can( 'activate_plugins' ) && ( false === $is_wc_active ) ) {
		add_action( 'admin_notices', 'cms_license_management_admin_notices_callback' );
	} else {
		run_cms_license_management();
	}
}

add_action( 'plugins_loaded', 'cms_license_management_plugins_loaded_callback' );

/**
 * Show admin notice for the required plugins not active or installed.
 */
function cms_license_management_admin_notices_callback() {
	$this_plugin_data   = get_plugin_data( __FILE__ );
	$this_plugin        = $this_plugin_data['Name'];
	$wc_plugin          = 'WooCommerce';
	?>
	<div class="error">
		<p>
			<?php
			/* translators: 1: %s: strong tag open, 2: %s: strong tag close, 3: %s: this plugin, 4: %s: woocommerce plugin */
			echo wp_kses_post( sprintf( __( '%1$s%3$s%2$s is ineffective as it requires %1$s%4$s%2$s to be installed and active.', 'cms-license-management' ), '<strong>', '</strong>', esc_html( $this_plugin ), esc_html( $wc_plugin ) ) );
			?>
		</p>
	</div>
	<?php
}


