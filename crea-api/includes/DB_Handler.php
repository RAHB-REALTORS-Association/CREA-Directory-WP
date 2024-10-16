<?php
namespace CreaAPI;

defined('ABSPATH') || exit;

class DB_Handler {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'crea_api_offices';
    }

    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Plugin Activation: Create the database table.
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crea_api_offices';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            OfficeNationalAssociationId varchar(255) NOT NULL,
            OfficeName varchar(255) NOT NULL,
            OfficeAddress1 varchar(255),
            OfficeAddress2 varchar(255),
            OfficeCity varchar(100),
            OfficeStateOrProvince varchar(100),
            OfficePostalCode varchar(50),
            OfficePhone varchar(50),
            OfficePhoneNormalized varchar(50),
            OfficeFax varchar(50),
            OfficeEmail varchar(100),
            SocialMediaWebsiteUrlOrId varchar(255),
            PRIMARY KEY  (OfficeNationalAssociationId)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Plugin Deactivation: Optionally drop the database table.
     */
    public static function deactivate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'crea_api_offices';
        $table_name_escaped = esc_sql($table_name);

        $sql = "DROP TABLE IF EXISTS `$table_name_escaped`";
        $wpdb->query($sql);
    }

    /**
     * Save offices to the database.
     */
    public function save_offices($offices) {
        global $wpdb;
        foreach ($offices as $office) {
            // Normalize phone number
            $office_phone_normalized = preg_replace('/\D/', '', $office['OfficePhone']);

            $wpdb->replace(
                $this->table_name,
                [
                    'OfficeNationalAssociationId' => $office['OfficeNationalAssociationId'],
                    'OfficeName'                  => $office['OfficeName'],
                    'OfficeAddress1'              => $office['OfficeAddress1'],
                    'OfficeAddress2'              => $office['OfficeAddress2'],
                    'OfficeCity'                  => $office['OfficeCity'],
                    'OfficeStateOrProvince'       => $office['OfficeStateOrProvince'],
                    'OfficePostalCode'            => $office['OfficePostalCode'],
                    'OfficePhone'                 => $office['OfficePhone'],
                    'OfficePhoneNormalized'       => $office_phone_normalized,
                    'OfficeFax'                   => $office['OfficeFax'],
                    'OfficeEmail'                 => $office['OfficeEmail'],
                    'SocialMediaWebsiteUrlOrId'   => $office['SocialMediaWebsiteUrlOrId'],
                ],
                [
                    '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    '%s', '%s', '%s', '%s', '%s',
                ]
            );
        }
    }

    /**
     * Update offices in the database.
     */
    public function update_offices($offices) {
        $this->save_offices($offices);
    }

    /**
     * Remove offices from the database.
     */
    public function remove_offices($office_ids) {
        global $wpdb;

        if (empty($office_ids)) {
            return;
        }

        $table_name_escaped = esc_sql($this->table_name);
        $placeholders = implode(', ', array_fill(0, count($office_ids), '%s'));

        $sql = "DELETE FROM `$table_name_escaped` WHERE OfficeNationalAssociationId IN ($placeholders)";

        $prepared_sql = $wpdb->prepare($sql, $office_ids);
        $wpdb->query($prepared_sql);
    }

    /**
     * Retrieve all offices from the database.
     */
    public function get_offices() {
        global $wpdb;
        $table_name_escaped = esc_sql($this->table_name);

        $sql = "SELECT * FROM `$table_name_escaped`";

        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }

    /**
     * Get the total number of records in the database.
     */
    public function get_total_records() {
        global $wpdb;
        $table_name_escaped = esc_sql($this->table_name);

        $sql = "SELECT COUNT(*) FROM `$table_name_escaped`";

        $count = $wpdb->get_var($sql);
        return $count;
    }

    /**
     * Clear all data from the database table.
     */
    public function clear_data() {
        global $wpdb;
        $table_name_escaped = esc_sql($this->table_name);

        $sql = "TRUNCATE TABLE `$table_name_escaped`";
        $wpdb->query($sql);
    }
}
