<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Tasks\TaskCollectionQuery;
use App\JsonApi\V1\Tasks\TaskQuery;
use App\JsonApi\V1\Tasks\TaskRequest;
use App\Models\Task;
use App\Response\JsonApiResponse;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(TaskCollectionQuery $request): DataResponse|ErrorResponse
    {
        $queryParams = $request->all();
        $result = $this->service->getTasks($queryParams);

        return JsonApiResponse::handle($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->createTask($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskQuery $request, Task $task): DataResponse
    {
        return JsonApiResponse::data($task);
    }

    /**
     * Update the specified Post
     */
    public function update(TaskRequest $request, Task $task): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateTask($task, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): ErrorResponse|JsonResponse
    {
        $result = $this->service->deleteTask($task);

        return JsonApiResponse::handle($result);
    }
}
