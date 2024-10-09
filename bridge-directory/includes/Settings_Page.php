<?php
namespace CreaAPI;

defined( 'ABSPATH' ) || exit;

class Settings_Page {
    public function register() {
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings_page() {
        add_options_page(
            'CREA API Settings',
            'CREA API',
            'manage_options',
            'crea-api',
            [ $this, 'settings_page_html' ]
        );
    }

    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Process sync and clear cache actions
        if ( isset( $_POST['crea_api_full_sync'] ) ) {
            check_admin_referer( 'crea_api_full_sync' );
            $data_sync = new Data_Sync( new DB_Handler() );
            $data_sync->full_sync();
            echo '<div class="updated"><p>Full sync initiated.</p></div>';
        }

        if ( isset( $_POST['crea_api_clear_cache'] ) ) {
            check_admin_referer( 'crea_api_clear_cache' );
            $db_handler = new DB_Handler();
            $db_handler->clear_data();
            echo '<div class="updated"><p>Data cleared.</p></div>';
        }

        $db_handler = new DB_Handler();
        $total_records = $db_handler->get_total_records();

        ?>
        <div class="wrap">
            <h1>CREA API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'crea_api_settings' );
                do_settings_sections( 'crea_api_settings' );
                submit_button();
                ?>
            </form>

            <h2>Data Management</h2>
            <p>Total Records: <?php echo esc_html( $total_records ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'crea_api_full_sync' ); ?>
                <input type="hidden" name="crea_api_full_sync" value="1">
                <?php submit_button( 'Full Sync', 'primary', 'submit', false ); ?>
            </form>
            <form method="post" style="margin-top: 10px;">
                <?php wp_nonce_field( 'crea_api_clear_cache' ); ?>
                <input type="hidden" name="crea_api_clear_cache" value="1">
                <?php submit_button( 'Clear Data', 'secondary', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting( 'crea_api_settings', 'crea_api_access_token', [
            'sanitize_callback' => [ $this, 'validate_input' ],
        ] );

        register_setting( 'crea_api_settings', 'crea_api_dataset_name', [
            'sanitize_callback' => [ $this, 'validate_input' ],
        ] );

        register_setting( 'crea_api_settings', 'crea_api_sync_interval', [
            'sanitize_callback' => 'absint',
            'default'           => 24,
        ] );

        register_setting( 'crea_api_settings', 'crea_api_advanced_query', [
            'sanitize_callback' => [ $this, 'sanitize_query' ],
            'default'           => '',
        ] );

        add_settings_section(
            'crea_api_main',
            'API Settings',
            function() { echo '<p>Enter your API settings below:</p>'; },
            'crea_api_settings'
        );

        add_settings_field(
            'crea_api_access_token',
            'Access Token',
            [ $this, 'access_token_field_html' ],
            'crea_api_settings',
            'crea_api_main'
        );

        add_settings_field(
            'crea_api_dataset_name',
            'Dataset Name',
            [ $this, 'dataset_name_field_html' ],
            'crea_api_settings',
            'crea_api_main'
        );

        add_settings_field(
            'crea_api_sync_interval',
            'Sync Interval (hours)',
            [ $this, 'sync_interval_field_html' ],
            'crea_api_settings',
            'crea_api_main'
        );

        add_settings_field(
            'crea_api_advanced_query',
            'Advanced Query Filter',
            [ $this, 'advanced_query_field_html' ],
            'crea_api_settings',
            'crea_api_main'
        );
    }

    public function access_token_field_html() {
        $value = get_option( 'crea_api_access_token', '' );
        echo '<input type="text" name="crea_api_access_token" value="' . esc_attr( $value ) . '" />';
    }

    public function dataset_name_field_html() {
        $value = get_option( 'crea_api_dataset_name', '' );
        echo '<input type="text" name="crea_api_dataset_name" value="' . esc_attr( $value ) . '" />';
    }

    public function sync_interval_field_html() {
        $value = get_option( 'crea_api_sync_interval', 24 );
        echo '<input type="number" name="crea_api_sync_interval" value="' . esc_attr( $value ) . '" min="1" />';
    }

    public function advanced_query_field_html() {
        $value = get_option( 'crea_api_advanced_query', '' );
        echo '<input type="text" name="crea_api_advanced_query" value="' . esc_attr( $value ) . '" style="width: 100%;" />';
        echo '<p class="description">Enter additional query parameters for the API requests. Separate parameters with <code>&</code>. Do not include <code>OfficeStatus</code>. Example: <code>OriginatingSystemName.in=Hamilton&OriginatingSystemName.in=Mississauga</code></p>';
    }

    public function validate_input( $input ) {
        return sanitize_text_field( $input );
    }

    public function sanitize_query( $input ) {
        // Allow characters commonly used in URLs and query parameters
        return preg_replace( '/[^a-zA-Z0-9=&._\-+]/', '', $input );
    }
}
