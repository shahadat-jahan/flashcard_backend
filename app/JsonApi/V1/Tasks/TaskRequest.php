<?php

namespace App\JsonApi\V1\Tasks;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class TaskRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'notify_before_deadline' => ['nullable', 'boolean'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'topics' => ['required', 'array', JsonApiRule::toMany()],
            'assign_to' => ['required', 'array', JsonApiRule::toOne()],
        ];

        if (request()->method() === 'PATCH' || request()->method() === 'PUT') {
            $rules = [
                'subject' => ['nullable', 'string', 'max:255'],
                'note' => ['nullable', 'string'],
                'notify_before_deadline' => ['nullable', 'boolean'],
                'due_date' => ['nullable', 'date', 'after_or_equal:today'],
                'topics' => ['nullable', 'array', JsonApiRule::toMany()],
                'assign_to' => ['nullable', 'array', JsonApiRule::toOne()],
            ];
        }

        return $rules;
    }
}
