<?php

namespace App\JsonApi\V1\Tasks;

use App\Enums\TaskStatus;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class TaskCollectionQuery extends ResourceQuery
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
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'filter.status' => Rule::enum(TaskStatus::class),
            'filter.created_date_form' => 'date',
            'filter.created_date_to' => 'date',
            'filter.due_date_form' => 'date',
            'filter.due_date_to' => 'date',
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'page.number' => [
                'integer',
                'min:1',
            ],
            'page.size' => [
                'integer',
                'min:1',
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
        ];
    }
}
