<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\PaymentMethod;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends BaseController
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
    public function store(StorePaymentMethodRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        //
    }

    public function addPaymentMethod(Request $request) :JsonResponse
    {
        try {
            // Get the user
            $user = User::where('id', $request->user_id)->first();

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }

            if ($request->has('cardholder_name') && empty($request->input('cardholder_name'))) {
                return $this->returnError('Validation Error', 'Cardholder name field can not be empty');
            }

            if ($request->has('card_number') && empty($request->input('card_number'))) {
                return $this->returnError('Validation Error', 'Card number field can not be empty');
            }

            if ($request->has('expiration_date') && empty($request->input('expiration_date'))) {
                return $this->returnError('Validation Error', 'Expiration date field can not be empty');
            }

            if ($request->has('card_type') && empty($request->input('card_type'))) {
                return $this->returnError('Validation Error', 'Card type field can not be empty');
            }

            $paymentMethods = PaymentMethod::create([
                'user_id' => $request->user_id,
                'cardholder_name' => $request->cardholder_name,
                'card_number' => $request->card_number,
                'expiration_date' => $request->expiration_date,
                'card_type' => $request->card_type,
            ]);

            return $this->returnSuccess('Payment method added successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

    public function deletePaymentMethod(Request $request) :JsonResponse
    {
        try {
            $user = User::where('id', $request->user_id)->first();

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }
            $paymentMethod = PaymentMethod::where('id', $request->payment_method_id)
            ->where('user_id', $user->id)
            ->first();
            
            if (!$paymentMethod) {
                // If the payment method doesn't exist or is not associated with the user, return an error response.
                return response()->json(['message' => 'Payment method not found'], 404);
            }
        
            // Now, you can delete the payment method.
            $paymentMethod->delete();

            return $this->returnSuccess('Payment method deleted successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }
    
    public function updatePaymentMethod(Request $request) :JsonResponse
    {
        try {
            $user = User::where('id', $request->user_id)->first();

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }
            $paymentMethod = PaymentMethod::where('id', $request->payment_method_id)
            ->where('user_id', $user->id)
            ->first();
            
            if (!$paymentMethod) {
                // If the payment method doesn't exist or is not associated with the user, return an error response.
                return response()->json(['message' => 'Payment method not found'], 404);
            }
        
            if ($request->has('cardholder_name') && empty($request->input('cardholder_name'))) {
                return $this->returnError('Validation Error', 'Cardholder name field can not be empty');
            }

            if ($request->has('card_number') && empty($request->input('card_number'))) {
                return $this->returnError('Validation Error', 'Card number field can not be empty');
            }

            if ($request->has('expiration_date') && empty($request->input('expiration_date'))) {
                return $this->returnError('Validation Error', 'Expiration date field can not be empty');
            }

            if ($request->has('card_type') && empty($request->input('card_type'))) {
                return $this->returnError('Validation Error', 'Card type field can not be empty');
            }

            $paymentMethod->cardholder_name = $request->input('cardholder_name', $paymentMethod->cardholder_name);
            $paymentMethod->card_number = $request->input('card_number', $paymentMethod->card_number);
            $paymentMethod->expiration_date = $request->input('expiration_date', $paymentMethod->expiration_date);
            $paymentMethod->card_type = $request->input('card_type', $paymentMethod->card_type);

            $paymentMethod->save();

            return $this->returnSuccess('Payment method deleted successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }
}
