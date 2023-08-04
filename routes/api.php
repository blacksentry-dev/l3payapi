<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\OnlineRenewSubscription;
use App\Http\Controllers\API\TransactionController;

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
  


Route::group(['middleware' => 'cors'], function () {
    // Define your API routes here
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [RegisterController::class, 'login']);
    Route::post('/users/verify-email', [RegisterController::class, 'verifyEmail']);
    Route::put('/users/profile-update', [RegisterController::class, 'updateProfile']);
    Route::post('/users/forgot-password', [RegisterController::class, 'forgotPassword']);
    Route::post('/users/verify-password-otp/{user_id}', [RegisterController::class, 'verifyResetPasswordOtp']);
    Route::post('/users/resend-otp', [RegisterController::class, 'resendOtp']);

    //Feedback and Rating
    Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);
    Route::get('/feedback/user/{user_id}', [FeedbackController::class, 'getUserFeedback']);
    Route::get('/rating/average', [FeedbackController::class, 'getAverageFeedback']);
    //Wallet Payment
    Route::post('/wallet/create', [WalletController::class, 'createUsersWallet']);
    Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
    Route::get('/wallet/balance/{user_id}',
        [WalletController::class, 'getWalletBalance']
    );
    Route::post('/wallet/payment', [WalletController::class, 'makeWalletPayment']);
    // Transaction
    Route::post('/user/transaction', [TransactionController::class, 'createTransaction']);
    Route::get('/user/get-transaction/{user_id}', [TransactionController::class, 'getUserTransaction']);
    Route::get('/user/total-transaction/{user_id}', [TransactionController::class, 'getUserTotalTransaction']);
    Route::get('/user/monthly-transaction/{user_id}', [TransactionController::class, 'getUserMonthlyTransaction']);
    Route::get('/user/user-transaction-category/{category}/{user_id}', [TransactionController::class, 'getUserTransactionByCategory']);
});


//Feedback and Rating
Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);
Route::get('/feedback/user/{user_id}', [FeedbackController::class, 'getUserFeedback']);
Route::get('/rating/average', [FeedbackController::class, 'getAverageFeedback']);
//Wallet Payment
Route::post('/wallet/create', [WalletController::class, 'createUsersWallet']);
Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
Route::get('/wallet/balance/{user_id}', [WalletController::class, 'getWalletBalance']);
Route::post('/wallet/payment', [WalletController::class, 'makeWalletPayment']);
// Transaction
Route::post('/user/transaction', [TransactionController::class, 'createTransaction']);
Route::get('/user/get-transaction/{user_id}', [TransactionController::class, 'getUserTransaction']);
Route::get('/user/total-transaction/{user_id}', [TransactionController::class, 'getUserTotalTransaction']);
Route::get('/user/monthly-transaction/{user_id}', [TransactionController::class, 'getUserMonthlyTransaction']);
Route::get('/user/user-transaction-category/{category}/{user_id}', [TransactionController::class, 'getUserTransactionByCategory']);

Route::post('24online/renew-package', [OnlineRenewSubscription::class, 'RenewSubscription']);
Route::post('24online/user-status', [OnlineRenewSubscription::class, 'UserStatus']);
Route::post('24online/renewal-history', [OnlineRenewSubscription::class, 'RenewalHistory']);
Route::post('24online/user-password', [OnlineRenewSubscription::class, 'getUserPassword']);
Route::post('24online/user-usage-info', [OnlineRenewSubscription::class, 'getUserUsageInfo']);
Route::post('24online/payment-status', [OnlineRenewSubscription::class, 'getPaymentStatus']);
Route::post('24online/invoice-detail', [OnlineRenewSubscription::class, 'getInvoiceDetail']);


Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
    Route::post('/users/send-registration-email', [RegisterController::class, 'sendRegistrationOTP']);
});
