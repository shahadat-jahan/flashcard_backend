<?php

namespace App\Services;

use App\Enums\ServiceResultType as ResultType;
use Symfony\Component\HttpFoundation\Response;

class ServiceResult
{
    public ResultType $type;

    public mixed $data;

    public ?object $error;

    public function __construct()
    {
        $this->type = ResultType::DATA;
        $this->data = null;
        $this->error = null;
    }

    public function setType(ResultType $type): void
    {
        $this->type = $type;
    }

    public function setData(mixed $data, ?ResultType $type = null): void
    {
        $this->type = $type ?? ResultType::DATA;
        $this->data = $data;
    }

    public function setDeleted(): void
    {
        $this->type = ResultType::DELETE;
        $this->data = null;
    }

    public function setError(string $message, ?int $code = null): void
    {
        $this->type = ResultType::ERROR;
        $this->error->code = $code ?? Response::HTTP_BAD_REQUEST;
        $this->error->message = $message;
    }
}
