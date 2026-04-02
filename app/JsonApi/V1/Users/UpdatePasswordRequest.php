<?php

namespace App\JsonApi\V1\Users;

use Illuminate\Validation\Rules\Password;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class UpdatePasswordRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        return [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }
}
