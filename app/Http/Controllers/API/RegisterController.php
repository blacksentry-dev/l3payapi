<?php
   
namespace App\Http\Controllers\API;
   
use Validator;
use App\Models\Otp;
use App\Models\User;
use App\ValidationRules;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'username' => 'required|string|max:255|unique:users',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        if(!$user){
            return $this->sendError('Something went wrong, please try again.', $user->errors());    
        }

        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['Name'] =  $user->first_name;

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
        // $this->storeOTPInCache($email, $otp);
   
        return $this->sendResponse($success, 'User signed up successfully.');
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
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['Name'] =  $user->first_name;
   
            return $this->sendResponse($success, 'User signed in successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }


    /**
     * @OA\Put(
     *     path="/api/users/profile",
     *     operationId="UpdateProfile",
     *     tags={"Profile"},
     *     summary="Update User Profile",
     *     description="Update user profile information",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address"},
     *               @OA\Property(property="first_name", type="string"),
     *               @OA\Property(property="last_name", type="string"),
     *               @OA\Property(property="address", type="string"),
     *            ),
     *        ),
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

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255|nullable',
            'last_name' => 'string|max:255|nullable',
            'address' => 'string|nullable',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user_id = 5;
        $user = User::find($user_id);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        if ($request->filled(['first_name', 'last_name', 'address'])) {
            $user->update([
                'first_name' => $request->input('first_name', $user->first_name),
                'last_name' => $request->input('last_name', $user->last_name),
                'address' => $request->input('address', $user->address),
            ]);
        }
        
    
        return $this->sendResponse(['status' => 'success'], 'Cest bonne.');
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

        $validator = Validator::make($request->all(), [
            'otp' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        $otp = $request->input('otp');
        $otpModel = Otp::findByOtp($otp);

        if (!$otpModel) {
            return $this->sendError('Invalid OTP.', [], 400);
        }

        // Check if the OTP has expired
        if ($otpModel->expiration < now()) {
            return $this->sendError('OTP has expired.', [], 400);
        }

        // Retrieve the associated user details
        $user = $this->getUserFromOtp($otpModel);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        // Update the user's email_verified field to true
        $user->markEmailAsVerified();
        $user->save();

        return $this->sendResponse(['status' => 'success', 'user' => $user], 'Email verification successful.'); 

       
        // return $otp;
    

    }

    private function getUserFromOtp(Otp $otpModel): ?User
    {
        return User::find($otpModel->user_id);
    }

    public function markEmailAsVerified()
    {
        $this->verified = true;
        $this->email_verified_at = Carbon::now();
        $this->save();
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        
        // Generate a unique password reset token
        $token = Str::random(60);

        // Store the token in the password_resets table
        $user->password_reset_token()->create([
            'email' => $user->email,
            'token' => $token,
        ]);

        // Send the password reset email
        Mail::to($user->email)->send(new PasswordResetMail($token));

        // dd($user->passwordReset);

        return $this->sendResponse(['status' => 'success'], 'Cest bonne.');
    }

    // private function sendResetPasswordMail(string $email, string $firstName, string $lastName, string $otp)
    // {
    //     // Construct the email message
    //     $message = "Hello $firstName $lastName,\n\n";
    //     $message .= "Click the link to reset your password:\n";
    //     $message .= "$otp\n\n";
    //     $message .= "If you didn't sign up for this service, please disregard this email.\n";

    //     // Send the email
    //     Mail::raw($message, function ($emailMessage) use ($email) {
    //         $emailMessage->to($email)
    //             ->subject('Email Verification OTP');
    //     });
    // }
}