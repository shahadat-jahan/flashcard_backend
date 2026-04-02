<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;

class ImageExtension implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Decode the Base64-encoded image data
        $imageData = base64_decode($value);

        try {
            $metadata = explode(';base64,', $value);
            $extension = explode('/', $metadata[0])[1];
            // Check if the extension is valid
            if (! in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $fail('The :attribute must be a JPG, JPEG, or PNG image.');
            }
        } catch (\Exception $e) {
            Log::error('Profile Image validation: ' . $e->getMessage());
            // Fail the validation if the image cannot be processed
            $fail('The :attribute is not a valid image.');
        }
    }
}
