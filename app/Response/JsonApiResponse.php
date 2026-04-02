<?php

namespace App\Response;

use App\Enums\ServiceResultType as ResultType;
use App\Services\ServiceResult;
use Illuminate\Http\JsonResponse;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonApiResponse
{
    public static function handle(ServiceResult $result): DataResponse|JsonResponse|MetaResponse|ErrorResponse
    {
        return match ($result->type) {
            ResultType::DATA => self::data($result->data),
            ResultType::META => self::meta($result->data),
            ResultType::JSON => self::json($result->data),
            ResultType::DELETE => self::delete(),
            ResultType::ERROR => self::error($result->error->message ?? 'Unknown error', $result->error->code),
            default => self::error('Something went wrong!', Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }

    public static function data(mixed $data): DataResponse
    {
        return new DataResponse($data);
    }

    public static function meta(mixed $data): MetaResponse
    {
        return new MetaResponse($data);
    }

    public static function json(array $data): JsonResponse
    {
        return response()->json($data, Response::HTTP_OK);
    }

    public static function delete(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public static function error(string $message, int $code = 400): ErrorResponse
    {
        $error = new Error;
        $error->setStatus($code ?? Response::HTTP_BAD_REQUEST);
        $error->setTitle(Response::$statusTexts[$code ?? Response::HTTP_BAD_REQUEST]);
        $error->setDetail($message);

        return new ErrorResponse([$error]);
    }
}
