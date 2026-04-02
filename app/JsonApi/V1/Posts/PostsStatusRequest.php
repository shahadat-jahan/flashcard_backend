<?php

namespace App\JsonApi\V1\Posts;

use App\Enums\PostStatus;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class PostsStatusRequest extends ResourceRequest
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
            'status' => ['nullable', 'integer', Rule::enum(PostStatus::class)],
            'decline_reason' => ['nullable', 'string', 'min:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'decline_reason.required_if' => 'Please provide a reason for declining the post.',
            'decline_reason.min' => 'The decline reason must be at least :min characters long.',
        ];
    }
}
