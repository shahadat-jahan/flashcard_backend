<?php

namespace App\JsonApi\V1\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class BulkPostsStatusUpdateRequest extends ResourceRequest
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
            'status' => ['required', 'integer', Rule::enum(PostStatus::class)],
            'post_ids' => ['required', 'array'],
            'post_ids.*' => ['required', 'integer', Rule::exists(Post::class, 'id')],
            'decline_reason' => ['required_if:status,'.PostStatus::DECLINED->value, 'string', 'min:5'],
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
