<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Users\UpdatePasswordRequest;
use App\JsonApi\V1\Users\UserCollectionQuery;
use App\JsonApi\V1\Users\UserQuery;
use App\JsonApi\V1\Users\UserRequest;
use App\Models\User;
use App\Response\JsonApiResponse;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(UserCollectionQuery $request): DataResponse|ErrorResponse
    {
        $queryParams = $request->all();
        $result = $this->service->getUsers($queryParams);

        return JsonApiResponse::handle($result);
    }

    /**
     * Store a newly created resource.
     */
    public function store(UserRequest $request): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->createUser($data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserQuery $request, User $user): DataResponse
    {
        $result = $this->service->getUser($user);

        return JsonApiResponse::handle($result);
    }

    /**
     * Display the user posts.
     */
    public function posts(User $user): DataResponse|ErrorResponse
    {
        $result = $this->service->getUserPosts($user);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUser($user, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update User Password
     */
    public function updatePassword(UpdatePasswordRequest $request, User $user): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUserPassword($user, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update User Status
     */
    public function updateStatus(UserRequest $request, User $user): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUserStatus($user, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Update User Role
     */
    public function updateRole(UserRequest $request, User $user): DataResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->updateUserRole($user, $data);

        return JsonApiResponse::handle($result);
    }

    /**
     * Delete User
     */
    public function destroy(User $user): JsonResponse|ErrorResponse
    {
        $result = $this->service->deleteUser($user);

        return JsonApiResponse::handle($result);
    }

    public function follow(User $user): JsonResponse|ErrorResponse
    {
        $result = $this->service->followUser($user);

        return JsonApiResponse::handle($result);
    }

    public function unfollow(User $user): JsonResponse|ErrorResponse
    {
        $result = $this->service->unfollowUser($user);

        return JsonApiResponse::handle($result);
    }

    public function sendInvitation(User $user): JsonResponse|ErrorResponse
    {
        $result = $this->service->inviteNewUser($user);

        return JsonApiResponse::handle($result);
    }
}
