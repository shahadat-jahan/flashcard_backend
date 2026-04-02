<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Send a new email verification notification.
     *
     * @throws ValidationException
     */
    public function store(Request $request): MetaResponse|ErrorResponse
    {
        $result = $this->service->sendEmailVerifyLink($request);

        return JsonApiResponse::handle($result);
    }
}
