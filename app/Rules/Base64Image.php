<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class Base64Image implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $value);

        try {
            // Try to create an image instance using Intervention Image
            $manager = new ImageManager(new Driver);

            // Read the image data using the Base64ImageDecoder
            $manager->read($imageData, Base64ImageDecoder::class);
            // If the image is created successfully, it is valid

        } catch (\Exception $e) {
            Log::error('Profile Image validation: '.$e->getMessage());
            // If an exception is thrown, it means the data is not a valid image
            $fail('The :attribute must be a valid image.');
        }
    }
}
