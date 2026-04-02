<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
        //
    }

    /**
     * Handle an incoming authentication request.
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request): JsonResponse|ErrorResponse
    {
        $result = $this->service->userLogin($request);

        return JsonApiResponse::handle($result);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): MetaResponse|ErrorResponse
    {
        $result = $this->service->userLogout($request);

        return JsonApiResponse::handle($result);
    }

    public function refreshToken(Request $request): JsonResponse|ErrorResponse
    {
        $result = $this->service->refreshToken($request);

        return JsonApiResponse::handle($result);
    }
}
