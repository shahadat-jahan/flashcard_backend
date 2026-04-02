<?php

namespace App\JsonApi\V1\Tasks;

use App\Models\Task;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property Task $resource
 */
class TaskResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param  Request|null  $request
     */
    public function attributes($request): iterable
    {
        return [
            'subject' => $this->resource->subject,
            'note' => $this->resource->note,
            'status' => $this->resource->status,
            'due_date' => $this->resource->due_date,
            'notify_before_deadline' => $this->resource->notify_before_deadline,
            'publishedAt' => $this->resource->published_at,
            'submittedAt' => $this->resource->submitted_at,
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
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
            $this->relation('assign_to')->withoutLinks(),
            $this->relation('assign_by')->withoutLinks(),
            $this->relation('topics')->withoutLinks(),
            $this->relation('publishedBy')->withoutLinks(),
            $this->relation('post')->withoutLinks(),
        ];
    }
}
