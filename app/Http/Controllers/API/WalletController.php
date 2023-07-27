<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\Bill;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Client\Request as ClientRequest;

class WalletController extends BaseController
{


    /**
     * @OA\Post(
     *     path="/api/wallet/create/{user_id}",
     *     operationId="createWallet",
     *     tags={"Wallet"},
     *     summary="Create Wallet",
     *     description="Create a wallet for a user.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="amount", type="double"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Wallet created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Wallet created successfully.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="User not found"),
     * )
     */
    public function createUsersWallet(Request $request): JsonResponse
    {
        $user = User::find($request->user_id);

        try {
            if ($user->wallet) {
                $userwallet = Wallet::where('user_id', $request->user_id)->first();
                $userwallet->amount = $userwallet->amount + $request->amount;
                return $this->returnSuccess($userwallet, 'Wallet updated successfully.', 200);
            }else{
                $wallet = new Wallet();
                $wallet->user_id = $request->user_id;
                $wallet->amount = $request->amount;
                $wallet->date_created = now();
                $wallet->status = 'active';
                $wallet->save();
                return $this->returnSuccess($wallet, 'Wallet created successfully.', 200);
            }
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        } 
    }
    


    /**
     * @OA\Post(
     *     path="/api/wallet/fund",
     *     operationId="fundWallet",
     *     tags={"Wallet"},
     *     summary="Fund Wallet",
     *     description="Fund the user's wallet with a specified amount.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "amount"},
     *             @OA\Property(property="user_id", type="string"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wallet funded successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cest bonne.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="User not found"),
     * )
     */
    public function fundWallet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);    

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        //Get user from user_id
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $amount = $request->input('amount');

        // Perform the necessary actions to fund the wallet
        $wallet = $user->wallet;
        $wallet->amount += $amount;
        // dd($wallet->amount);
        $wallet->save();

        return $this->sendResponse(['status' => 'success'], 'Wallet successfully funded.');

    }


    /**
     * @OA\Get(
     *     path="/api/wallet/balance",
     *     operationId="getWalletBalance",
     *     tags={"Wallet"},
     *     summary="Get Wallet Balance",
     *     description="Retrieve the balance of the user's wallet.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Wallet balance retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", example="123456789"),
     *                 @OA\Property(property="balance", type="number", format="float", example=150.00),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Wallet not found"),
     * )
     */

    public function getWalletBalance(Request $request): JsonResponse
    {
        $user_id = 5;
        $wallet = Wallet::where('user_id', $user_id)->first();

        if (!$wallet) {
            return $this->sendError('Wallet not found.', [], 404);
        }

        $balance = $wallet->amount;

        $data = [
            'user_id' => $user_id,
            'balance' => $balance,
        ];

        return $this->sendResponse(['status' => 'success','data' => $data], 'Wallet balance retrieved successfully.');

    }


    /**
     * @OA\Post(
     *     path="/api/wallet/payment",
     *     operationId="walletPayment",
     *     tags={"Wallet"},
     *     summary="Make a payment from the user's wallet",
     *     description="Make a payment from the user's wallet balance.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "payment_amount", "bill_id"},
     *             @OA\Property(property="user_id", type="string"),
     *             @OA\Property(property="payment_amount", type="number", format="float"),
     *             @OA\Property(property="bill_id", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment successful.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     * )
     */

    public function makeWalletPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'payment_amount' => 'required|numeric|min:0',
            'bill_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->input('user_id'));
        $bill = Bill::find($request->input('bill_id'));

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $paymentAmount = $request->input('payment_amount');
        // $billId = $request->input('bill_id');
        $billDescription = $bill->description;

        // Check if the user's wallet balance is sufficient to cover the payment
        if ($user->wallet->amount < $paymentAmount) {
            return $this->sendError('Insufficient wallet balance.', [], 400);
        }

        // Perform the necessary actions to process the wallet payment
        // For this example, we'll just deduct the payment amount from the user's wallet balance

        $user->wallet->amount -= $paymentAmount;
        $user->wallet->save();


        //Create a new wallet payment transaction record
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'wallet',
            'description' => "Payment for {$billDescription} bill",
            'amount' => $paymentAmount,
        ]);

        return $this->sendResponse(['status' => 'success'], 'Payment Successful!');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWalletRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}
