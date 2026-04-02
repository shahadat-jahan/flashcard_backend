<?php

namespace App\JsonApi\V1\Posts;

use App\Rules\Base64Image;
use App\Rules\ImageExtension;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class PostImageRequest extends ResourceRequest
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
            'image' => ['required', new Base64Image, new ImageExtension],
        ];
    }
}
