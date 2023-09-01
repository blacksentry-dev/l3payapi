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
     *     path="/api/wallet/create",
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
        $userwallet = Wallet::where('user_id', $request->user_id)->first();

        try {
            if (!is_null($userwallet)) {
                $userwallet = Wallet::where('user_id', $request->user_id)->first();
                $wallet = Wallet::find($userwallet->id);
                $wallet->amount = sprintf("%.2f", $userwallet->amount) + sprintf("%.2f", $request->amount);
                $wallet->save();
                return $this->returnSuccess($wallet, 'Wallet updated successfully.', 200);
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
     *     path="/api/wallet/balance/{user_id}",
     *     operationId="getWalletBalance",
     *     tags={"Wallet"},
     *     summary="Get Wallet Balance",
     *     description="Retrieve the balance of the user's wallet.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *          description="ID of User",
     *          in="path",
     *          name="user_id",
     *          required=true,
     *          example="1",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
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

    public function getWalletBalance($user_id): JsonResponse
    {
        try {
            $wallet = Wallet::where('user_id', $user_id)->first();
            if (!$wallet) {
                return $this->returnError(null, 'Wallet not found', 404);
            }
            return $this->returnSuccess($wallet, 'User Wallet retrieved successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
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

    public function makeWalletPayment(Request $request, $user_id): JsonResponse
    {
        try {
                if ($request->has('amount') && empty($request->input('amount'))) {
                    return $this->returnError('Validation Error', 'amount field can not be empty');
                }
                
                // Get user from user_id
                $user = User::find($request->input('user_id'));
            
                if (!$user) {
                    return $this->sendError('User not found.', [], 404);
                }
            
                $amount = $request->input('amount');
            
                // Check if user's wallet balance is sufficient
                $wallet = $user->wallet;
            
                if ($wallet->amount < $amount) {
                    return $this->sendError('Insufficient Wallet Balance.');
                }
            
                // Deduct the payment amount from the wallet
                $wallet->amount -= $amount;
                $wallet->save();

                return $this->returnSuccess($wallet, 'Wallet payment successfully.', 200);
            } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        } 
    
        // return $this->sendResponse(['status' => 'success'], 'Payment Successful!');
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
