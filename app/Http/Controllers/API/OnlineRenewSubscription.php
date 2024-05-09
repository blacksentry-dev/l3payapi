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
     * tags={"24Online (FTTH Subscription)"},
     * summary="User Renew Subscription",
     * description="User Renew Subscription here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={"username"},
     *               @OA\Property(property="username", type="string"),
     *         ),
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
        $url = 'https://102.164.36.86:10080/24online/service/UserService/renewUser';

        // Set the API request parameters as a JSON object
        $data = [
            'username' => $request->username,
            'invstatus' => '',
            'authUsername' => 'administrator',
            'sourceIPAddress' => '',
        ];

        try {
            // Make the API request using Laravel's HTTP client
            $response = Http::withoutVerifying()->post($url, $data);

            $responseData = $response->json();
            if ($responseData["responsecode"] == 1) {
                $success['message'] =  $responseData["responsemsg"];
                return $this->returnSuccess($success, 'User renewed successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);            
        }
    }

    public function handleAutoRenewal(Request $request)
    {
        try {
            // Check if auto-renewal is enabled in the frontend setting
            $autoRenewalEnabled = $request->input('auto_renewal_enabled', false);

            if (!$autoRenewalEnabled) {
                return $this->returnError('Auto-renewal disabled.', 'Auto-renewal is not enabled in the frontend setting.');
            }

            // Get the user's subscription details (you need to adjust this based on your application's structure)
            $userSubscription = Subscription::where('user_id', $request->user_id)->first();

            if (!$userSubscription) {
                return $this->returnError('Subscription not found.', 'User subscription not found.');
            }

            // Check if the subscription renewal date is reached
            if ($userSubscription->renewal_date <= now()) {
                // Call the auto-renewal logic (using the existing RenewSubscription API method)
                $response = $this->RenewSubscription($request);

                // Handle the response and return appropriate JSON response
                $responseData = $response->json();

                if ($responseData["responsecode"] == 1) {
                    // Update the renewal date for the subscription
                    $userSubscription->update(['renewal_date' => now()->addMonth()]);

                    // Return success message
                    return $this->returnSuccess('Subscription renewed successfully.', 200);
                } else {
                    // Return error message based on the renewal response
                    return $this->returnError('Error', $responseData["responsemsg"]);
                }
            } else {
                return $this->returnError('Renewal date not reached.', 'Subscription renewal date has not been reached yet.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions during the auto-renewal process
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    // /**
    //  * @OA\Post(
    //  * path="/api/24online/user-password",
    //  * operationId="Get User Password",
    //  * tags={"24Online (FTTH Subscription)"},
    //  * summary="Get User Password",
    //  * description="Get User Password here",
    //  *     @OA\RequestBody(
    //  *         @OA\JsonContent(
    //  *              required={"username"},
    //  *              @OA\Property(property="username", type="string", description="Username"),
    //  *         ),
    //  *    ),
    //  *      @OA\Response(
    //  *          response=201,
    //  *          description="Register Successfully",
    //  *          @OA\JsonContent()
    //  *       ),
    //  *      @OA\Response(
    //  *          response=200,
    //  *          description="Register Successfully",
    //  *          @OA\JsonContent()
    //  *       ),
    //  *      @OA\Response(
    //  *          response=422,
    //  *          description="Unprocessable Entity",
    //  *          @OA\JsonContent()
    //  *       ),
    //  *      @OA\Response(response=400, description="Bad request"),
    //  *      @OA\Response(response=404, description="Resource Not Found"),
    //  * )
    //  */
    public function getUserPassword(Request $request){
        $url = 'https://102.164.36.86:10080/24online/service/UserService/getUserPassword';

        // Set the API request parameters as a JSON object
        $data = [
            'username' => $request->username,
        ];

        try {
            // Make the API request using Laravel's HTTP client
            //The original format is -- Http::post
            $response = Http::withoutVerifying()->post($url, $data);
            $responseData = $response->json();
            if ($responseData["responsecode"] == 1) {
                $success['password'] =  $responseData["responsemsg"];
                return $this->returnSuccess($success, 'User Password retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/user-status",
     * operationId="Get User Status",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Status",
     * description="Get User Status here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *         ),
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
    public function userStatus(Request $request){
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/userInfo';

        try {
            // Make the API request using Laravel's HTTP client and add the username and password in the header
            //The original format is -- Http::withHeaders
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url);
            $responseData = $response->json();
            // Check if the request was successful
            if ($responseData["responsecode"] == 1) {
                $success["planName"] = $responseData["responsemsg"]["planName"];
                $success["phone"] = $responseData["responsemsg"]["phone"];
                $success["userid"] = $responseData["responsemsg"]["userid"];
                $success["createdate"] = $responseData["responsemsg"]["createdate"];
                $success["dateofbirth"] = $responseData["responsemsg"]["dateofbirth"];
                $success["usertype"] = $responseData["responsemsg"]["usertype"];
                $success["ipaddress"] = $responseData["responsemsg"]["ipaddress"];
                $success["expirydate"] = $responseData["responsemsg"]["expirydate"];
                $success["emailid"] = $responseData["responsemsg"]["emailid"];
                $success["planPrice"] = $responseData["responsemsg"]["planPrice"];
                $success["nextbilldate"] = $responseData["responsemsg"]["nextbilldate"];
                $success["username"] = $responseData["responsemsg"]["username"];
                $success["accountno"] = $responseData["responsemsg"]["accountno"];
                $success["name"] = $responseData["responsemsg"]["name"];
                $success["lastrenewaldate"] = $responseData["responsemsg"]["lastrenewaldate"];
                $success["renewalPrice"] = $responseData["responsemsg"]["renewalPrice"];
                return $this->returnSuccess($success, 'Retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/user-usage-info",
     * operationId="Get User Usage Information",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Usage Information",
     * description="Get User Usage Information here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *         ),
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
    public function getUserUsageInfo(Request $request){
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/usageInfo';


        try {
            // Make the API request using Laravel's HTTP client and add the username and password in the header
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url);

            $responseData = $response->json();
            if ($responseData["responsecode"] == 1) {
                $success["uploadused"] = $responseData["responsemsg"]["uploadused"];
                $success["downloadused"] = $responseData["responsemsg"]["downloadused"];
                $success["totalused"] = $responseData["responsemsg"]["totalused"];
                $success["usedminutesaccountwise"] = $responseData["responsemsg"]["usedminutesaccountwise"];
                return $this->returnSuccess($success, 'Retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/payment-status",
     * operationId="Get User Payment Status",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Payment Status",
     * description="Get User Payment Status here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *         ),
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
    public function getPaymentStatus(Request $request){
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/getPaymentStatus';

        // Set the API request parameters as a JSON object
        $data = [
            'status' => true,
        ];

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url, $data);

            $responseData = $response->json();
            //return $responseData;
            if ($responseData["responsecode"] == 1) {
                $success['paymentHistory'] =  $responseData["responsemsg"]["actionHistoryList"];
                return $this->returnSuccess($success, 'User payment status retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/renewal-history",
     * operationId="Get User Renewal History",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Renewal History",
     * description="Get User Renewal History here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password", "month", "year", "beginindex", "count"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *              @OA\Property(property="month", type="string", description="Month to start"),
     *              @OA\Property(property="year", type="string", description="Year"),
     *              @OA\Property(property="beginindex", type="integer", description="The beginning index"),
     *              @OA\Property(property="count", type="integer", description="The number of items to retrieve"),
     *      ),
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
    public function RenewalHistory(Request $request)
    {
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/renewalHistory';

        $data = [
            'month' => $request->month,
            'year' => $request->year,
            'beginindex' => $request->beginindex,
            'count' => $request->count,
        ];
        try {
            // Make the API request using Laravel's HTTP client and add the username and password in the header
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url, $data);
            $responseData = $response->json();
            // Check if the request was successful
            if ($responseData["responsecode"] == 1) {
                $success = [];
                foreach ($responseData["responsemsg"] as $responseItem) {
                    $responseObject = [
                        "packagename" => $responseItem["packagename"],
                        "renewdate" => $responseItem["renewdate"],
                        "allotteduploaddata" => $responseItem["allotteduploaddata"],
                        "allotteddownloaddata" => $responseItem["allotteddownloaddata"],
                        "allottedtime" => $responseItem["allottedtime"],
                        "allottedtotaldata" => $responseItem["allottedtotaldata"],
                        "expirydate" => $responseItem["expirydate"],
                    ];
                    $success[] = $responseObject;
                }
                return $this->returnSuccess($success, 'Retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/invoice-detail",
     * operationId="Get User Invoice Details",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Invoice Details",
     * description="Get User Invoice Details here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password", "beginindex", "count"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *              @OA\Property(property="beginindex", type="integer", description="The beginning index"),
     *              @OA\Property(property="count", type="integer", description="The number of items to retrieve"),
     *          ),
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
    public function getInvoiceDetail(Request $request)
    {
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/invoiceDetail';

        $data = [
            'beginindex' => $request->beginindex,
            'count' => $request->count,
        ];

        try {
            // Make the API request using Laravel's HTTP client and add the username and password in the header
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url, $data);
            $responseData = $response->json();
            // Check if the request was successful
            if ($responseData["responsecode"] == 1) {
                $success = [];
                foreach ($responseData["responsemsg"] as $responseItem) {
                    $responseObject = [
                        "fullCustomerName" => $responseItem["fullCustomerName"],
                        "invoiceNo" => $responseItem["invoiceNo"],
                        "address1" => $responseItem["address1"],
                        "invoiceDate" => $responseItem["expiryDate"],
                        "basicInvoiceAmount" => $responseItem["basicInvoiceAmount"],
                        "grandTotal" => $responseItem["grandTotal"],
                    ];
                    $success[] = $responseObject;
                }
                return $this->returnSuccess($success, 'Retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/24online/session-usage-detail",
     * operationId="Get User Usage Details",
     * tags={"24Online (FTTH Subscription)"},
     * summary="Get User Usage Details",
     * description="Get User Invoice Details here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={"username", "password", "fromdate", "todate"},
     *              @OA\Property(property="username", type="string", description="Username"),
     *              @OA\Property(property="password", type="string", description="Password"),
     *              @OA\Property(property="fromdate", type="date", description="2000-01-01"),
     *              @OA\Property(property="todate", type="date", description="2000-01-01"),
     *          ),
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
    public function sessionUsageDetails(Request $request)
    {
        $url = 'https://102.164.36.86:10080/24online/service/MyAccountService/sessionUsageDetails';

        $data = [
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
        ];
        try {
            // Make the API request using Laravel's HTTP client and add the username and password in the header
            $response = Http::withoutVerifying()->withHeaders([
                'username' => $request->username,
                'password' => $request->password,
            ])->post($url, $data);
            $responseData = $response->json();

            // Check if the request was successful
            if ($responseData["responsecode"] == 1) {      
                $success['sessionUsageDetails'] = json_decode($responseData["responsemsg"]["result"]);
                return $this->returnSuccess($success, 'User Session Details retrieved successfully.', 200);
            } else {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/24online/user-account-status",
     *     operationId="Get User Account Status",
     *     tags={"24Online (FTTH Subscription)"},
     *     summary="Get User Account Status",
     *     description="Get User Account Status here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"username", "accountNumber"},
     *             @OA\Property(property="username", type="string", description="The username of the user."),
     *             @OA\Property(property="accountNumber", type="string", description="The account number of the user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Account Status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="responsemsg", type="string", description="Response message."),
     *             @OA\Property(property="accountStatus", type="string", description="The user's account status (Active/Expired).")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function getUserAccountStatus(Request $request){
        $url = 'https://102.164.36.86:10080/24online/service/UserService/getUserStatus';

        // Set the API request parameters as a JSON object
        $data = [
            'username' => $request->username,
            'accountNumber' => $request->accountNumber,
        ];

        try {
            // Make the API request using Laravel's HTTP client
            //The original format is -- Http::post
            $response = Http::withoutVerifying()->post($url, $data);
            $responseData = $response->json();
            if ($responseData["responsecode"] == 2) {
                $success['responsemsg'] =  $responseData["responsemsg"];
                $success['accountStatus'] =  "Expired";
                return $this->returnSuccess($success, 'User Account Status retrieved successfully.', 200);
            } elseif ($responseData["responsecode"] == 3) {
                $success['responsemsg'] =  $responseData["responsemsg"];
                $success['accountStatus'] =  "Active";
                return $this->returnSuccess($success, 'User Account Status retrieved successfully.', 200);
            } {
                return $this->returnError('Error', $responseData["responsemsg"]);
            }
        } catch (\Exception $e) {
            return $this->returnError('Error', $e->getMessage(), 500);
        }
    }
}

