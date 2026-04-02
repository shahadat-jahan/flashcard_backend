<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Designations\DesignationRequest;
use App\Models\Designation;
use App\Response\JsonApiResponse;
use App\Services\DesignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class DesignationController extends Controller
{
    public function __construct(private readonly DesignationService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): DataResponse|ErrorResponse
    {
        $result = $this->service->getDesignations($request);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified designation.
     */
    public function show(Designation $designation): DataResponse
    {
        return JsonApiResponse::data($designation);
    }

    /**
     * Store a newly created designation in storage.
     */
    public function store(DesignationRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->createDesignation($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the user posts.
     */
    public function users(Designation $designation): DataResponse|ErrorResponse
    {
        $result = $this->service->getUsersByDesignation($designation);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the specified designation in storage.
     */
    public function update(DesignationRequest $request, Designation $designation): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateDesignation($designation, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Remove the specified designation from storage.
     */
    public function destroy(Designation $designation): JsonResponse|ErrorResponse
    {
        $result = $this->service->deleteDesignation($designation);

        return JsonApiResponse::handle($result);
    }
}
