<?php

namespace Tests\Unit;

use App\Enums\ServiceResultType;
use App\Services\ServiceResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ServiceResultTest extends TestCase
{
    public function test_set_error_populates_error_payload_with_custom_code(): void
    {
        $result = new ServiceResult();

        $result->setError('Validation failed.', Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertSame(ServiceResultType::ERROR, $result->type);
        $this->assertNotNull($result->error);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $result->error->code);
        $this->assertSame('Validation failed.', $result->error->message);
    }

    public function test_set_error_uses_bad_request_when_code_is_not_provided(): void
    {
        $result = new ServiceResult();

        $result->setError('Something went wrong.');

        $this->assertSame(ServiceResultType::ERROR, $result->type);
        $this->assertNotNull($result->error);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->error->code);
        $this->assertSame('Something went wrong.', $result->error->message);
    }
}
