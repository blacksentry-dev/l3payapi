<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
  
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('/users/verify-email', [RegisterController::class, 'verifyEmail']);
Route::put('/users/profile-update', [RegisterController::class, 'updateProfile']);
Route::post('/users/forgot-password', [RegisterController::class, 'forgotPassword']);

//Feedback and Rating
Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);
Route::get('/feedback/user/{user_id}', [FeedbackController::class, 'getUserFeedback']);
Route::get('/rating/average', [FeedbackController::class, 'getAverageFeedback']);
//Wallet Payment
Route::post('/wallet/create/{user_id}', [WalletController::class, 'createUsersWallet']);
Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
Route::get('/wallet/balance', [WalletController::class, 'getWalletBalance']);
Route::post('/wallet/payment', [WalletController::class, 'makeWalletPayment']);
     
Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
    Route::post('/users/send-registration-email', [RegisterController::class, 'sendRegistrationOTP']);
});
