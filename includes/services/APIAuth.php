<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAuth {
    /**
    * Validate the API key.
    * 
    * @param WP_REST_Request $request The request object.
    * @return bool|WP_Error True if the API key is valid, WP_Error otherwise.
    */
    public function validate_api_key(WP_REST_Request $request) {
        $api_key = $request->get_header('X-API-Key');

        // Check if the API key is set
        if ( !$api_key || empty($api_key) ) {
            return new WP_Error('no_api_key', 'No API key provided', array('status' => 403));
        }

        // Validate API key
        if (!$this->is_valid_api_key($api_key)) {
            return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 403));
        }
        
        // Return true if the API key is valid.
        return true;
    }
    
    /**
     * Check if the API key is valid.
     * 
     * @param string $api_key The API key.
     * @return bool True if the API key is valid, false otherwise.
     */
    private function is_valid_api_key(string $api_key): bool {
        // Retrieve the valid API keys from the options table
        $hashed_keys = get_option('bbt_api_keys', array());
        
        // Check if the provided API key matches any of the hashed keys
        foreach ($hashed_keys as $hashed_key) {
            if (password_verify($api_key, $hashed_key)) {
                return true;
            }
        }
        
        return false;
    }
}