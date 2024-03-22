<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FcmbApiService;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\API\BaseController as BaseController;

class FcmbWalletController extends BaseController
{
    protected $bankApiService;

    public function __construct(FcmbApiService $bankApiService)
    {
        $this->bankApiService = $bankApiService;
    }
    protected function generateToken()
    {
        // Your logic to generate a unique token
        return md5(uniqid());
    }

    public function createUserBankWalletAccount(Request $request)
    {
        try {
            // Validate request data if needed

            $requestData = $request->all();

            // Specify the endpoint for creating a base retail account
            $endpoint = '/OpenAccount-clone/api/Accounts/v2/CreateBaseRetailAccount';

            // Call the Bank API service to create an account
            $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);

            // Process the response from the bank's API
            // You may want to log the response or perform additional actions based on the result

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
        
    }

    // You can add more methods for other endpoints using the same structure
    // public function createUserWallet(Request $request)
    // {
    //     $url = 'https://devapi.fcmb.com/OpenAccount-clone/api/Accounts/v1/CreateBaseRetailAccount';

    //     $data = $request->all();

    //     $xToken = $this->generateToken();
    //     $utcTimestamp = now()->timestamp;

    //     try {
    //         // Make the API request using Laravel's HTTP client and add the username and password in the header
    //         $response = Http::withHeaders([
    //             'client_id' => 250,
    //             'x-token' => $xToken,
    //             'UTCTimestamp' => $utcTimestamp,
    //             'Content-Type' => 'application/json',
    //             'Cache-Control' => 'no-cache',
    //             'Ocp-Apim-Subscription-Key' => "50f3a5f76b0045a082ed035e05bc9686",
    //         ])->post($url, $data);
    //         $responseData = $response->json();
    //         // Check if the request was successful
    //         if ($responseData["code"] == "00") {
    //             $success['paymentHistory'] =  $responseData["data"]["description"];
    //             return $this->returnSuccess($success, 'Retrieved successfully.', 200);  
    //         } else {
    //             return $this->returnError('Error', $responseData["responsemsg"]);
    //         }
    //     } catch (\Exception $e) {
    //         return $this->returnError('Error', $e->getMessage(), 500);
    //     }
    // }

    // public function getStatesssss(Request $request){
    //     $url = 'https://devportal.fcmb.com/utilityinquiry/api/BranchCityAndState/GetAllStates';

    //     // $data = $request->all();

    //     $xToken = $this->generateToken();
    //     $utcTimestamp = now()->timestamp;

    //     try {
    //         // Make the API request using Laravel's HTTP client and add the username and password in the header
    //         $response = Http::withHeaders([
    //             'client_id' => 250,
    //             'x-token' => $xToken,
    //             'UTCTimestamp' => $utcTimestamp,
    //             'Content-Type' => 'application/json',
    //             'Cache-Control' => 'no-cache',
    //             'Ocp-Apim-Subscription-Key' => "50f3a5f76b0045a082ed035e05bc9686",
    //         ])->get($url);

    //         if ($response->successful()) {
    //             // Process the response
    //             $responseData = $response->json();
    
    //             // Check if the response data exists and has the 'code' field
    //             if (isset($responseData['code']) && $responseData['code'] == '00') {
    //                 // Access the states data and return success response
    //                 $allStates = $responseData['data'];
    //                 return $this->returnSuccess(['allStates' => $allStates], 'Retrieved successfully.', 200);
    //             } else {
    //                 // Return error if the 'code' field is not '00'
    //                 return $this->returnError('Error', $responseData['description']);
    //             }
    //         } else {
    //             // Return error if the request was not successful
    //             return $this->returnError('Error', 'Failed to retrieve data.', $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         // Return error for any exceptions
    //         return $this->returnError('Error', $e->getMessage(), 500);
    //     }
    // }

