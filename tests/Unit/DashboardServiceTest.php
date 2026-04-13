<?php

namespace Tests\Unit;

use App\Enums\ServiceResultType;
use App\Repositories\PostRepository;
use App\Services\DashboardService;
use App\Models\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_post_statistics_returns_meta_data_from_repository(): void
    {
        $user = new User();
        $queryParams = ['range' => 'weekly'];
        $stats = ['approved' => 4, 'pending' => 2];

        $repository = Mockery::mock(PostRepository::class);
        $repository->expects('statusWisePostCount')
            ->with($user, $queryParams)
            ->andReturns($stats);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $service = new DashboardService($repository);
        $result = $service->postStatistics($queryParams);

        $this->assertSame(ServiceResultType::META, $result->type);
        $this->assertSame($stats, $result->data);
        $this->assertNull($result->error);
    }

    public function test_post_statistics_returns_error_result_when_repository_fails(): void
    {
        $user = new User();
        $queryParams = ['range' => 'monthly'];
        $repositoryException = new Exception('Repository failure');

        $repository = Mockery::mock(PostRepository::class);
        $repository->expects('statusWisePostCount')
            ->with($user, $queryParams)
            ->andThrow($repositoryException);

        Auth::shouldReceive('user')->once()->andReturn($user);
        Log::shouldReceive('error')
            ->once()
            ->with('Post status count Failed', ['exception' => 'Repository failure']);

        $service = new DashboardService($repository);
        $result = $service->postStatistics($queryParams);

        $this->assertSame(ServiceResultType::ERROR, $result->type);
        $this->assertNull($result->data);
        $this->assertNotNull($result->error);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->error->code);
        $this->assertSame('Post status count Failed', $result->error->message);
    }
}
