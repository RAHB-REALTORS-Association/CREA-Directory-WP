<?php
namespace CreaAPI;

defined( 'ABSPATH' ) || exit;

class Block_Register {
    private $search_handler;

    public function __construct( $search_handler ) {
        $this->search_handler = $search_handler;
    }

    public function register() {
        add_action( 'init', [ $this, 'register_blocks' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function register_blocks() {
        wp_register_script(
            'crea-api-block',
            plugins_url( 'build/blocks.js', __DIR__ ),
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
            '1.0.0',
            true
        );

        register_block_type( 'crea-api/office-list', [
            'editor_script'   => 'crea-api-block',
            'attributes'      => [
                'columns'  => [
                    'type'    => 'number',
                    'default' => 3,
                ],
            ],
            'render_callback' => [ $this, 'render_block' ],
        ] );
    }

    public function enqueue_scripts() {
        if ( has_block( 'crea-api/office-list' ) ) {
            wp_enqueue_script(
                'crea-api-frontend',
                plugins_url( 'assets/js/crea-api.js', __DIR__ ),
                [ 'jquery' ],
                '1.0.0',
                true
            );

            wp_enqueue_style(
                'crea-api-style',
                plugins_url( 'assets/css/crea-api.css', __DIR__ ),
                [],
                '1.0.0'
            );

            wp_localize_script( 'crea-api-frontend', 'bridgeDirectory', [
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'columns'    => get_option( 'crea_api_columns', 3 ),
                'nonce'      => wp_create_nonce( 'crea_api_nonce' ),
            ] );
        }
    }

    public function render_block( $attributes ) {
        ob_start();
        ?>
        <div class="crea-api-grid">
            <div class="crea-api-search">
                <input type="text" id="crea-api-search-input" placeholder="Search...">
            </div>
            <div id="crea-api-cards" class="crea-api-cards">
                <!-- Cards will be dynamically added here -->
            </div>
            <div id="crea-api-loader" class="crea-api-loader" style="display: none;">
                Loading...
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
