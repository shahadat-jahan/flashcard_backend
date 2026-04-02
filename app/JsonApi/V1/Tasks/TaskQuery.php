<?php

namespace App\JsonApi\V1\Tasks;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class TaskQuery extends ResourceQuery
{
    /**
     * Get the validation rules that apply to the request query parameters.
     */
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => JsonApiRule::notSupported(),
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => JsonApiRule::notSupported(),
            'sort' => JsonApiRule::notSupported(),
            'withCount' => JsonApiRule::notSupported(),
        ];
    }
}
