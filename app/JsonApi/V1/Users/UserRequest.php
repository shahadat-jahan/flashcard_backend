<?php

namespace App\JsonApi\V1\Users;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', Password::defaults()],
            'designation' => ['required', 'array', JsonApiRule::toOne()],
            'role' => ['required', 'integer', Rule::enum(UserRole::class)],
        ];

        if (request()->method() === 'PATCH' || request()->method() === 'PUT') {
            $rules = [
                'first_name' => ['nullable', 'string', 'max:150'],
                'last_name' => ['nullable', 'string', 'max:150'],
                'designation' => ['nullable', 'array', JsonApiRule::toOne()],
                'status' => ['nullable', 'integer', Rule::enum(UserStatus::class)],
                'role' => ['nullable', 'integer', Rule::enum(UserRole::class)],
                'password' => ['nullable', 'confirmed', Password::defaults()],
            ];
        }

        return $rules;
    }
}
