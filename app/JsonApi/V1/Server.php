<?php

namespace App\JsonApi\V1;

use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     */
    protected string $baseUri = '/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     */
    public function serving(): void
    {
        Auth::shouldUse('sanctum');
    }

    /**
     * Get the server's list of schemas.
     */
    protected function allSchemas(): array
    {
        return [
            Users\UserSchema::class,
            Topics\TopicSchema::class,
            Posts\PostSchema::class,
            Profile\ProfileSchema::class,
            Tasks\TaskSchema::class,
            Notifications\NotificationSchema::class,
            Dashboards\DashboardSchema::class,
            Designations\DesignationSchema::class,
            Comments\CommentSchema::class,
        ];
    }
}
