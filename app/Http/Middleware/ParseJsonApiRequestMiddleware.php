<?php

namespace App\Http\Middleware;

use App\Enums\AuthRouteNames;
use App\Response\JsonApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LaravelJsonApi\Core\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Response;

class ParseJsonApiRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next): Response|ErrorResponse
    {
        if ($request->header('Content-Type') === 'application/vnd.api+json') {
            if (! $request->isJson()) {
                return JsonApiResponse::error('Expecting JSON to decode.', Response::HTTP_BAD_REQUEST);
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return JsonApiResponse::error('Invalid JSON syntax/format.', Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($request->getContent(), true);

            if (! empty($data['data']['type']) && isset($data['data']['attributes'])) {
                $this->validateResourceType($request, $data['data']['type']);

                $request->merge($data['data']['attributes']);
                $request->offsetUnset('data');
            } else {
                return JsonApiResponse::error('Invalid JSON:API request structure.', Response::HTTP_BAD_REQUEST);
            }
        }

        return $next($request);
    }

    /**
     * Validate Request Resource Type
     * NOTE: this is used for Validate JSON:API request format.
     * https://laraveljsonapi.io/docs/3.0/tutorial/05-creating-resources.html#create-requests
     *
     * @throws ValidationException
     */
    private function validateResourceType(Request $request, string $resourceType): void
    {
        $resourceTypeFromRoute = AuthRouteNames::tryFrom($request->route()->getName())?->getResourceType();

        if (! $resourceTypeFromRoute || $resourceType !== $resourceTypeFromRoute) {
            throw ValidationException::withMessages([
                'data.type' => 'Resource type '.$resourceType.' is not supported by this endpoint.',
            ])->status(Response::HTTP_CONFLICT);
        }
    }
}
