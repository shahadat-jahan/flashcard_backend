<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Response\JsonApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service)
    {
        //
    }

    public function index(Request $request): MetaResponse|ErrorResponse
    {
        $queryParams = $request->query();
        $result = $this->service->postStatistics($queryParams);

        return JsonApiResponse::handle($result);
    }
}
