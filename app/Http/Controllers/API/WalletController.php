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

class WalletController extends BaseController
{

    
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
        dd($wallet);

        $wallet->balance += $amount;
        $wallet->save();

        return $this->sendResponse(['status' => 'success'], 'Cest bonne.');

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
