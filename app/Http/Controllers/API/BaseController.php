<?php


namespace App\Http\Controllers\API;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller as Controller;


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

    protected function sendPasswordResetEmail(string $email, string $firstName, string $lastName, string $otp): void
    {
        $message = "Hello $firstName $lastName,\n\n";
        $message .= "You have requested to reset you password. Please use the following OTP to reset your password:\n";
        $message .= "$otp\n\n";
        $message .= "If you didn't sign up for this service, please disregard this email.\n";

        Mail::raw($message, function ($emailMessage) use ($email) {
            $emailMessage->to($email)
                ->subject('Password Reset OTP');
        });
    }

    protected function PasswordResetSuccessMail(string $email, string $firstName, string $lastName): void
    {
        $message = "Hello $firstName $lastName,\n\n";
        $message .= "You have successfully reset your password\n";

        Mail::raw($message, function ($emailMessage) use ($email) {
            $emailMessage->to($email)
                ->subject('Password Reset Successfully');
        });
    }

    protected function SupportTicketMail(string $email, string $firstName, string $lastName, string $address, string $issue, string $description): void
    {
        $message = "Hello Support, our FTTH customer,\n\n";
        $message .= "$firstName $lastName at $address has raised an issue on $issue.\n";
        $message .= "Description: $description\n\n";
        $message .= "Please address issues accordingly.\n";

        Mail::raw($message, function ($emailMessage) use ($email, $firstName, $lastName) {
            $emailMessage->to('blacksentry-dev@layer3.com.ng') // Support team's email address
                ->from($email, "$firstName $lastName")
                ->subject('New FTTH Ticket');
        });
    }

    protected function feedbackAndRatingMail(string $email, string $firstName, string $lastName, string $feedback, int $rating): void
    {
        $message = "Howdy Blacksentry team, our LayerPay customer,\n\n";
        $message .= "$firstName $lastName has rated the application $rating star with the following\n";
        $message .= "Feedback: $feedback.\n\n";

        Mail::raw($message, function ($emailMessage) use ($email, $firstName, $lastName) {
            $emailMessage->to('blacksentry-dev@layer3.com.ng') // Blacksentry team's email address
                ->from($email, "$firstName $lastName")
                ->subject('App rating and feedback');
        });
    }
}