    public function getStates(Request $request){
        try {
            // Validate request data if needed

            $requestData = $request->all();

            // Specify the endpoint for creating a base retail account
            $endpoint = '/utilityinquiry/api/BranchCityAndState/GetAllStates';

            // Call the Bank API service to create an account
            $response = $this->bankApiService->sendRequest($endpoint, 'get', $requestData);

            // Process the response from the bank's API
            // You may want to log the response or perform additional actions based on the result

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
        
    }

    public function getCities(Request $request){
        try {
            // Validate request data if needed

            $requestData = $request->all();

            // Specify the endpoint for creating a base retail account
            $endpoint = '/utilityinquiry/api/BranchCityAndState/GetAllCities';

            // Call the Bank API service to create an account
            $response = $this->bankApiService->sendRequest($endpoint, 'get', $requestData);

            // Process the response from the bank's API
            // You may want to log the response or perform additional actions based on the result
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
            
    }

    public function generateConsentOPT(Request $request){
        try {
            // Validate request data if needed
            $requestData = $request->all();
    
            // Specify the endpoint for generating consent OTP
            $endpoint = '/Consent/api/OTP/generateConsentOTP';
    
            // Call the Bank API service to generate consent OTP
            $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);
    
            // Process the response from the bank's API
            // You may want to log the response or perform additional actions based on the result
    
            return response()->json($response);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getConsentId(Request $request){
        try {
            // Validate request data if needed
            $requestData = $request->all();
    
            // Specify the endpoint for giving consent
            $endpoint = '/Consent/api/CustomerConsent/v1/GiveConsent';
    
            // Call the Bank API service to give consent
            $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);
    
            // Process the response from the bank's API
            if ($response['code'] === '00') {
                // Consent added successfully
                $consentId = $response['data'];
    
                // Assuming you have a User model and a column named 'consent_id' in your users table
                // You can save the consent ID to the user's table here
                $user = Auth::user(); // Assuming you're using Laravel's authentication
                $user->consent_id = $consentId;
                $user->save();
    
                return response()->json([
                    'success' => true,
                    'message' => 'Consent added successfully',
                    'consent_id' => $consentId,
                ]);
            } else {
                // Handle error response from the bank's API
                return response()->json([
                    'success' => false,
                    'message' => $response['description'],
                ], 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateConsentId(Request $request){
        try {
            // Validate request data if needed
            $requestData = $request->all();

            // Specify the endpoint for updating customer consent
            $endpoint = '/Consent/api/CustomerConsent/v1/updatecustomerconsent';

            // Call the Bank API service to update customer consent
            $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);

            // Process the response from the bank's API
            if ($response['code'] === '00') {
                // Consent updated successfully
                $custConsentGuid = $response['data']['custConsent_Guid'];

                // Find the user by consent ID and update the consent ID in the database
                $user = User::where('consent_id', $custConsentGuid)->first();
                if ($user) {
                    // Update the user's consent ID
                    $user->consent_id = $custConsentGuid;
                    $user->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Consent ID updated successfully',
                    'consent_id' => $custConsentGuid,
                ]);
            } else {
                // Handle error response from the bank's API
                return response()->json([
                    'success' => false,
                    'message' => $response['description'],
                ], 400);
            }
        } catch (\Throwable $th) {
            // Handle exceptions
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getAccountBalance(Request $request){
        try {
            // Validate request data if needed

            $requestData = $request->all();

            // Specify the endpoint for creating a base retail account
            $endpoint = 'acctInquiry/api/AccountInquiry/acctBalanceByOtpOrConsentId';

            // Call the Bank API service to create an account
            $response = $this->bankApiService->sendRequest($endpoint, 'get', $requestData);

            // Process the response from the bank's API
            // You may want to log the response or perform additional actions based on the result
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    
    public function intraBankTransfer(Request $request){
        try {
            // Validate request data if needed
            $requestData = $request->all();

            // Specify the endpoint for updating customer consent
            $endpoint = 'intrabanktransferopenapi/api/IntrabankTransfer/c2bTransferWithConsent';

            // Call the Bank API service to update customer consent
            $response = $this->bankApiService->sendRequest($endpoint, 'post', $requestData);

            // Process the response from the bank's API
            if ($response['code'] === '00') {
                // Consent updated successfully
                $transactionDetails = $response['data'];

                // Find the user by consent ID and update the consent ID in the database
                $user = User::where('consent_id', $custConsentGuid)->first();
                if ($user) {
                    // Update the user's consent ID
                    $user->consent_id = $custConsentGuid;
                    $user->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Consent ID updated successfully',
                    'consent_id' => $custConsentGuid,
                ]);
            } else {
                // Handle error response from the bank's API
                return response()->json([
                    'success' => false,
                    'message' => $response['description'],
                ], 400);
            }
        } catch (\Throwable $th) {
            // Handle exceptions
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
