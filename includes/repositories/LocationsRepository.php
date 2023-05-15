<?php
if (!defined('ABSPATH')) {
    exit;
}

class LocationsRepository {
    /**
     * Gets store id by name.
     * 
     * @param string $location_name The location name.
     * @return int|null $store_id The store ID or null if not found.
     */
    public function get_store_id( string $location_name ): ?int {
        $location_id = null;
        $locations = $this->get_locations();
        
        foreach ($locations as $location) {
            if ($location['title'] === $location_name) {
                $location_id = $location['id'];
                break;
            }
        }
        
        return $location_id;
    }
    
    /**
     * Get all locations.
     * 
     * @return array $locations The locations.
     */
    public function get_locations() {
        $locations = array();
        
        // Query args
        $args = array(
            'post_type' => 'locations',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
    
        $locations_query = new WP_Query( $args );
        
        if ( $locations_query->have_posts() ) {
            while ( $locations_query->have_posts() ) {
                $locations_query->the_post();
                $locations[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                );
            }
            
            wp_reset_postdata();
            
        }
        
        return $locations;
    }
}