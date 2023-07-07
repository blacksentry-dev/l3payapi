<?php
   
namespace App\Http\Controllers\API;
   
use Validator;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
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
        $user = $this->getUserFromToken($request->bearerToken());

        if (!$user) {
            return $this->sendError('Invalid token', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'string',
            'last_name' => 'string',
            'address' => 'string',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = Auth::user();
        $user->email = $request->input('email');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->address = $request->input('address');
        $user->save();
    
        return $this->sendResponse(['status' => 'success'], 'Profile details validated.');
    }

    private function getUserFromToken(string $token)
    {
        $payload = Auth::guard('api')->payload();

        if (!$payload || !$payload->get('sub')) {
            return null;
        }

        return User::find($payload->get('sub'));
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

    // private function storeOTPInCache(string $email, string $otp)
    // {
    //     $expiration = now()->addMinutes(15);
    //     Cache::put($email, $otp, $expiration);
    // }

    /**
     * @OA\Post(
     *     path="/api/users/verify-email",
     *     operationId="verifyEmail",
     *     tags={"Verification"},
     *     summary="Verify Email",
     *     description="Verify the user's email using the OTP (One-Time Password) sent during registration.",
     *     security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"email", "otp"},
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="otp", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification successful.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function verifyEmail(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        
        $email = $request->input('email');
        $otp = $request->input('otp');

        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $storedOTP = $this->getStoredOTPFromCache($email);

        if (!$storedOTP || $storedOTP !== $otp) {
            return $this->sendError('Invalid OTP.', [], 400);
        }

        $user->email_verified = true;
        $user->save();

        return $this->sendResponse(['status' => 'success'], 'Email verification successful.');
    }
}