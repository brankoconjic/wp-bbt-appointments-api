<?php
if (!defined('ABSPATH')) {
    exit;
}

class AppointmentsController extends WP_REST_Controller {
    private $appointments_repository;
    private $api_auth;
    
    /**
     * The number of items per page.
     */
    private int $per_page = 400;

    protected $namespace = 'bbt/v1';
    protected $rest_base = 'appointments';
    
    public function __construct(AppointmentsRepository $appointments_repository, APIAuth $api_auth) {
        $this->appointments_repository = $appointments_repository;
        $this->api_auth = $api_auth;
    }
    
    /**
     * Register the routes for the objects of the controller.
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'handle_request'),
                'permission_callback' => array($this->api_auth, 'validate_api_key'),
            ),
        ) );
    }
    
    /**
     * Handle the request and return API JSON response.
     * 
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response $response The response object.
     */
    public function handle_request( WP_REST_Request $request ): WP_REST_Response {
        $page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;
        
        // Get date params
        $date = array(
            'day' => $request->get_param('day'),
            'month' => $request->get_param('month'),
            'year' => $request->get_param('year'),
        );
        
        // Validate date params
        $formatted_date = DateTimeImmutable::createFromFormat('d-m-Y', $date['day'] . '-' . $date['month'] . '-' . $date['year']);
        $date_errors = DateTimeImmutable::getLastErrors();
        
        // Return error if the date is invalid
        if ( ! $formatted_date || $date_errors['warning_count'] ) {
            return new WP_REST_Response(
                array(
                    'code' => 'invalid_date',
                    'message' => 'Invalid date',
                    'data' => array (
                        'status' => 400
                    )
                ), 400);
        }
        
        // Get appointments
        $appointments = $this->appointments_repository->get_appointments( $page, $this->per_page, $date );
        
        // Get total appointments
        $total_appointments = $this->appointments_repository->get_total_appointments($date);
        
        // Calculate total pages
        $total_pages = ceil($total_appointments / $this->per_page);
        
        // Create a new instance of WP_REST_Response
        $response = new WP_REST_Response($appointments);

        // Set the headers
        $response->header( 'X-TotalResults', $total_appointments);
        $response->header( 'X-Pagination-CurrentPage', $page );
        $response->header( 'X-Pagination-PerPage', $this->per_page );
        $response->header( 'X-Pagination-TotalPages', $total_pages );
        
        return $response;
    }
}
