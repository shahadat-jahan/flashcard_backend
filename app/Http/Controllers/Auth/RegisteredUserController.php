<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Handle an incoming registration request.
     */
    public function store(UserRegistrationRequest $request): MetaResponse|ErrorResponse
    {
        $data = $request->validated();
        $result = $this->service->registerUser($data);

        return JsonApiResponse::handle($result);
    }
}
