<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Response\JsonApiResponse;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): DataResponse|ErrorResponse
    {
        $result = $this->service->getNotifications($request);

        return JsonApiResponse::handle($result);
    }

    public function read(DatabaseNotification $notification): DataResponse|ErrorResponse
    {
        $result = $this->service->readNotification($notification);

        return JsonApiResponse::handle($result);
    }

    public function readAll(): MetaResponse|ErrorResponse
    {
        $result = $this->service->readAllNotifications();

        return JsonApiResponse::handle($result);
    }
}
