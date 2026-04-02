<?php

namespace App\JsonApi\V1\Profile;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class UpdatePasswordRequest extends ResourceRequest
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
            'current_password' => [
                Rule::requiredIf(request()->user()->hasPassword()),
                function ($attribute, $value, $fail) {
                    if (request()->user()->hasPassword() && ! Hash::check($value, request()->user()->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }
}
