<?php

namespace App\JsonApi\V1\Posts;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PostRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'content' => [
                'nullable',
                'required_unless:action_type,'.PostStatus::DRAFT->value,
                'string', ...$this->getContentLimitRule(),
            ],
            'action_type' => ['required', 'integer', Rule::enum(PostStatus::class)],
            'note' => ['nullable', 'string'],
            'topics' => ['nullable', 'array', JsonApiRule::toMany()],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'author' => ['required', 'array', JsonApiRule::toOne()],
            'unused_image_urls' => ['nullable', 'array'],
            'post_type' => ['required', 'integer', Rule::enum(PostType::class)],
        ];

        if (request()->method() === 'PATCH' || request()->method() === 'PUT') {
            $rules = [
                'title' => ['required', 'string', 'min:10', 'max:255'],
                'content' => [
                    'nullable',
                    'required_unless:action_type,'.PostStatus::DRAFT->value,
                    'string', ...$this->getContentLimitRule(),
                ],
                'action_type' => ['required', 'integer', Rule::enum(PostStatus::class)],
                'note' => ['nullable', 'string'],
                'topics' => ['nullable', 'array', JsonApiRule::toMany()],
                'unused_image_urls' => ['nullable', 'array'],
                'post_type' => ['required', 'integer', Rule::enum(PostType::class)],
            ];

            if (auth()->check() && auth()->user()->isAdmin()) {
                // Fetch the current post from the route (assuming route model binding)
                $post = $this->route('post');
                $rules['due_date'] = ['nullable', 'date'];

                if ($post && $post->status !== PostStatus::APPROVED) {
                    $rules['due_date'] = ['after_or_equal:today'];
                }
            }
        }

        // Restrict action_type for non-admin users
        if (auth()->check() && ! auth()->user()->isAdmin()) {
            $rules['action_type'] = [Rule::in([PostStatus::DRAFT->value, PostStatus::PENDING->value])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'action_type.in' => 'The action type must be either Draft or Pending for non-admin users.',
            'due_date.after_or_equal' => 'The due date must be today or a future date unless the post is approved.',
        ];
    }

    private function getContentLimitRule(): array
    {
        $rules = ['min:60'];

        $type = $this->input('data.attributes.post_type');
        if ($type == PostType::FLASHCARD->value) {
            $rules[] = 'max:500';
        } elseif ($type == PostType::TWEET->value) {
            $rules[] = 'max:280';
        }

        return $rules;
    }
}
