<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\API\BaseController as BaseController;


class OnlineRenewSubscription extends BaseController
{
    /**
     * @OA\Post(
     * path="/api/24online/renew-package",
     * operationId="User Renew Subscription",
     * tags={"Subscription"},
     * summary="User Renew Subscriptionr",
     * description="User Renew Subscription here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Register Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Register Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function RenewSubscription(Request $request)
    {
        $url = 'http://selfserviceportal.layer3.ng:10080/24online/service/UserService/renewUser';

        // Set the API request parameters as a JSON object
        $data = [
            'username' => $request->username,
            'invstatus' => '',
            'authUsername' => 'administrator',
            'sourceIPAddress' => '',
        ];

        try {
            // Make the API request using Laravel's HTTP client
            $response = Http::post($url, $data);

            // Check if the request was successful
            if ($response->successful()) {
                $responseData = $response->json();
                $success['message'] =  $responseData["responsemsg"];
                return $this->returnSuccess($success, 'User renewed successfully.', 200);
            } else {
                // If the request was not successful, handle the error
                return $this->returnError('Error', $response->status());
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);            
        }
    }
}
