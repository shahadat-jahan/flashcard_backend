<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class VerifyEmailController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @throws ValidationException
     */
    public function __invoke(EmailVerificationRequest $request): MetaResponse|ErrorResponse
    {
        $result = $this->service->verifyEmail($request);

        return JsonApiResponse::handle($result);
    }
}
