<?php
namespace CreaAPI;

use CreaAPI\Token_Manager;

defined('ABSPATH') || exit;

class API_Client {
    private $client_id;
    private $client_secret;
    private $token_manager;
    private $office_aor;

    public function __construct() {
        $this->client_id     = get_option('crea_api_client_id');
        $this->client_secret = get_option('crea_api_client_secret');
        $this->token_manager = new Token_Manager($this->client_id, $this->client_secret);
        $this->office_aor    = get_option('crea_api_office_aor', '');
    }

    public function fetch_all_offices() {
        $all_offices = [];
        $skip = 0;
        $top = 1000; // Adjust according to the API limits

        do {
            $response = $this->fetch_offices([
                'skip' => $skip,
                'top'  => $top,
                // 'officeStatus' => 'Active', // Not needed since default is 'Active'
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $offices = $response;

            foreach ($offices as $office) {
                // Only interested in OfficeType 'Firm'
                if ($office['OfficeType'] === 'Firm') {
                    // Extract the website URL
                    $website_url = '';
                    if (!empty($office['OfficeSocialMedia'])) {
                        foreach ($office['OfficeSocialMedia'] as $social_media) {
                            if ($social_media['SocialMediaType'] === 'Website') {
                                $website_url = $social_media['SocialMediaUrlOrId'];
                                break; // Stop after finding the website URL
                            }
                        }
                    }

                    // Add the website URL to the office data
                    $office['SocialMediaWebsiteUrlOrId'] = $website_url;

                    // Use 'OfficeNationalAssociationId' as the key
                    $all_offices[$office['OfficeNationalAssociationId']] = $office;
                }
            }

            $skip += $top;
        } while (count($offices) === $top);

        return $all_offices;
    }

    public function fetch_updated_offices($since) {
        $updated_offices = [];
        $skip = 0;
        $top = 1000; // Adjust according to the API limits

        do {
            $response = $this->fetch_offices([
                'skip'                 => $skip,
                'top'                  => $top,
                'modificationTimestamp' => $since,
                // 'officeStatus' => 'Active', // Not needed since default is 'Active'
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $offices = $response;

            foreach ($offices as $office) {
                if ($office['OfficeType'] === 'Firm') {
                    // Extract the website URL
                    $website_url = '';
                    if (!empty($office['OfficeSocialMedia'])) {
                        foreach ($office['OfficeSocialMedia'] as $social_media) {
                            if ($social_media['SocialMediaType'] === 'Website') {
                                $website_url = $social_media['SocialMediaUrlOrId'];
                                break;
                            }
                        }
                    }

                    // Add the website URL to the office data
                    $office['SocialMediaWebsiteUrlOrId'] = $website_url;

                    $updated_offices[$office['OfficeNationalAssociationId']] = $office;
                }
            }

            $skip += $top;
        } while (count($offices) === $top);

        return $updated_offices;
    }

    public function fetch_inactive_offices($since) {
        $inactive_offices = [];
        $skip = 0;
        $top = 1000; // Adjust according to the API limits

        do {
            $response = $this->fetch_offices([
                'skip'                 => $skip,
                'top'                  => $top,
                'modificationTimestamp' => $since,
                'officeStatus'          => 'Inactive', // Override the default 'Active' status
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $offices = $response;

            foreach ($offices as $office) {
                $inactive_offices[] = $office['OfficeNationalAssociationId'];
            }

            $skip += $top;
        } while (count($offices) === $top);

        return $inactive_offices;
    }

    private function fetch_offices($args = []) {
        $access_token = $this->token_manager->refreshTokenIfNeeded();
        if (!$access_token) {
            return new \WP_Error('token_error', 'Unable to obtain access token.');
        }

        // Build headers with the access token
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ];

        // Build the query parameters
        $query_params = [];

        if (isset($args['skip'])) {
            $query_params['skip'] = $args['skip'];
        }

        if (isset($args['top'])) {
            $query_params['top'] = $args['top'];
        }

        if (isset($args['modificationTimestamp'])) {
            $query_params['modificationTimestamp'] = $args['modificationTimestamp'];
        }

        // Include OfficeAOR if it's set
        if (!empty($this->office_aor)) {
            $query_params['officeAOR'] = $this->office_aor;
        }

        // Include OfficeStatus, defaulting to 'Active'
        $query_params['officeStatus'] = isset($args['officeStatus']) ? $args['officeStatus'] : 'Active';

        // Build the query string
        $query_string = http_build_query($query_params);

        $url = 'https://api.crea.ca/office'; // Adjust to the correct endpoint

        if ($query_string) {
            $url .= '?' . $query_string;
        }

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (wp_remote_retrieve_response_code($response) == 200) {
            return $data;
        } else {
            return new \WP_Error('api_error', 'Failed to fetch data from the API.', ['response' => $response]);
        }
    }
}
