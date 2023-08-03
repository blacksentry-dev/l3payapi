<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class OnlineRenewSubscription extends Controller
{
    public function onlineRenewPackage(): JsonResponse
    {
        $url = 'http://selfserviceportal.layer3.ng:10080/24online/service/UserService/renewUser';

        // Set the API request parameters as a JSON object
        $data = [
            'username' => 'john_layer3',
            'invstatus' => '',
            'authUsername' => 'administrator',
            'sourceIPAddress' => '',
        ];

        try {
            // Make the API request using Laravel's HTTP client
            $response = Http::post($url, $data);

            // Check if the request was successful
            if ($response->successful()) {
                $responseData = $response->json(); // Get the response data as an array
                return response()->json($responseData);
            } else {
                // If the request was not successful, handle the error
                return response()->json(['error' => 'API request failed'], $response->status());
            }
        } catch (\Exception $e) {
            // Handle any exceptions that occurred during the API request
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
