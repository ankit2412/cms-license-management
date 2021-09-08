<?php
/**
 * The Updater plugin class.
 *
 * This is used to check and maintains the update of this plugin.
 */
class Pro_Updater
{
    /**
     * The plugin current version
     * @var string
     */
    public $current_version;
 
    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;
 
    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;
  
    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;
   
    /**
     * License key
     * @var string
     */
    public $license_key;
    
    /**
     * License email
     * @var string
     */
    public $license_email;
    
    /**
     * Registered Domain
     * @var string
     */
    public $registered_domain;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct( $current_version, $update_path, $plugin_slug, $license_key, $license_email, $registered_domain ) {
        // Set the class public variables
        $this->current_version   = $current_version;
        $this->update_path       = $update_path;
        $this->plugin_slug       = $plugin_slug;
        $this->license_key       = $license_key;
        $this->license_email     = $license_email;
        $this->registered_domain = $registered_domain;

        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
 
        // Define the alternative response for information checking
        add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );
    }
 
    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_update( $transient ) {
       
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
 
        // Get the remote version
        $remote_version = json_decode( $this->getRemote_version() );

        // If a newer version is available, add the update
        if ( version_compare( $this->current_version, $remote_version->new_version, '<' ) ) {
            $obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $remote_version->new_version;
			$obj->url = $remote_version->url;
			$obj->plugin = $this->plugin_slug;
			$obj->package = $remote_version->package;
			$obj->tested = $remote_version->tested;
			$transient->response[$this->plugin_slug] = $obj;
        }

        return $transient;
    }
 
    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function check_info( $false, $action, $arg )
    {
        if ( $arg->slug === $this->slug ) {
            $information = json_decode ( $this->getRemote_information() );
            return $information;
        }
        
        return false;
    }
 
    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function getRemote_version() {
        $request = wp_remote_post( 
            $this->update_path . 'get-remote-version-info',
            array(
                'method'  => 'POST',
			    'timeout' => 45,
                'body' => array(
                    'res_action'        => 'res_get_remote_version',
                    'license_key'       => $this->license_key,
                    'license_email'     => $this->license_email,
                    'registered_domain' => $this->registered_domain,
                )
            )
        );

        if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
            $reponse = json_decode( wp_remote_retrieve_body( $request ) );
            return $reponse->message;
        }

        return false;
    }
 
    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function getRemote_information() {
        $request = wp_remote_post(
            $this->update_path . 'get-pro-plugin-info',
            array(
                'method'  => 'POST',
			    'timeout' => 45,
                'body' => array(
                    'res_action' => 'res_get_plugin_info',
                    'license_key'       => $this->license_key,
                    'license_email'     => $this->license_email,
                    'registered_domain' => $this->registered_domain,
                )
            )
        );

        if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
            $reponse = json_decode( wp_remote_retrieve_body( $request ) );
            return $reponse->message;
        }

        return false;
    }
 
}