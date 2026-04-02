<?php

namespace App\JsonApi\V1\Notifications;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use JsonException;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property DatabaseNotification $resource
 */
class NotificationResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param  Request|null  $request
     *
     * @throws JsonException
     */
    public function attributes($request): iterable
    {
        $data = [
            'id' => $this->resource->id,
            'readAt' => $this->resource->read_at,
            'data' => $this->resource->data,
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
        ];

        return $data;
    }

    public function relationships($request): iterable
    {
        return [
            $this->relation('notifiable')->withoutLinks(),
        ];
    }
}
