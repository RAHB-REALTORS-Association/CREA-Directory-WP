<?php
namespace CreaAPI;

defined( 'ABSPATH' ) || exit;

class Search_Handler {
    private $db_handler;

    public function __construct( $db_handler ) {
        $this->db_handler = $db_handler;
    }

    public function search_offices( $query = '', $page = 1, $limit = 20 ) {
        global $wpdb;

        $offset = ( $page - 1 ) * $limit;
        $table_name = $this->db_handler->get_table_name();
        $table_name_escaped = esc_sql( $table_name );

        $parameters = [];
        $where_clauses = [];

        if ( ! empty( $query ) ) {
            $like_query = '%' . $wpdb->esc_like( $query ) . '%';
            $normalized_query = preg_replace( '/\D/', '', $query );

            // Always search by OfficeName and OfficeEmail
            $where_clauses[] = "(OfficeName LIKE %s OR OfficeEmail LIKE %s)";
            $parameters[] = $like_query;
            $parameters[] = $like_query;

            // Search by normalized phone number if the normalized query has at least 7 digits
            if ( strlen( $normalized_query ) >= 7 ) {
                $normalized_like_query = '%' . $wpdb->esc_like( $normalized_query ) . '%';
                $where_clauses[] = "OfficePhoneNormalized LIKE %s";
                $parameters[] = $normalized_like_query;
            }

            // Remove the digit check to allow address searches without digits
            $where_clauses[] = "(OfficeAddress1 LIKE %s OR OfficeAddress2 LIKE %s OR OfficeCity LIKE %s OR OfficePostalCode LIKE %s)";
            $parameters[] = $like_query;
            $parameters[] = $like_query;
            $parameters[] = $like_query;
            $parameters[] = $like_query;
        }

        $where = '';
        if ( ! empty( $where_clauses ) ) {
            $where = ' WHERE ' . implode( ' OR ', $where_clauses );
        }

        $sql = "SELECT * FROM `$table_name_escaped` $where ORDER BY OfficeName ASC LIMIT %d OFFSET %d";

        $parameters[] = $limit;
        $parameters[] = $offset;

        // Prepare the SQL statement
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared with $wpdb->prepare()
        $prepared_sql = $wpdb->prepare( $sql, $parameters );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above with $wpdb->prepare()
        $results = $wpdb->get_results( $prepared_sql, ARRAY_A );

        return $results;
    }
}
