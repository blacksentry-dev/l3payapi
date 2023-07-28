<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends BaseController
{
    
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
    public function store(StoreTransactionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/user/transaction",
     *     operationId="createTransaction",
     *     tags={"Transaction"},
     *     summary="Create Transaction",
     *     description="Create a Transaction for a user.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "type","description","amount"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="category", type="string"),
     *             @OA\Property(property="amount", type="double"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction created successfully.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal Server Error"),
     *     @OA\Response(response=404, description="Transaction not found"),
     * )
     */
    public function createTransaction(Request $request){
        try {
            $transaction = new Transaction();
            $transaction->user_id = $request->user_id;
            $transaction->type = $request->type;
            $transaction->description = $request->description;
            $transaction->category = $request->category;
            $transaction->amount = $request->amount;
            $transaction->save();
            return $this->returnSuccess($transaction, 'Transaction created successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError("Error",$th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/user/get-transaction/{user_id}",
     *     operationId="getTransaction",
     *     tags={"Transaction"},
     *     summary="Get User Transactions",
     *     description="Get all transactions for a user.",
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
     *         description="Transactions retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transactions retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal Server Error"),
     *     @OA\Response(response=404, description="Transaction not found"),
     * )
     */
    public function getUserTransaction($user_id){
        try {
            $transaction = Transaction::where('user_id', $user_id)->get();
            if(count($transaction) > 0){
                return $this->returnSuccess($transaction, 'Transactions retrieved successfully.', 200);
            }else{
                return $this->returnError($transaction, 'Transaction not found', 404);
            }
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/user/total-transaction/{user_id}",
     *     operationId="getUserTotalTransaction",
     *     tags={"Transaction"},
     *     summary="Get User Total Transactions",
     *     description="Get all transactions for a user.",
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
     *         description="Transactions retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transactions retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal Server Error"),
     *     @OA\Response(response=404, description="Transaction not found"),
     * )
     */
    public function getUserTotalTransaction($user_id){
        try {
            $transaction = Transaction::where('user_id', $user_id)->get();
            $totaltransaction = $transaction->sum('amount');
            return $this->returnSuccess($totaltransaction, 'Transactions retrieved successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/user/monthly-transaction/{user_id}",
     *     operationId="getUserTotalMonthlyTransaction",
     *     tags={"Transaction"},
     *     summary="Get User Total Transactions",
     *     description="Get all transactions for a user.",
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
     *         description="Transactions retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transactions retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal Server Error"),
     *     @OA\Response(response=404, description="Transaction not found"),
     * )
     */
    public function getUserMonthlyTransaction($user_id){
        try {
            $monthlytransaction = Transaction::select('*')
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('user_id', $user_id)
            ->get();
            $totalmonthlytransaction = $monthlytransaction->sum('amount');
            return $this->returnSuccess($totalmonthlytransaction, 'Transactions retrieved successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }
}
