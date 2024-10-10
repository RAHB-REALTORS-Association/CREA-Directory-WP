<?php
namespace CreaAPI;

defined('ABSPATH') || exit;

class Data_Sync {
    private $api_client;
    private $db_handler;

    public function __construct($db_handler) {
        $this->api_client = new API_Client();
        $this->db_handler = $db_handler;

        // Hook into WordPress actions and filters
        add_filter('cron_schedules', [$this, 'add_custom_cron_schedule']);
        add_action('crea_api_incremental_sync', [$this, 'incremental_sync']);
        add_action('update_option_crea_api_sync_interval', [$this, 'sync_interval_updated'], 10, 2);
    }

    /**
     * Define Custom Cron Schedule Based on Settings
     */
    public function add_custom_cron_schedule($schedules) {
        $interval_hours = get_option('crea_api_sync_interval', 24);
        $interval_seconds = absint($interval_hours) * HOUR_IN_SECONDS;

        $schedules['crea_api_sync_interval'] = [
            'interval' => $interval_seconds,
            'display'  => sprintf(__('Every %d Hours', 'crea-api'), $interval_hours),
        ];

        return $schedules;
    }

    /**
     * Activation Hook: Schedule the Incremental Sync Cron Event
     */
    public function activate_plugin() {
        $this->add_custom_cron_schedule(wp_get_schedules());
        $this->schedule_incremental_sync();
        $this->full_sync();
    }

    /**
     * Deactivation Hook: Unschedule the Incremental Sync Cron Event
     */
    public function deactivate_plugin() {
        $this->unschedule_incremental_sync();
    }

    /**
     * Schedule the Incremental Sync Cron Event
     */
    public function schedule_incremental_sync() {
        if (!wp_next_scheduled('crea_api_incremental_sync')) {
            wp_schedule_event(time(), 'crea_api_sync_interval', 'crea_api_incremental_sync');
            error_log('Crea API: Incremental sync scheduled.');
        } else {
            error_log('Crea API: Incremental sync already scheduled.');
        }
    }

    /**
     * Unschedule the Incremental Sync Cron Event
     */
    public function unschedule_incremental_sync() {
        $timestamp = wp_next_scheduled('crea_api_incremental_sync');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'crea_api_incremental_sync');
            error_log('Crea API: Incremental sync unscheduled.');
        }
    }

    /**
     * Reschedule the Incremental Sync Cron Event When Interval Changes
     */
    public function sync_interval_updated($old_value, $new_value) {
        if ($old_value !== $new_value) {
            $this->reschedule_incremental_sync();
            error_log('Crea API: Sync interval updated from ' . $old_value . ' to ' . $new_value . ' hours.');
        }
    }

    /**
     * Reschedule the Incremental Sync Cron Event
     */
    public function reschedule_incremental_sync() {
        $this->unschedule_incremental_sync();
        $this->schedule_incremental_sync();
        error_log('Crea API: Incremental sync rescheduled with new interval.');
    }

    /**
     * Perform a Full Synchronization
     */
    public function full_sync() {
        error_log('Crea API: Starting full sync.');
        $offices = $this->api_client->fetch_all_offices();
        if (is_wp_error($offices)) {
            error_log('Crea API Full Sync Error: ' . $offices->get_error_message());
            return;
        }
        $this->db_handler->save_offices($offices);
        update_option('crea_api_last_full_sync', gmdate('Y-m-d\TH:i:s\Z'));
        error_log('Crea API: Full sync completed.');
    }

    /**
     * Perform an Incremental Synchronization
     */
    public function incremental_sync() {
        error_log('Crea API: Starting incremental sync.');
        $last_sync = get_option('crea_api_last_sync', '1970-01-01T00:00:00Z');

        $updated_offices = $this->api_client->fetch_updated_offices($last_sync);
        $inactive_offices = $this->api_client->fetch_inactive_offices($last_sync);

        if (!is_wp_error($updated_offices)) {
            $this->db_handler->update_offices($updated_offices);
            error_log('Crea API: Updated offices synchronized.');
        } else {
            error_log('Crea API Incremental Sync Error (Updated Offices): ' . $updated_offices->get_error_message());
        }

        if (!is_wp_error($inactive_offices)) {
            $this->db_handler->remove_offices($inactive_offices);
            error_log('Crea API: Inactive offices removed.');
        } else {
            error_log('Crea API Incremental Sync Error (Inactive Offices): ' . $inactive_offices->get_error_message());
        }

        $current_time_utc = gmdate('Y-m-d\TH:i:s\Z');
        update_option('crea_api_last_sync', $current_time_utc);
        error_log('Crea API: Incremental sync completed. Updated last sync time to ' . $current_time_utc);
    }
}
