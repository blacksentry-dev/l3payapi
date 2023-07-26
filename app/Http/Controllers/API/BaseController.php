<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Models\User;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    public function returnSuccess($result, $message, $code = 200){
        return response()->json([
            'status' => true,
            'message' => $message,
            'code' => $code,
            'data' => $result
        ], $code);
    }

    public function returnError($message, $error, $code = 401){
        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $error
        ], $code);
    }

    function isEmailExistsInDatabase($email)
    {
        return User::where('email', $email)->exists();
    }

    function isUsernameExistsInDatabase($username)
    {
        return User::where('username', $username)->exists();
    }

    function isPhoneExistsInDatabase($phone)
    {
        return User::where('phone_number', $phone)->exists();
    }
}