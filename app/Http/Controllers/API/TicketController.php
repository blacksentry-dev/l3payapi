<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Controllers\API\BaseController as BaseController;

class TicketController extends BaseController
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
    public function store(StoreTicketRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }

    public function createTicket(Request $request)
    {
        try {
            // Get the user
            $user = User::where('id', $request->user_id)->first();
            $email = $request->email;
            $firstName = $request->first_name;
            $lastName = $request->last_name;
            $address = $request->address;
            $issue = $request->issue;
            $description = $request->description;

            if (!$user) {
                return $this->returnError('User not found.', [], 404);
            }

            if ($request->has('first_name') && empty($request->input('first_name'))) {
                return $this->returnError('Validation Error', 'First name field can not be empty');
            }

            if ($request->has('last_name') && empty($request->input('last_name'))) {
                return $this->returnError('Validation Error', 'Last name field can not be empty');
            }

            if ($request->has('email') && empty($request->input('email'))) {
                return $this->returnError('Validation Error', 'Email field can not be empty');
            }

            if ($request->has('address') && empty($request->input('address'))) {
                return $this->returnError('Validation Error', 'Address field can not be empty');
            }
            
            if ($request->has('issue') && empty($request->input('issue'))) {
                return $this->returnError('Validation Error', 'Issue field can not be empty');
            }

            if ($request->has('description') && empty($request->input('description'))) {
                return $this->returnError('Validation Error', 'Description field can not be empty');
            }

            // $ticket = Ticket::create([
            //     'user_id' => $request->user_id,
            //     'first_name' => $request->first_name,
            //     'last_name' => $request->last_name,
            //     'email' => $request->email,
            //     'address' => $request->address,
            //     'issue' => $request->issue,
            //     'description' => $request->description,
            // ]);

            $this->SupportTicketMail($email, $firstName, $lastName, $address, $issue, $description);

            return $this->returnSuccess('FTTH ticket raised successfully.', 200);
        } catch (\Throwable $th) {
            return $this->returnError('Error', $th->getMessage(), 500);
        }
    }
}
