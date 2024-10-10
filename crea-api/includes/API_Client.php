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

    /**
     * Fetch all offices from the API.
     *
     * @return array|\WP_Error
     */
    public function fetch_all_offices() {
        $all_offices = [];
        $skip = 0;
        $top = 1000; // Adjust according to the API limits
        $has_more_data = true;

        while ($has_more_data) {
            $response = $this->fetch_offices([
                'skip' => $skip,
                'top'  => $top,
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            // Extract offices and pagination info
            $offices = $response['data'];
            $pagination = $response['pagination'];

            foreach ($offices as $office) {
                // Ensure 'OfficeType' key exists
                if (isset($office['OfficeType']) && $office['OfficeType'] === 'Firm') {
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
                } else {
                    // Handle missing or different 'OfficeType'
                    error_log('Skipping office without OfficeType "Firm": ' . print_r($office, true));
                }
            }

            // Update the skip value based on the number of records retrieved
            $skip += count($offices);

            // Check if there is more data to fetch
            if (count($offices) < $top) {
                $has_more_data = false;
            }
        }

        return $all_offices;
    }

    /**
     * Fetch updated offices from the API since a given timestamp.
     *
     * @param string $since
     * @return array|\WP_Error
     */
    public function fetch_updated_offices($since) {
        $updated_offices = [];
        $skip = 0;
        $top = 1000;
        $has_more_data = true;

        while ($has_more_data) {
            $response = $this->fetch_offices([
                'skip'                 => $skip,
                'top'                  => $top,
                'modificationTimestamp' => $since,
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $offices = $response['data'];
            $pagination = $response['pagination'];

            foreach ($offices as $office) {
                if (isset($office['OfficeType']) && $office['OfficeType'] === 'Firm') {
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
                } else {
                    error_log('Skipping office without OfficeType "Firm": ' . print_r($office, true));
                }
            }

            // Update skip value
            $skip += count($offices);

            // Check if there is more data
            if (count($offices) < $top) {
                $has_more_data = false;
            }
        }

        return $updated_offices;
    }

    /**
     * Fetch inactive offices from the API since a given timestamp.
     *
     * @param string $since
     * @return array|\WP_Error
     */
    public function fetch_inactive_offices($since) {
        $inactive_offices = [];
        $skip = 0;
        $top = 1000;
        $has_more_data = true;

        while ($has_more_data) {
            $response = $this->fetch_offices([
                'skip'                 => $skip,
                'top'                  => $top,
                'modificationTimestamp' => $since,
                'officeStatus'          => 'Inactive', // Override the default 'Active' status
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $offices = $response['data'];
            $pagination = $response['pagination'];

            foreach ($offices as $office) {
                $inactive_offices[] = $office['OfficeNationalAssociationId'];
            }

            // Update skip value
            $skip += count($offices);

            // Check if there is more data
            if (count($offices) < $top) {
                $has_more_data = false;
            }
        }

        return $inactive_offices;
    }

    /**
     * Fetch offices from the API with optional parameters.
     *
     * @param array $args
     * @return array|\WP_Error
     */
    private function fetch_offices($args = []) {
        $access_token = $this->token_manager->refreshTokenIfNeeded();
        if (!$access_token) {
            return new \WP_Error('token_error', 'Unable to obtain access token.');
        }

        // Build headers with the access token
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
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

        $url = 'https://boardapi.realtor.ca/Office'; // Adjust to the correct endpoint

        if ($query_string) {
            $url .= '?' . $query_string;
        }

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            error_log('WP_Error during API request: ' . $response->get_error_message());
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (wp_remote_retrieve_response_code($response) == 200) {
            if (isset($data['data'])) {
                return $data;
            } else {
                error_log('Unexpected API response structure: ' . print_r($data, true));
                return new \WP_Error('api_error', 'Unexpected API response structure.', ['response' => $data]);
            }
        } else {
            error_log('API Error: ' . wp_remote_retrieve_response_code($response) . ' - ' . print_r($data, true));
            return new \WP_Error('api_error', 'Failed to fetch data from the API.', ['response' => $data]);
        }
    }
}
