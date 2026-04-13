<?php

namespace Tests\Feature;

use App\Enums\ServiceResultType;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\ServiceResult;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    public function test_dashboard_endpoint_requires_authentication(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/vnd.api+json',
        ])->get(route('v1.dashboard.index'));

        $response->assertUnauthorized();
    }

    public function test_dashboard_endpoint_requires_a_verified_email_address(): void
    {
        $user = User::factory()->unverified()->make();

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/vnd.api+json',
        ])->get(route('v1.dashboard.index'));

        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson([
                'errors' => [
                    [
                        'status' => (string) Response::HTTP_CONFLICT,
                        'detail' => 'Your email address is not verified.',
                    ],
                ],
            ]);
    }

    public function test_dashboard_endpoint_requires_the_json_api_accept_header(): void
    {
        $user = User::factory()->make();

        Sanctum::actingAs($user);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get(route('v1.dashboard.index'));

        $response->assertStatus(Response::HTTP_UNSUPPORTED_MEDIA_TYPE)
            ->assertJson([
                'errors' => [
                    [
                        'status' => (string) Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                        'detail' => 'Accept header must be application/vnd.api+json',
                    ],
                ],
            ]);
    }

    public function test_dashboard_endpoint_returns_meta_statistics_for_verified_users(): void
    {
        $user = User::factory()->make();
        $query = ['filter' => ['post_type' => 2]];
        $meta = [
            'draft' => 1,
            'pending' => 2,
            'approved' => 3,
            'declined' => 4,
            'total' => 10,
        ];

        Sanctum::actingAs($user);

        $result = new ServiceResult();
        $result->setData($meta, ServiceResultType::META);

        $service = Mockery::mock(DashboardService::class);
        $service->shouldReceive('postStatistics')
            ->once()
            ->with($query)
            ->andReturn($result);

        $this->app->instance(DashboardService::class, $service);

        $response = $this->withHeaders([
            'Accept' => 'application/vnd.api+json',
        ])->get(route('v1.dashboard.index', $query));

        $response->assertOk()
            ->assertJson([
                'meta' => $meta,
            ]);
    }
}
