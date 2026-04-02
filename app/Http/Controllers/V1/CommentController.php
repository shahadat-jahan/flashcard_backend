<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Comments\CommentRequest;
use App\Models\Comment;
use App\Response\JsonApiResponse;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): DataResponse|ErrorResponse
    {
        $result = $this->service->getComments($request);

        return JsonApiResponse::handle($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $data['user_id'] = $user->id;
        $result = $this->service->createComment($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment): DataResponse|ErrorResponse
    {
        return JsonApiResponse::data($comment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommentRequest $request, Comment $comment): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $data['user_id'] = $user->id;
        $result = $this->service->updateComment($comment, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);
        $result = $this->service->deleteComment($comment);

        return JsonApiResponse::handle($result);
    }
}
