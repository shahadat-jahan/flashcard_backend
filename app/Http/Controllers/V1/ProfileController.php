<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Profile\ProfileRequest;
use App\JsonApi\V1\Profile\ProfileTopicRequest;
use App\JsonApi\V1\Profile\UpdatePasswordRequest;
use App\Response\JsonApiResponse;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    /**
     * Display the authenticated user's profile.
     */
    public function index(): DataResponse
    {
        $result = $this->service->getUser(Auth::user());

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function store(ProfileRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUser($request->user(), $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUserPassword($request->user(), $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Attach topics to the authenticated user's profile.
     */
    public function attachTopics(ProfileTopicRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUser($request->user(), $data);

        return JsonApiResponse::handle($result);
    }
}
