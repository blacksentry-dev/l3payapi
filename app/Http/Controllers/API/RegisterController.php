<?php
   
namespace App\Http\Controllers\API;
   
use Validator;
use App\Models\Otp;
use App\Models\User;
use App\ValidationRules;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\PasswordResetMail;
use Illuminate\Http\JsonResponse;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Wallet;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;

class RegisterController extends BaseController
{

    /**
     * @OA\Post(
     * path="/api/register",
     * operationId="Register",
     * tags={"Register"},
     * summary="User Register",
     * description="User Register here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *               type="object",
     *               required={"first_name","last_name","email", "username", "password", "password_confirmation"},
     *               @OA\Property(property="first_name", type="string"),
     *               @OA\Property(property="last_name", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="username", type="string"),
     *               @OA\Property(property="password", type="string"),
     *               @OA\Property(property="password_confirmation", type="string")
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
    public function register(Request $request): JsonResponse
    {

        try {
            if ($request->has('first_name') && empty($request->input('first_name'))) {
                return $this->returnError('Validation Error', 'Firstname field can not be empty');
            }

            if ($request->has('last_name') && empty($request->input('last_name'))) {
                return $this->returnError('Validation Error', 'Lastname field can not be empty');
            }

            if ($this->isUsernameExistsInDatabase($request->input('username'))) {
                return $this->returnError('Validation Error', 'Username already taken');
            }

            if ($request->has('username') && empty($request->input('username'))) {
                return $this->returnError('Validation Error', 'Username field can not be empty');
            }

            if ($request->has('email') && empty($request->input('email'))) {
                return $this->returnError('Validation Error', 'Email field can not be empty');
            }

            // if ($this->isUsernameExistsInDatabase($request->input('username'))) {
            //     return $this->returnError('Validation Error', 'Username already in use');
            // }

            // if ($this->isPhoneExistsInDatabase($request->input('phone_number'))) {
            //     return $this->returnError('Validation Error', 'Phone Number already in use');
            // }

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $request->input('password'))) {
                return $this->returnError('Validation Error', 'Password must contain at least one uppercase letter, one digit, and be at least 8 characters long.', 400);
            }

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);

            if (!$user) {
                return $this->sendError('Something went wrong, please try again.', $user->errors());
            }

            $walletBallance = 0.00;

            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->first_name;
            $success['user_id'] =  $user->id;
            $success['username'] =  $user->username;
            $success['email'] =  $user->email;
            $success['wallet_balance'] =  $walletBallance;

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;
            $userName = $user->username;

            $otp = $this->generateOTP();
            Otp::create([
                'otp' => $otp,
                'expiration' => now()->addMinutes(15),
                'user_id' => $user->id,
            ]);
            $this->sendEmailOTP($email, $firstName, $lastName, $otp);
           
            return $this->returnSuccess($success, 'User signed up successfully.');
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

    
    // Generate Otp
    private function generateOTP(): string
    {
        // Generate a random 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // You can customize the OTP generation logic as per your requirements

        return $otp;
    }

    private function sendEmailOTP(string $email, string $firstName, string $lastName, string $otp)
    {
        // Construct the email message
        $message = "Hello $firstName $lastName,\n\n";
        $message .= "Thank you for registering with our service. Please use the following OTP to verify your email address:\n";
        $message .= "$otp\n\n";
        $message .= "If you didn't sign up for this service, please disregard this email.\n";

        // Send the email
        Mail::raw($message, function ($emailMessage) use ($email) {
            $emailMessage->to($email)
                ->subject('Email Verification OTP');
        });
    }


    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="authLogin",
     * tags={"Login"},
     * summary="User Login",
     * description="Login User Here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={"username", "password"},
     *               @OA\Property(property="username", type="string"),
     *               @OA\Property(property="password", type="string")
     *          ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Login Successfully",
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
    public function login(Request $request): JsonResponse
    {
        
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $walletBalance = Wallet::where('user_id', $user->id)->first();

            if (!$walletBalance) {
                $walletBalance = 0.00;
            } else {
                $walletBalance = $walletBalance->amount;
            }

            $expirationTime = Carbon::now()->addHour()->timestamp;

            if(!empty($user->email_verified_at)){
                $success['token'] =  $user->createToken('MyApp')->accessToken;
                $success['token_expires_at'] = $expirationTime;
                $success['user'] =  $user;
                $success['wallet_balance'] = $walletBalance;
                return $this->returnSuccess($success, 'User signed up successfully.');
            }
            return $this->returnError('Error', "You have not verified your email", 410);
        }else{ 
            return $this->returnError('Error', 'Invalid username or password', 401);
        } 
    }


    public function logout()
    {
        $user = Auth::user();
        $user->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * @OA\Put(
     *     path="/api/users/profile-update",
     *     operationId="UpdateProfile",
     *     tags={"Profile"},
     *     summary="Update User Profile",
     *     description="Update user profile information",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={""},
     *               @OA\Property(property="first_name", type="string"),
     *               @OA\Property(property="last_name", type="string"),
     *               @OA\Property(property="username", type="string"),
     *               @OA\Property(property="phone_number", type="integer"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="address", type="string")
     *         ),
     *    ),
     *    @OA\Response(
     *        response=201,
     *        description="Profile updated successfully",
     *        @OA\JsonContent()
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @OA\JsonContent(),
     *    ),
     *    @OA\Response(response=400, description="Bad request"),
     *    @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {

        try {
            $user = User::where('id', $request->user_id)->first();

            if (!$user) {
                return $this->returnError('User not found.', 404);
            }

            if ($request->has('first_name') && empty($request->input('first_name'))) {
                return $this->returnError('Validation Error', 'First name field can not be empty');
            }

            if ($request->has('last_name') && empty($request->input('last_name'))) {
                return $this->returnError('Validation Error', 'Last name field can not be empty');
            }

            if ($request->has('username') && empty($request->input('username'))) {
                return $this->returnError('Validation Error', 'Username field can not be empty');
            }

            if ($request->has('phone_number') && empty($request->input('phone_number'))) {
                return $this->returnError('Validation Error', 'Phone number field can not be empty');
            }

            if ($request->has('email') && empty($request->input('email'))) {
                return $this->returnError('Validation Error', 'Email field can not be empty');
            }

            if ($request->has('address') && empty($request->input('address'))) {
                return $this->returnError('Validation Error', 'Address field can not be empty');
            }

            if ($request->has('dob') && empty($request->input('dob'))) {
                return $this->returnError('Validation Error', 'Date of birth field can not be empty');
            }

            if ($request->has('sex') && empty($request->input('sex'))) {
                return $this->returnError('Validation Error', 'Sex field can not be empty');
            }

            $user->first_name = $request->input('first_name', $user->first_name);
            $user->last_name = $request->input('last_name', $user->last_name);
            $user->username = $request->input('username', $user->username);
            $user->phone_number = $request->input('phone_number', $user->phone_number);
            $user->email = $request->input('email', $user->email);
            $user->address = $request->input('address', $user->address);
            $user->dob = Carbon::parse($request->input('dob', $user->dob));
            $user->sex = $request->input('sex', $user->sex);


            $user->save();

            $success = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->address,
                'dob' => $user->dob,
                'sex' => $user->sex,
            ];

           
            return $this->returnSuccess($success, 'Profile updated successfully.');
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }    
    }

    private function getUserFromToken(string $token)
    {
        $payload = Auth::guard('api')->payload();

        if (!$payload || !$payload->get('sub')) {
            return null;
        }

        return User::find($payload->get('sub'));
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }
   

    /**
     * @OA\Post(
     *     path="/api/users/verify-email",
     *     operationId="verifyEmail",
     *     tags={"Email Verification"},
     *     summary="Verify Email",
     *     description="Verify user's email using the OTP.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={"otp"},
     *               @OA\Property(property="otp", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification successful.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid OTP",
     *         @OA\JsonContent(),
     *     ),
     * )
     */
    
