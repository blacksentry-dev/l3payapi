<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use App\Http\Controllers\API\BaseController as BaseController;

class FeedbackController extends BaseController
{
    /**
     * Display a listing of the resource.
     */

     /**
     * @OA\Post(
     *     path="/api/feedback",
     *     operationId="submitFeedback",
     *     tags={"Feedback"},
     *     summary="Submit Feedback and Rating",
     *     description="Allow users to provide feedback and ratings on their experience.",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "feedback", "rating"},
     *             @OA\Property(property="user_id", type="string"),
     *             @OA\Property(property="feedback", type="string"),
     *             @OA\Property(property="rating", type="number", format="float"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Feedback submitted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Feedback submitted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=404, description="User not found"),
     * )
     */
    public function submitFeedback(Request $request) :JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'feedback' => 'required|string',
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user = User::find($request->input('user_id'));

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $feedback = new Feedback();
        $feedback->user_id = $user->id;
        $feedback->feedback = $request->input('feedback');
        $feedback->rating = $request->input('rating');
        $feedback->save();
        
        return $this->sendResponse(['status' => 'success', 'feedback' => $feedback], 'Feedback submitted successfully.');
        
    }

    /**
     * @OA\Get(
     *     path="/api/feedback/user/{user_id}",
     *     operationId="getUserFeedback",
     *     tags={"Feedback"},
     *     summary="Get User Feedback",
     *     description="Get feedback and rating for a specific user.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User feedback retrieved successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function getUserFeedback(Request $request): JsonResponse
    {
        $user_id = 40; // Hardcoded user ID
    
        // Retrieve feedback for the user
        $feedback = Feedback::where('user_id', $user_id)->first();
    
        if (!$feedback) {
            return $this->sendError('User feedback not found.', [], 404);
        }

        $data = [
            'user_id' => $feedback->user_id,
            'feedback' => $feedback->feedback,
            'rating' => $feedback->rating,
        ];

        return $this->sendResponse(['status' => 'success', 'data' => $data], 'User feedback retrived successfully.');

    }

    /**
     * @OA\Get(
     *     path="/api/rating/average",
     *     operationId="getAverageRating",
     *     tags={"Feedback"},
     *     summary="Get Average Rating",
     *     description="Get the average rating for all feedback.",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Average rating retrieved successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function getAverageFeedback(Request $request): JsonResponse
    {
        $averageRating = Feedback::avg('rating');
        // dd($averageRating);
        
        $data = [
            'averageRating' => round($averageRating, 1),
        ];

        return $this->sendResponse(['status' => 'success', 'data' => $data], 'This is the average of all L3pay app ratings.');

    }







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
    public function store(StoreFeedbackRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeedbackRequest $request, Feedback $feedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        //
    }
}
