<?php

namespace App\JsonApi\V1\Posts;

use App\Models\Post;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property Post $resource
 */
class PostResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param  Request|null  $request
     */
    public function attributes($request): iterable
    {
        return [
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'post_type' => $this->resource->type,
            'content' => $this->resource->content,
            'note' => $this->resource->task->note ?: null,
            'status' => $this->resource->status,
            'approved_at' => $this->resource->approved_at,
            'deleted_at' => $this->resource->deleted_at,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'submitted_at' => $this->resource->task->submitted_at ?: null,
            'due_date' => $this->resource->task->due_date ?: null,
            'total_like' => $this->resource->total_like ?: 0,
        ];
    }

    /**
     * Get the resource's relationships.
     *
     * @param  Request|null  $request
     */
    public function relationships($request): iterable
    {
        return [
            $this->relation('author')->withoutLinks(),
            $this->relation('topics')->withoutLinks(),
            $this->relation('likes')->withoutLinks(),
            $this->relation('approvedBy')->withoutLinks(),
            $this->relation('createdBy')->withoutLinks(),
        ];
    }
}