    public function verifyEmail(Request $request): JsonResponse
    {
        try {

            if ($request->has('otp') && empty($request->input('otp'))) {
                return $this->returnError('Validation Error', 'Otp field can not be empty', 401);
            }
            
            $otpModel = Otp::findByOtp($request->input('otp'));

            if (!$otpModel) {
                return $this->returnError('Validation Error', 'Invalid OTP', 404);
            }

            if ($otpModel->expiration < now()) {
                return $this->returnError('Validation Error', 'OTP has expired', 410);
            }

            $user = $this->getUserFromOtp($otpModel);
            if (!$user) {
                return $this->returnError('Error', 'User not found', 404);
            }

            $user->markEmailAsVerified();
            $user->verified = 1;
            $user->save();

            $walletBallance = 0.00;

            $success['user'] =  $user;
            $success['wallet_balance'] =  $walletBallance;

            return $this->returnSuccess($success, 'Email verification successful.');
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }       
    }

    private function getUserFromOtp(Otp $otpModel): ?User
    {
        return User::find($otpModel->user_id);
    }

    /**
     * @OA\Post(
     *     path="/api/users/resend-otp",
     *     operationId="resendOtp",
     *     tags={"Email Verification"},
     *     summary="Resend OTP to the user's email",
     *     description="Resend OTP to the user's email if they have not been verified or if the previous OTP has expired.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={"user_id"},
     *               @OA\Property(property="username", type="sring"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="message", type="string", example="OTP resent successfully.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal server error"),
     * )
     */
    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->first();

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

