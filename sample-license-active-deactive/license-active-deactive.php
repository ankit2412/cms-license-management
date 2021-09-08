<?php

/**
 * Store URL
 */
if ( ! defined( 'STORE_URL' ) ) {
	define ( 'STORE_URL', '#YOUR STRORE URL#' );
}

/**
 * Base plugin path
 * for update funtionality
 */
if ( ! defined( 'PRO_PLUGIN_BASE' ) ) {
	define( 'PRO_PLUGIN_BASE', plugin_basename( __FILE__ ) );
}

// Plugin path.
if ( ! defined( 'PRO_PLUGIN_PATH' ) ) {
	define( 'PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin URL.
if ( ! defined( 'PRO_PLUGIN_URL' ) ) {
	define( 'PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once PRO_PLUGIN_PATH . 'includes/class-pro-updater.php';

/**
 * Make API call
 * 
 * @param string $api API call namespace
 * @param string $method API call method
 * @param array $param API call body
 * @return string json encoded response string
 */
function pro_call_api( $api = 'activate-license', $method = 'POST', $param = array() ) {
    $api_params = array(
        'method'  => $method,
        'timeout' => 45,
        'body'    => $param,
    );

    $url         = esc_url_raw( STORE_URL . '/wp-json/cms-license-management/v1/' . $api );
    $return_data = array();

    if ( 'POST' == $method ) {
        $response = wp_remote_post( $url, $api_params );
    }
    else {
        $response = wp_remote_get( $url, $api_params );
    }

    if ( is_wp_error( $response ) ) {
        $return_data['error'] = __( 'Unexpected Error! The query returned with an error.', 'your-text-domain' );
    }

    $api_data    = json_decode( wp_remote_retrieve_body( $response ) );
    $status_code = wp_remote_retrieve_response_code( $response );
    
    if ( ! empty ( $api_data ) ) {
        if( 200 == $status_code ) {
            $return_data['success'] = __( $api_data->message, 'your-text-domain' );
        }
        else {
            $return_data['error'] = __( $api_data->message, 'your-text-domain' );
        }
    }
    else {
        $return_data['error'] = __( 'Uable to reach remote server!', 'your-text-domain' );
    }
    
    $return_data['status_code'] = $status_code;

    return json_encode( $return_data );
}

/**
 * Get Plugin license status using REST API
 * 
 * @return string json encoded response string
 */
function pro_get_license_status() {

    $api               = 'get-license-status';
    $res_license_key   = get_option( 'res_license_key' );
    $res_license_email = get_option( 'res_license_email' );
    $body_params       = array(	
                        'res_action'        => 'res_get_license_status',
                        'license_key'       => $res_license_key,
                        'license_email'     => $res_license_email,
                        'registered_domain' => $_SERVER['SERVER_NAME'],
                        );
    $api_reposne     = json_decode ( pro_call_api( $api, 'POST', $body_params ) );
    
    return json_encode ( $api_reposne );
}

/**
 * Add option page to active/deactive plugin license
 *
 * @return void
 */
function pro_license_menu() {
    add_options_page(
        'Activate License',
        'Activate License',
        'manage_options',
        'pro-license-management-page',
        'pro_license_management_page',
    );
}
/**
 * License Activation
 * 
 * @since 1.0.0
 */
add_action( 'admin_menu', 'pro_license_menu' );

/**
 * License Management page html
 *
 * @return void
 */
function pro_license_management_page() {

    echo '<div class="wrap">';
    echo '<h2>' . __( 'License Management', 'your-text-domain' ) . '</h2>';

    if ( ( isset( $_REQUEST[ 'res_license_key' ] ) && ! empty ( $_REQUEST[ 'res_license_key' ] ) ) && ( isset( $_REQUEST[ 'res_license_email' ] ) && ! empty ( $_REQUEST[ 'res_license_email' ] ) ) ) {

        $res_license_key   = sanitize_text_field( $_REQUEST[ 'res_license_key' ] );
        $res_license_email = sanitize_email( $_REQUEST[ 'res_license_email' ] );
        
        if ( $res_license_email ) {
            $body_params     = array(
                                'license_key'       => $res_license_key,
                                'license_email'     => $res_license_email,
                                'registered_domain' => $_SERVER['SERVER_NAME'],
                            );
            $api             = '';

            if ( isset( $_REQUEST[ 'activate_license' ] ) ) {
                $api                       = 'activate-license';
                $body_params['res_action'] = 'res_activate';
            }
            
            if ( isset( $_REQUEST[ 'deactivate_license' ] ) ) {
                $api                       = 'deactivate-license';
                $body_params['res_action'] = 'res_deactivate';
            }

            $api_reposne = json_decode ( pro_call_api( $api, 'POST', $body_params ) );
            
            if( isset ( $api_reposne->status_code ) && 200 === $api_reposne->status_code && ! empty ( $api_reposne->success ) && 'activate-license' == $api ) {
                update_option( 'res_license_key', $res_license_key );
                update_option( 'res_license_email', $res_license_email );
            }
            else if( isset ( $api_reposne->status_code ) && 200 === $api_reposne->status_code && ! empty ( $api_reposne->success ) && 'deactivate-license' == $api ) {
                update_option( 'res_license_key', '' );
                update_option( 'res_license_email', '' );
                pro_check_updates();
            }

            if ( ! empty ( $api_reposne->success ) ) {
                echo '<p>' . $api_reposne->success . '</p>';
            }
            else {
                echo '<p>' . $api_reposne->error . '</p>';
            }
        }
        else {
            echo '<p>Please enter correct Registered email address!!</p>';
        }
    }

    ?>
    <p><?php echo __( 'Please enter the license key for this product to activate it. You were given a license key when you purchased this item.', 'your-text-domain' ); ?></p>
    <form class='cms-license-management-form' action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="res_license_key"><?php echo __( 'License Key','your-text-domain' ); ?></label></th>
                <td ><input class="regular-text" type="text" id="res_license_key" name="res_license_key" value="<?php echo get_option('res_license_key'); ?>" ></td>
            </tr>
            <tr>
                <th style="width:100px;"><label for="res_license_email"><?php echo __( 'Registered Email','your-text-domain' ); ?></label></th>
                <td ><input class="regular-text" type="text" id="res_license_email" name="res_license_email" value="<?php echo get_option('res_license_email'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button button-primary" />
            <input type="submit" name="deactivate_license" value="Deactivate" class="button" />
        </p>
    </form>
    <?php
    
    echo '</div>';
}

/**
 * Check pro pluigin updates
 * 
 * @since    1.0.0
 */
function pro_check_updates() {

    $plugin_current_version = '1.0.0';
    $plugin_slug            = PRO_PLUGIN_BASE;
    $license_status         = json_decode ( pro_get_license_status() );

    if ( isset( $license_status->success ) && 'active' == $license_status->success ) {
        
        $plugin_remote_path = STORE_URL . '/wp-json/cms-license-management/v1/';
        $res_license_key    = get_option( 'res_license_key' );
        $res_license_email  = get_option( 'res_license_email' );
        $registered_domain  = $_SERVER['SERVER_NAME'];

        new Pro_Updater( $plugin_current_version, $plugin_remote_path, $plugin_slug, $res_license_key, $res_license_email, $registered_domain );
    }
    else {
        /**
         * remove pre set site transient update plugin entry.
         * 
         * @since 1.0.0
         */
        
        $transient = get_site_transient( 'update_plugins' );
        
        if ( ! empty( $transient->response[$plugin_slug] ) ) :
            $pro_plugin_update = $transient->response[$plugin_slug];
            $pro_plugin_update->new_version = $plugin_current_version;

            unset( $transient->response[$plugin_slug] );
            $transient->no_update[$plugin_slug] = $pro_plugin_update;

            set_site_transient( 'update_plugins', $transient );
        endif;
    }
}
/**
 * Update functionality
 * 
 * @since 1.0.0
 */		
add_action( 'init', 'pro_check_updates' );