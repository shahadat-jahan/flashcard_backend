<?php

namespace App\JsonApi\V1\Users;

use App\Enums\UserStatus;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserCollectionQuery extends ResourceQuery
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
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'filter.status' => Rule::enum(UserStatus::class),
            'filter.created_date_form' => 'date',
            'filter.created_date_to' => 'date',
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
        ];
    }
}
