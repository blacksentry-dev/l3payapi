<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Class FcmbApiService.
 */
class FcmbApiService
{
    protected $baseUrl;
    protected $clientId;
    protected $subscriptionKey;

    public function __construct()
    {
        // Set the base URL, client ID, and subscription key for the bank's API
        $this->baseUrl = config('services.bank_api.base_url');
        $this->clientId = config('services.bank_api.client_id');
        $this->subscriptionKey = config('services.bank_api.subscription_key');
    }

    public function sendRequest($endpoint, $method, $data)
    {
        // Generate x-token and UTCTimestamp (You may need to adjust the logic based on the bank's requirements)
        $xToken = $this->generateToken();
        $utcTimestamp = now()->timestamp;

        // Merge headers with generated x-token and UTCTimestamp
        $headers = [
            'client_id' => $this->clientId,
            'x-token' => $xToken,
            'UTCTimestamp' => $utcTimestamp,
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Ocp-Apim-Subscription-Key' => "50f3a5f76b0045a082ed035e05bc9686",
        ];

        // Make the API request to the specified endpoint
        $response = Http::{$method}($this->baseUrl . $endpoint, [
            'headers' => $headers,
            'json' => $data,
        ]);

        // Return the response from the bank's API
        return $response->json();
    }

    // Add logic to generate a unique x-token
    protected function generateToken()
    {
        // Your logic to generate a unique token
        return md5(uniqid());
    }

}
