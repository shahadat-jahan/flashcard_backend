<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

//use Intervention\Image\Image;

class SquareImage implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Decode the Base64-encoded image data
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $value));
        try {
            // create a new manager instance with a desired driver
            $manager = new ImageManager(new Driver);
            $image = $manager->read($imageData);
            // Check if the image is square
            if ($image->width() !== $image->height()) {
                $fail('The :attribute must be square (equal width and height).');
            } elseif ($image->width() > 300 || $image->height() > 300) {
                $fail('The :attribute dimensions must not exceed 300x300 pixels.');
            }
        } catch (\Exception $e) {
            Log::error('Profile Image validation: ' . $e->getMessage());
            // Fail the validation if the image cannot be processed
            $fail('The :attribute is not a valid image.');
        }
    }
}
