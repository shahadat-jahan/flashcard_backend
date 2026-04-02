<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class PasswordResetLinkController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ForgotPasswordRequest $request): MetaResponse|ErrorResponse
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $result = $this->service->sendPasswordResetLink($request);

        return JsonApiResponse::handle($result);
    }
}
