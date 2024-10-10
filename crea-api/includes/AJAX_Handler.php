<?php
namespace CreaAPI;

defined( 'ABSPATH' ) || exit;

class AJAX_Handler {
    private $search_handler;

    public function __construct( $search_handler ) {
        $this->search_handler = $search_handler;

        add_action( 'wp_ajax_crea_api_load_offices', [ $this, 'load_offices' ] );
        add_action( 'wp_ajax_nopriv_crea_api_load_offices', [ $this, 'load_offices' ] );
    }

    public function load_offices() {
        check_ajax_referer( 'crea_api_nonce', 'nonce' );

        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        $limit = 20; // Number of results per page

        $offices = $this->search_handler->search_offices( $query, $page, $limit );

        wp_send_json_success( [ 'offices' => $offices ] );
    }
}
