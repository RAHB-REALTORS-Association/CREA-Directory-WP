<?php
namespace CreaAPI;

defined('ABSPATH') || exit;

class Token_Manager {
    private $client_id;
    private $client_secret;

    public function __construct($client_id, $client_secret) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function refreshTokenIfNeeded() {
        $access_token = get_transient('crea_access_token');
        $expires_at = get_transient('crea_access_token_expires_at');

        // If token is valid, return it
        if ($access_token && time() < $expires_at) {
            return $access_token;
        }

        // Otherwise, refresh the token
        return $this->fetchAccessToken();
    }

    private function fetchAccessToken() {
        $api_endpoint = "https://identity.crea.ca/connect/token";
        $args = [
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope'         => 'BoardDataApi.read',
            ],
        ];

        $response = wp_remote_post($api_endpoint, $args);

        if (is_wp_error($response)) {
            error_log('WP_Error encountered while refreshing access token: ' . $response->get_error_message());
            return false;
        } else if (wp_remote_retrieve_response_code($response) != 200) {
            error_log('Unexpected status code while refreshing access token: ' . wp_remote_retrieve_response_code($response));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['access_token'])) {
            $expires_at = time() + $data['expires_in'];
            set_transient('crea_access_token', $data['access_token'], $data['expires_in']);
            set_transient('crea_access_token_expires_at', $expires_at, $data['expires_in']);
            return $data['access_token'];
        } else {
            error_log('Failed to obtain access token during refresh');
            return false;
        }
    }
}
