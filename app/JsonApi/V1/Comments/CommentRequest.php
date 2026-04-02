<?php

namespace App\JsonApi\V1\Comments;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class CommentRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:280'],
            'post' => ['required', 'array', JsonApiRule::toOne()],
        ];
    }
}
