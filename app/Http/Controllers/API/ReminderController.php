<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reminder;
use App\Http\Requests\StoreReminderRequest;
use App\Http\Requests\UpdateReminderRequest;
use App\Http\Controllers\API\BaseController as BaseController;


class ReminderController extends BaseController
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
    public function store(StoreReminderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reminder $reminder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reminder $reminder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReminderRequest $request, Reminder $reminder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reminder $reminder)
    {
        //
    }

    public function schedulePaymentReminder(Request $request): JsonResponse
    {
        try {
            if ($request->has('payment_due_date') && empty($request->input('payment_due_date'))) {
                return $this->returnError('Validation Error', 'Payment due date field can not be empty');
            }

            if ($request->has('notification_time') && empty($request->input('notification_time'))) {
                return $this->returnError('Validation Error', 'Notification time field can not be empty');
            }

            // Get the user
            $user = User::where('id', $request->user_id)->first();

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }

            // Create a payment reminder
            Reminder::create([
                'user_id' => $user->id,
                'payment_due_date' => $request->payment_due_date,
                'notification_time' => $request->notification_time,
            ]);

            return $this->returnSuccess('Payment reminder scheduled successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

    public function getPaymentReminders(Request $request): JsonResponse
    {
        try {
            $reminders = Reminder::where('user_id', $request->user_id)->orderBy('id', 'DESC')->get();
            if(count($reminders) > 0){
                return $this->returnSuccess($reminders, 'Reminders retrieved successfully.', 200);
            }else{
                return $this->returnError($reminders, 'Reminders not found', 404);
            }
        } catch (\Throwable $th) {
            return $this->returnError("Error", $th->getMessage(), 500);
        }
    }

    public function cancelPaymentReminder(Request $request): JsonResponse
    {
        try {
            $reminder = Reminder::find($request->reminder_id);
            // $user = User::where('id', $request->user_id)->first();

            if (!$reminder) {
                return $this->returnError('Reminder not found.', [], 404);
            }

            $reminder->delete();

            return $this->returnSuccess('Payment reminder cancelled.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }

}