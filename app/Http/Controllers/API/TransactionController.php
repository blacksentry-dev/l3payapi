<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Controllers\API\BaseController as BaseController;

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
            $transaction = Transaction::where('user_id', $user_id)->orderBy('id', 'DESC')->get();
            // if(count($transaction) > 0){
            //     return $this->returnSuccess($transaction, 'Transactions retrieved successfully.', 200);
            // }else{
            //     return $this->returnError($transaction, 'Transaction not found', 404);
            // }
            if ($transaction->isEmpty()) {
                // Return an empty array with a 204 status code (No Content)
                return $this->returnSuccess([], 'No transactions found.', 204);
            } else {
                return $this->returnSuccess($transaction, 'Transactions retrieved successfully.', 200);
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

    /**
     * @OA\GET(
     *     path="/api/user/user-transaction-category/{category}/{user_id}",
     *     operationId="getUserTransactionByCategory",
     *     tags={"Transaction"},
     *     summary="Get Transactions By Category For User",
     *     description="Get all transactions for a user in a categoty.",
     *     @OA\Parameter(
     *          description="Transaction category",
     *          in="path",
     *          name="category",
     *          required=true,
     *          example="wallet",
     *          @OA\Schema(
     *              type="string",
     *              format="string"
     *          )
     *     ),
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
    public function getUserTransactionByCategory($category,$user_id)
    {
        try {
            $transaction = Transaction::where('user_id', $user_id)
            ->where('category', $category)
            ->orderBy('id', 'DESC')->get();
            return $this->returnSuccess($transaction, 'Transactions retrieved successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/transaction-pin/create",
     *     operationId="setTransactionPin",
     *     tags={"Transaction Pin"},
     *     summary="Set a user's transaction PIN.",
     *     description="Set a user's transaction PIN.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON request body for setting transaction PIN",
     *         @OA\JsonContent(
     *             required={"user_id", "transaction_pin"},
     *             @OA\Property(property="user_id", type="integer", example="1"),
     *             @OA\Property(property="transaction_pin", type="integer", example="1234"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction PIN set successfully.",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *     ),
     * )
     */
    public function setTransactionPin(Request $request)
    {
        try {
            // Validate the request data (e.g., PIN, user ID)
            $request->validate([
                'user_id' => 'required',
                'transaction_pin' => 'required|digits:4', // Adjust validation rules as needed
            ]);

            $user = User::find($request->user_id);
            
            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            $user->transaction_pin = bcrypt($request->transaction_pin); // Hash the PIN for security
            $user->save();

            return response()->json(['message' => 'Transaction PIN set successfully']);
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
        
    }

    /**
     * @OA\Post(
     *     path="/api/transaction-pin/update",
     *     operationId="updateTransactionPin",
     *     tags={"Transaction Pin"},
     *     summary="Update a user's transaction PIN.",
     *     description="Update a user's transaction PIN.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON request body for updating transaction PIN",
     *         @OA\JsonContent(
     *             required={"user_id", "old_pin", "new_pin"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="old_pin", type="integer", example=1234),
     *             @OA\Property(property="new_pin", type="integer", example=5678),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction PIN updated successfully.",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid old PIN.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error.",
     *     ),
     * )
     */
    public function updateTransactionPin(Request $request)
    {
        try {
            // Validate the request data (e.g., user ID, old PIN, new PIN)
            $request->validate([
                'user_id' => 'required|numeric',
                'old_pin' => 'required|digits:4',
                'new_pin' => 'required|digits:4',
            ]);

            // Verify the old PIN
            $user = User::find($request->user_id);

            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            if (!password_verify($request->old_pin, $user->transaction_pin)) {
                return response()->json(['error' => 'Invalid old PIN'], 422);
            }

            // Update the PIN
            $user->transaction_pin = bcrypt($request->new_pin);
            $user->save();

            return response()->json(['message' => 'Transaction PIN updated successfully']);

        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
        
    }
}
