<?php

use App\Http\Controllers\API\PaymentMethodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ReminderController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\OnlineRenewSubscription;

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
    Route::post('/users/verify-password-otp', [RegisterController::class, 'verifyResetPasswordOtp']);
    Route::post('/users/reset-password', [RegisterController::class, 'resetPassword']);
    Route::post('/users/resend-otp', [RegisterController::class, 'resendOtp']);
    Route::post('/users/change-password/{user_id}', [RegisterController::class,'changePassword']);

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
    Route::post('/wallet/payment/{user_id}', [WalletController::class, 'makeWalletPayment']);
    // Transaction
    Route::post('/user/transaction', [TransactionController::class, 'createTransaction']);
    Route::get('/user/get-transaction/{user_id}', [TransactionController::class, 'getUserTransaction']);
    Route::get('/user/total-transaction/{user_id}', [TransactionController::class, 'getUserTotalTransaction']);
    Route::get('/user/monthly-transaction/{user_id}', [TransactionController::class, 'getUserMonthlyTransaction']);
    Route::get('/user/user-transaction-category/{category}/{user_id}', [TransactionController::class, 'getUserTransactionByCategory']);
    Route::post('/user/transaction-pin/create', [TransactionController::class, 'setTransactionPin']);
    Route::put('/user/transaction-pin/update', [TransactionController::class, 'updateTransactionPin']);

    //Reminder
    Route::post('/reminder/schedule', [ReminderController::class, 'schedulePaymentReminder']);
    Route::get('/reminder/user-reminder', [ReminderController::class, 'getPaymentReminders']);
    Route::post('/reminder/cancel', [ReminderController::class, 'cancelPaymentReminder']);
    //Tickets
    Route::post('/tickets/create', [TicketController::class, 'createTicket']);
    //Payment Methods
    Route::post('/payment-method/create', [PaymentMethodController::class, 'addPaymentMethod']);
    Route::post('/payment-method/delete', [PaymentMethodController::class, 'deletePaymentMethod']);
    Route::put('/payment-method/update', [PaymentMethodController::class, 'updatePaymentMethod']);

});


//Feedback and Rating
Route::post('/feedback', [FeedbackController::class, 'submitFeedback']);
Route::get('/feedback/user/{user_id}', [FeedbackController::class, 'getUserFeedback']);
Route::get('/rating/average', [FeedbackController::class, 'getAverageFeedback']);
//Wallet Payment
Route::post('/wallet/create', [WalletController::class, 'createUsersWallet']);
Route::post('/wallet/fund', [WalletController::class, 'fundWallet']);
Route::get('/wallet/balance/{user_id}', [WalletController::class, 'getWalletBalance']);
Route::post('/wallet/payment/{user_id}', [WalletController::class, 'makeWalletPayment']);
// Transaction
Route::post('/user/transaction', [TransactionController::class, 'createTransaction']);
Route::get('/user/get-transaction/{user_id}', [TransactionController::class, 'getUserTransaction']);
Route::get('/user/total-transaction/{user_id}', [TransactionController::class, 'getUserTotalTransaction']);
Route::get('/user/monthly-transaction/{user_id}', [TransactionController::class, 'getUserMonthlyTransaction']);
Route::get('/user/user-transaction-category/{category}/{user_id}', [TransactionController::class, 'getUserTransactionByCategory']);
Route::post('/user/transaction-pin/create', [TransactionController::class, 'setTransactionPin']);
Route::post('/user/transaction-pin/update', [TransactionController::class, 'updateTransactionPin']);

Route::post('24online/renew-package', [OnlineRenewSubscription::class, 'RenewSubscription']);
Route::post('24online/user-status', [OnlineRenewSubscription::class, 'UserStatus']);
Route::post('24online/renewal-history', [OnlineRenewSubscription::class, 'RenewalHistory']);
Route::post('24online/user-password', [OnlineRenewSubscription::class, 'getUserPassword']);
Route::post('24online/user-usage-info', [OnlineRenewSubscription::class, 'getUserUsageInfo']);
Route::post('24online/payment-status', [OnlineRenewSubscription::class, 'getPaymentStatus']);
Route::post('24online/invoice-detail', [OnlineRenewSubscription::class, 'getInvoiceDetail']);
Route::post('24online/session-usage-detail', [OnlineRenewSubscription::class, 'sessionUsageDetails']);


Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class);
    Route::post('/users/send-registration-email', [RegisterController::class, 'sendRegistrationOTP']);
});

Route::post('/reminder/schedule', [ReminderController::class, 'schedulePaymentReminder']);
Route::get('/reminder/user-reminder', [ReminderController::class, 'getPaymentReminders']);
Route::post('/reminder/cancel', [ReminderController::class, 'cancelPaymentReminder']);
