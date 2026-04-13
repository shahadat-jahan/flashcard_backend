<?php

namespace Tests\Unit;

use Tests\TestCase;

class UrlHelperTest extends TestCase
{
    public function test_frontend_url_builds_path_and_query_string(): void
    {
        config()->set('app.frontend_url', 'https://frontend.example.com/');

        $url = frontendUrl('/reset-password', [
            'token' => 'abc123',
            'email' => 'user@example.com',
        ]);

        $this->assertSame(
            'https://frontend.example.com/reset-password?token=abc123&email=user%40example.com',
            $url
        );
    }

    public function test_frontend_url_returns_base_url_when_no_path_or_query_are_provided(): void
    {
        config()->set('app.frontend_url', 'https://frontend.example.com');

        $this->assertSame('https://frontend.example.com/', frontendUrl());
    }
}
