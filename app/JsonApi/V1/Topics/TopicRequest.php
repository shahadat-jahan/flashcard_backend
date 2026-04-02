<?php

namespace App\JsonApi\V1\Topics;

use App\Enums\TopicStatus;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class TopicRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        $topic = $this->model();
        $uniqueTopicName = Rule::unique('topics', 'name');
        if ($topic) {
            $uniqueTopicName->ignoreModel($topic);
        }

        return [
            'name' => ['required', 'string', 'max:35', $uniqueTopicName],
            'status' => ['nullable', 'integer', Rule::enum(TopicStatus::class)],
        ];
    }
}
