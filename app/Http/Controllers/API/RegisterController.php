<?php
   
namespace App\Http\Controllers\API;
   
use Validator;
use App\Models\Otp;
use App\Models\User;
use App\ValidationRules;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\PasswordResetMail;
use Illuminate\Http\JsonResponse;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\API\BaseController as BaseController;
   
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
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"first_name","last_name","username","phone_number","email", "password", "password_confirmation"},
     *               @OA\Property(property="first_name", type="text"),
     *               @OA\Property(property="last_name", type="text"),
     *               @OA\Property(property="username", type="text"),
     *               @OA\Property(property="phone_number", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="password_confirmation", type="password")
     *            ),
     *        ),
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

            if ($this->isEmailExistsInDatabase($request->input('email'))) {
                return $this->returnError('Validation Error', 'Email already taken');
            }

            if ($this->isUsernameExistsInDatabase($request->input('username'))) {
                return $this->returnError('Validation Error', 'Username already in use');
            }

            if ($this->isPhoneExistsInDatabase($request->input('phone_number'))) {
                return $this->returnError('Validation Error', 'Phone Number already in use');
            }



            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);

            if (!$user) {
                return $this->sendError('Something went wrong, please try again.', $user->errors());
            }

            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->first_name;

            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

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
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"username", "password"},
     *               @OA\Property(property="username", type="username"),
     *               @OA\Property(property="password", type="password")
     *            ),
     *        ),
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
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            if(!empty($user->email_verified_at)){
                $success['token'] =  $user->createToken('MyApp')->accessToken;
                $success['user'] =  $user;
                return $this->returnSuccess($success, 'User signed up successfully.');
            }
            return $this->returnError('Error', "You have not verified your email", 410);
        }else{ 
            return $this->returnError('Error', 'Invalid username or password', 401);
        } 
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
     *         @OA\JsonContent(),
     *            @OA\Schema(
     *               type="object",
     *               required={""},
     *               @OA\Property(property="address", type="string"),
     *            ),
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
            $user_id = 1;
            $user = User::find($user_id);

            if (!$user) {
                return $this->returnError('User not found.', 404);
            }

            $existingData = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->address,
            ];

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

            $user->first_name = $request->input('first_name', $user->first_name);
            $user->last_name = $request->input('last_name', $user->last_name);
            $user->username = $request->input('username', $user->username);
            $user->phone_number = $request->input('phone_number', $user->phone_number);
            $user->email = $request->input('email', $user->email);
            $user->address = $request->input('address', $user->address);

            $user->save();

            $success = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->address,
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
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"otp"},
     *               @OA\Property(property="otp", type="string"),
     *            ),
     *        ),
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

            return $this->returnSuccess($user, 'Email verification successful.');
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
            $user_id = 1;
            $user = User::find($user_id);

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
            } else {
                $otp = $this->generateOTP();
    
                Otp::create([
                    'otp' => $otp,
                    'expiration' => now()->addMinutes(15),
                    'user_id' => $user->id,
                ]);
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
            $user_id = 1;
            $user = User::find($user_id);
            $email = $user->email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;

            if (!$user) {
                return $this->returnError('User not found.', 404);
            }

            $otp = $this->generateOTP();

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['otp' => $otp, 'created_at' => now()]
            );

            $this->sendPasswordResetEmail($email, $firstName, $lastName, $otp);

            return $this->returnSuccess($otp, 'Reset passwaord Otp sent successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }     
    }
}