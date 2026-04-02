<?php

namespace App\Http\Middleware;

use App\Response\JsonApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcceptHeaderIsJson
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Accept') !== 'application/vnd.api+json') {
            return JsonApiResponse::error('Accept header must be application/vnd.api+json', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        if ($request->header('Content-Type') && $request->header('Content-Type') !== 'application/vnd.api+json') {
            return JsonApiResponse::error('Content-Type must be application/vnd.api+json', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        return $next($request);
    }
}
