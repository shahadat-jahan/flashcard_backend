<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Posts\BulkPostsStatusUpdateRequest;
use App\JsonApi\V1\Posts\PostCollectionQuery;
use App\JsonApi\V1\Posts\PostImageRequest;
use App\JsonApi\V1\Posts\PostQuery;
use App\JsonApi\V1\Posts\PostRequest;
use App\JsonApi\V1\Posts\PostsStatusRequest;
use App\Models\Post;
use App\Response\JsonApiResponse;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class PostController extends Controller
{
    public function __construct(private readonly PostService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PostCollectionQuery $request): DataResponse|ErrorResponse
    {
        $queryParams = $request->all();
        $result = $this->service->getPosts($queryParams);

        return JsonApiResponse::handle($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->createPost($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(PostQuery $request, Post $post): DataResponse
    {
        return JsonApiResponse::data($post);
    }

    /**
     * Update the specified Post
     */
    public function update(PostRequest $request, Post $post): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updatePost($post, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the specified Post
     */
    public function updateStatus(PostsStatusRequest $request, Post $post): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updatePostStatus($post, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Bulk Status Update for Post
     */
    public function bulkStatusUpdate(BulkPostsStatusUpdateRequest $request): MetaResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->bulkUpdatePostStatus($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): ErrorResponse|JsonResponse
    {
        $result = $this->service->deletePost($post);

        return JsonApiResponse::handle($result);
    }

    public function imageUpload(PostImageRequest $request): JsonResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->postImage($data);

        return JsonApiResponse::handle($result);
    }

    public function imageDelete(Request $request): MetaResponse|ErrorResponse
    {
        $data = $request->input('data.attributes');
        $result = $this->service->postImageDelete($data);

        return JsonApiResponse::handle($result);
    }

    public function comments(Post $post): DataResponse|ErrorResponse
    {
        $result = $this->service->getCommentsByPost($post);

        return JsonApiResponse::handle($result);
    }

    public function like(Post $post): JsonResponse|ErrorResponse
    {
        $result = $this->service->likePost($post);

        return JsonApiResponse::handle($result);
    }

    public function unlike(Post $post): JsonResponse|ErrorResponse
    {
        $result = $this->service->unlikePost($post);

        return JsonApiResponse::handle($result);
    }
}
