<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ImageSize implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    protected int $maxSize;

    public function __construct($maxSize = 1)
    {
        $this->maxSize = $maxSize;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Decode the Base64 image data
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $value));

        // Calculate the image size in MB
        $imageSize = strlen($imageData) / (1024 * 1024); // Convert bytes to MB

        // Check if the image size exceeds the maximum allowed size
        if ($imageSize > $this->maxSize) {
            $fail('The :attribute size must not exceed '.$this->maxSize.'MB.');
        }
    }
}
