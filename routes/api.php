<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RegisterController;
  
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
Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);
Route::get('/feedback/user/{user_id}', [FeedbackController::class, 'getUserFeedback']);
Route::get('/rating/average', [FeedbackController::class, 'getAverageFeedback']);
     
Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
    Route::put('/users/profile', [RegisterController::class, 'updateProfile']);
    Route::post('/users/send-registration-email', [RegisterController::class, 'sendRegistrationOTP']);
});
