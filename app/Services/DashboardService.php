<?php

namespace App\Services;

use App\Enums\ServiceResultType as ResultType;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class DashboardService extends Service
{
    public function __construct(private readonly PostRepository $postRepository)
    {
        parent::__construct();
    }

    /**
     * Get topic list
     */
    public function postStatistics(array $queryParams = []): ServiceResult
    {
        try {
            $user = Auth::user();
            $count = $this->postRepository->statusWisePostCount($user, $queryParams);

            $this->result->setData($count, ResultType::META);
        } catch (Exception $exception) {
            $message = 'Post status count Failed';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }
}