            if (!$user) {
                return $this->returnError('User not found.', 404);
            }

            if ($user->verified == 1) {
                return $this->returnError('User already verified.', 409);
            }

            $existingOtp = Otp::where('user_id', $user->id)->first();
            
            if ($existingOtp) {
                if ($existingOtp->expiration > now()) {
                    return $this->returnError('OTP already sent.', 400);
                }

                $otp = $this->generateOTP();

                $existingOtp->update([
                    'otp' => $otp,
                    'expiration' => now()->addMinutes(15),
                ]);
                $this->sendEmailOTP($email, $firstName, $lastName, $otp);
            } else {
                $otp = $this->generateOTP();
    
                Otp::create([
                    'otp' => $otp,
                    'expiration' => now()->addMinutes(15),
                    'user_id' => $user->id,
                ]);
                $this->sendEmailOTP($email, $firstName, $lastName, $otp);
            }

            return $this->returnSuccess($otp, 'Otp resent successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        } 
    }

    /**
     * @OA\Post(
     *     path="/api/users/forgot-password",
     *     operationId="forgotPassword",
     *     tags={"Forgot Password"},
     *     summary="Send password reset OTP",
     *     description="Send a one-time password (OTP) to the user's email for password reset.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              type="object",
     *               required={"username"},
     *               @OA\Property(property="username", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success: OTP sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reset password OTP sent successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     ),
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return $this->returnError('Check the provided email for otp.', 404);
            }

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

            $otp = $this->generateOTP();

            // Calculate expiration time (15 minutes from now)
            $expiration = Carbon::now()->addMinutes(15);

            // Check if there's an existing token for the user
            $existingToken = DB::table('password_reset_tokens')
            ->where('user_id', $user->id)
            ->first();

            if ($existingToken) {
                // Update existing token
                DB::table('password_reset_tokens')
                    ->where('user_id', $user->id)
                    ->update([
                        'otp' => $otp,
                        'expiration' => $expiration,
                    ]);
            } else {
                // Create new token
                DB::table('password_reset_tokens')->insert([
                    'user_id' => $user->id,
                    'email' => $email,
                    'otp' => $otp,
                    'expiration' => $expiration,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

                $this->sendPasswordResetEmail($email, $firstName, $lastName, $otp);

                return $this->returnSuccess($otp, 'Reset passwaord Otp sent successfully.', 200);
            } catch (\Throwable $th) {
                return $this->returnError('Error', $th->getMessage(), 500);
            }     
    }

    /**
     * @OA\Post(
     *     path="/api/users/verify-reset-password-otp",
     *     operationId="verifyResetPasswordOtp",
     *     tags={"Forgot Password"},
     *     summary="Verify reset password OTP",
     *     description="Verify the reset password OTP entered by the user.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "otp"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="otp", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success: OTP verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reset password OTP verified successful.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid OTP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid OTP.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="OTP has expired.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP has expired.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     ),
     * )
     */
    public function verifyResetPasswordOtp(Request $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->first();

            if ($request->has('otp') && empty($request->input('otp'))) {
                return $this->returnError('Validation Error', 'Otp field can not be empty', 401);
            }
            
            $otpModel = PasswordResetToken::findByOtp($request->input('otp'));

            if (!$otpModel) {
                return $this->returnError('Validation Error', 'Invalid OTP', 404);
            }

            if ($otpModel->expiration < now()) {
                return $this->returnError('Validation Error', 'OTP has expired', 410);
            }

            $resetUser = User::find($otpModel->user_id);

            return $this->returnSuccess([
                'message' => 'Reset password OTP verified successfully.',
                'user' => $resetUser,
            ], 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }       
    }

