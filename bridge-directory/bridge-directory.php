<?php
/**
 * Plugin Name: Bridge Directory
 * Plugin URI: https://github.com/RAHB-REALTORS-Association/Bridge-Directory-WP
 * Description: Displays a searchable directory of offices using the Bridge Interactive API.
 * Version: 0.3.4
 * Author: Cornerstone Association of REALTORS
 * Author URI: https://www.cornerstone.inc
 * License: GPL-2.0
 * Text Domain: bridge-directory
 */
 
defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use BridgeDirectory\AJAX_Handler;
use BridgeDirectory\API_Client;
use BridgeDirectory\DB_Handler;
use BridgeDirectory\Search_Handler;
use BridgeDirectory\Block_Register;
use BridgeDirectory\Settings_Page;
use BridgeDirectory\Data_Sync;

// Initialize Settings Page
$settings_page = new Settings_Page();
$settings_page->register();

// Initialize DB Handler
$db_handler = new DB_Handler();

// Initialize Search Handler
$search_handler = new Search_Handler( $db_handler );

// Initialize Block Register
$block_register = new Block_Register( $search_handler );
$block_register->register();

// Initialize Data Sync
$data_sync = new Data_Sync( $db_handler );

// Initialize AJAX Handler
$ajax_handler = new AJAX_Handler( $search_handler );

/**
 * Register Activation and Deactivation Hooks
* These hooks delegate scheduling tasks to the Data_Sync class.
*/
register_activation_hook( __FILE__, [ $data_sync, 'activate_plugin' ] );
register_deactivation_hook( __FILE__, [ $data_sync, 'deactivate_plugin' ] );
