<?php
if (!defined('ABSPATH')) {
    exit;
}

class AppointmentsRepository {
    private LocationsRepository $locations_repository;
    
    public function __construct(LocationsRepository $locations_repository) {
        $this->locations_repository = $locations_repository;
    }
    
    /**
     * Get appointments.
     * 
     * @param int $page The page number.
     * @param int $per_page The number of items per page.
     * @return array $appointments The appointments.
     */
    public function get_appointments( int $page = 1, int $per_page = 40 ): array {
        $appointments = array();
        
        // Query args
        $args = array(
            'post_type' => 'appointments',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        );
    
        $appointments_query = new WP_Query( $args );
        
        if ( $appointments_query->have_posts() ) {
            while ( $appointments_query->have_posts() ) {
                $appointments_query->the_post();
                $location_field = get_field('field_63dfb311943e7');
                $appointments[] = $this->extract_appointment($location_field);
            }
            
            wp_reset_postdata();
        }

        return $appointments;
    }

    /**
     * Get the appointment preference.
     * 
     * @param array $appointment_preference The appointment preference.
     * @return array $appointment The appointment.
     */
    public function get_total_appointments():int {
        $args = array(
            'post_type'   => 'appointments',
            'post_status' => 'publish',
        );
    
        $appointments_query = new WP_Query( $args );

        return $appointments_query->found_posts;
    }
    
     /**
     * Extract appointment details from the appointment string.
     * 
     * @param string $appt The appointment preference string.
     * @return array|false - The new appointment array containing location(s), or false if the string is empty or not passed.
     */
    private function extract_appointment( string $appt ): array|false {
        if (empty($appt)) {
            return false;
        }
        
        $appointment_parts = explode("\n---\n", $appt);
        
        // Extract location
        $location = trim(str_replace("Where: ", "", $appointment_parts[1]));
        $location = explode(',', $location);
        $location = $location[0];
        
        // Create the array
        $app_obj = array();
        
        // Can use get_the_ID() and get_the_date() because we are in the loop
        $app_obj["appt_id"] = get_the_ID();
        $app_obj["appt_create_date"] = get_the_date();

        // Check for JS 'undefined' passed value and skip it
        $app_obj["store_id"] = $this->locations_repository->get_store_id($location);
        $app_obj["store_name"] = $location;

        // Return the array
        return $app_obj;
    }
}
