<?php

namespace App\JsonApi\V1\Profile;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ProfileTopicRequest extends ResourceRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        return [
            'topics' => ['required', 'array', JsonApiRule::toMany()],
        ];
    }
}
