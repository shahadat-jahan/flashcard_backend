<?php

/**
 * Generate a frontend URL with the given path and query parameters.
 */
function frontendUrl(string $path = '', array $queryParams = []): string
{
    $baseUrl = config('app.frontend_url');

    $url = rtrim($baseUrl, '/').'/'.ltrim($path, '/');

    if (! empty($queryParams)) {
        $url .= '?'.http_build_query($queryParams);
    }

    return $url;
}
