<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\User;
use App\Models\Wallet;
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
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="string"),
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
    public function createUsersWallet($user_id): JsonResponse
    {
        $user_id = 5;
        $user = User::find($user_id);
        // dd($user);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        // Check if the user already has a wallet
        if ($user->wallet) {
            return $this->sendError('Wallet already exists for the user.', [], 400);
        }

        // Create a new wallet for the user
        $wallet = new Wallet();
        $wallet->user_id = $user_id;
        $wallet->amount = 0.00;
        $wallet->date_created = now();
        $wallet->status = 'active';
        $wallet->save();

        return $this->sendResponse(['status' => 'success'], 'Successfully created users wallet.');

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

        return $this->sendResponse(['status' => 'success'], 'Cest bonne.');

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

    public function makeWalletPayment(Request $request): JsonResponse
    {
        dd($request);
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