    /**
     * @OA\Post(
     *     path="/api/users/reset-password",
     *     operationId="resetPassword",
     *     tags={"Forgot Password"},
     *     summary="Reset user's password",
     *     description="Reset the user's password with a new password.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success: Password reset successful.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset successful.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     ),
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

            // Update password
            // $input['password'] = bcrypt($input['password']);
            $user->password = bcrypt($request->input('password'));

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $request->input('password'))) {
                return $this->returnError('Validation Error', 'Password must contain at least one uppercase letter, one digit, and be at least 8 characters long.', 400);
            }
            $user->save();

            // Send password reset success email
            $this->PasswordResetSuccessMail($email, $firstName, $lastName);

            return $this->returnSuccess('Password reset successful.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/change-password/{user_id}",
     *     operationId="changePassword",
     *     tags={"Change Password"},
     *     summary="Change user's password",
     *     description="Change the user's password with recent password and new password.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "recent_password", "new_password", "confirm_new_password"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="recent_password", type="string"),
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="confirm_new_password", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success: Password changed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password changed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     ),
     * )
     */
    public function changePassword(Request $request, $user_id): JsonResponse
    {
        try {
            $user = User::find($request->user_id);

            if (!$user) {
                return $this->returnError('User not found.', 404);
            }

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;
    
            if (!Hash::check($request->input('recent_password'), $user->password)) {
                return $this->returnError('Validation Error', 'Recent password is incorrect.', 401);
            }
    
            if (empty($request->input('new_password')) || empty($request->input('confirm_new_password'))) {
                return $this->returnError('Validation Error', 'New password and confirm new password fields cannot be empty.', 401);
            }
    
            if ($request->input('new_password') !== $request->input('confirm_new_password')) {
                return $this->returnError('Validation Error', 'New password and confirm new password do not match.', 401);
            }

            // Add password strength validation here
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $request->input('new_password'))) {
                return $this->returnError('Validation Error', 'Password must contain at least one uppercase letter, one digit, and be at least 8 characters long.', 400);
            }
    
            $user->password = bcrypt($request->input('new_password'));
            $user->save();

            $this->PasswordResetSuccessMail($email, $firstName, $lastName);
    
            return $this->returnSuccess('Password changed successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

    
    /**
     * @OA\Get(
     *     path="/api/users/get-info",
     *     operationId="getUserInfo",
     *     tags={"Get User Info"},
     *     summary="Get user information",
     *     description="Get user information by user ID.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         description="The ID of the user",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="john_doe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="walletBalance", type="number", example=100.0),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     */
    public function getUserInfo(Request $request)
    {
        try {
            // Validate the request data (e.g., user ID)
            $request->validate([
                'user_id' => 'required',
            ]);

            $user = User::find($request->user_id);

            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            $wallet =  Wallet::where("user_id", "=", $request->user_id)->first();

            // You can customize the data you want to include in the response
            $userData = [
                'user_id' => $user->id,
                'username' => $user->name,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'walletBalance' => $wallet->amount,
                
                // Add more fields as needed
            ];

            if (!$userData){
                throw new \Exception('No data available for this user');
            }
            return response()->json(['user' => $userData]);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }
}
