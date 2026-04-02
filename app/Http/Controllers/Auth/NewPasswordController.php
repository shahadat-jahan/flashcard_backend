<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\NewPasswordRequest;
use App\Response\JsonApiResponse;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class NewPasswordController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(NewPasswordRequest $request): MetaResponse|ErrorResponse
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise, we will parse the error and return the response.
        $result = $this->service->setNewPassword($request);

        return JsonApiResponse::handle($result);
    }

    public function setPassword(NewPasswordRequest $request): MetaResponse|ErrorResponse
    {
        $result = $this->service->setPassword($request);

        return JsonApiResponse::handle($result);
    }
}
