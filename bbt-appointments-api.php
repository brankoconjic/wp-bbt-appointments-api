<?php
/*
Plugin Name: BBT Appointments API
Description: A plugin to add a custom REST API endpoint for Appointments.
Version: 1.0
Author: Branko Conjic
*/

if (!defined('ABSPATH')) {
    exit;
}

final class BBTAppointmentsAPI {
    private static $instance;    
    public $version = '1.0';
    
    /**
     * Register the REST API routes.
     * 
     * @return void
     * @since 1.0.0
     */
    public function register_rest_routes() {
        $this->includes();
        
        $api_auth = new APIAuth();
        $locations_repository = new LocationsRepository();
        $appointments_repository = new AppointmentsRepository($locations_repository);
        
        $appointments_controller = new AppointmentsController($appointments_repository, $api_auth);
        $appointments_controller->register_routes();
    }
    
    /**
     * Include required files.
     * 
     * @return void
     * @since 1.0.0
     */
    private function includes() {
        require_once BBT_API_PLUGIN_DIR . 'includes/services/APIAuth.php';
        require_once BBT_API_PLUGIN_DIR . 'includes/repositories/AppointmentsRepository.php';
        require_once BBT_API_PLUGIN_DIR . 'includes/repositories/LocationsRepository.php';
        require_once BBT_API_PLUGIN_DIR . 'includes/controllers/AppointmentsController.php';
    }
    
    /**
     * Define plugin constants.
     * 
     * @return void
     * @since 1.0.0
     */
    private function constants() {
        if ( ! defined( 'BBT_API_PLUGIN_DIR' ) ) {
            define( 'BBT_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }
    }
    
    /**
    * Initialize API keys.
    */
    public static function initialize_api_keys() {
        $api_keys = array(password_hash('XXX', PASSWORD_DEFAULT)); // just for testing purposes, should be stored in the database
        update_option('bbt_api_keys', $api_keys);
    }
    
    /**
     * Get the singleton instance.
     * 
     * @return BBTAppointmentsAPI The singleton instance.
     */
    public static function get_instance(): BBTAppointmentsAPI {
        if (null === self::$instance) {
            self::$instance = new BBTAppointmentsAPI;
            self::$instance->constants();
            
            add_action('rest_api_init', array(self::$instance, 'register_rest_routes'));
            add_action('init', array(self::$instance, 'initialize_api_keys'));
        }
        
        return self::$instance;
    }
}

/**
 * Returns the main instance of BBTAppointmentsAPI.
 * 
 * @return BBTAppointmentsAPI
 * @since 1.0.0
 */
function bbt_appointments_api() {
    return BBTAppointmentsAPI::get_instance();
}

// Initialize the plugin.
bbt_appointments_api();