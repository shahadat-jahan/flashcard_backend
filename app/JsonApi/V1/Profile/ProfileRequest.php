<?php

namespace App\JsonApi\V1\Profile;

use App\Rules\Base64Image;
use App\Rules\ImageExtension;
use App\Rules\ImageSize;
use App\Rules\SquareImage;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ProfileRequest extends ResourceRequest
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
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'avatar' => ['nullable', new Base64Image, new SquareImage, new ImageExtension, new ImageSize(1)],
        ];
    }
}
