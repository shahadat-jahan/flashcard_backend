<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Topics\TopicRequest;
use App\Models\Topic;
use App\Response\JsonApiResponse;
use App\Services\TopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class TopicController extends Controller
{
    public function __construct(private readonly TopicService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): DataResponse|ErrorResponse
    {
        $result = $this->service->getTopics($request);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified topic.
     */
    public function show(Topic $topic): DataResponse|ErrorResponse
    {
        return JsonApiResponse::data($topic);
    }

    /**
     * Store a newly created topic in storage.
     */
    public function store(TopicRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->createTopic($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the user posts.
     */
    public function posts(Topic $topic): DataResponse|ErrorResponse
    {
        $result = $this->service->getTopicPosts($topic);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the specified topic in storage.
     */
    public function update(TopicRequest $request, Topic $topic): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateTopic($topic, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Remove the specified topic from storage.
     */
    public function destroy(Topic $topic): JsonResponse|ErrorResponse
    {
        $result = $this->service->deleteTopic($topic);

        return JsonApiResponse::handle($result);
    }
}
