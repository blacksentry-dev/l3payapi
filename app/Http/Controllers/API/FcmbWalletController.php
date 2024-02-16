<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\FcmbApiService;
use App\Http\Controllers\API\BaseController as BaseController;

class FcmbWalletController extends BaseController
{
    protected $bankApiService;

    public function __construct(FcmbApiService $bankApiService)
    {
        $this->bankApiService = $bankApiService;
    }

    public function createAccount(Request $request)
    {
        // Validate request data if needed

        $requestData = $request->all();

        // Specify the endpoint for creating a base retail account
        $endpoint = '/api/Accounts/v2/CreateBaseRetailAccount';

        // Call the Bank API service to create an account
        $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);

        // Process the response from the bank's API
        // You may want to log the response or perform additional actions based on the result

        return response()->json($response);
    }

    // You can add more methods for other endpoints using the same structure
    
}
