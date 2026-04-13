<?php

namespace Tests\Unit;

use App\Rules\Base64Image;
use App\Rules\ImageExtension;
use App\Rules\ImageSize;
use App\Rules\SquareImage;
use Tests\TestCase;

class ValidationRuleTest extends TestCase
{
    public function test_image_extension_accepts_supported_extensions(): void
    {
        $messages = $this->validateRule(new ImageExtension(), 'avatar', 'data:image/png;base64,Zm9v');

        $this->assertSame([], $messages);
    }

    public function test_image_extension_rejects_unsupported_extensions(): void
    {
        $messages = $this->validateRule(new ImageExtension(), 'avatar', 'data:image/gif;base64,Zm9v');

        $this->assertSame(['The :attribute must be a JPG, JPEG, or PNG image.'], $messages);
    }

    public function test_image_size_passes_when_under_limit(): void
    {
        $messages = $this->validateRule(
            new ImageSize(1),
            'avatar',
            'data:image/png;base64,'.base64_encode(str_repeat('a', 128))
        );

        $this->assertSame([], $messages);
    }

    public function test_image_size_rejects_values_over_limit(): void
    {
        $messages = $this->validateRule(
            new ImageSize(1),
            'avatar',
            'data:image/png;base64,'.base64_encode(str_repeat('a', 2 * 1024 * 1024))
        );

        $this->assertSame(['The :attribute size must not exceed 1MB.'], $messages);
    }

    public function test_base64_image_rejects_invalid_payloads(): void
    {
        $messages = $this->validateRule(new Base64Image(), 'avatar', 'not-an-image');

        $this->assertSame(['The :attribute must be a valid image.'], $messages);
    }

    public function test_square_image_rejects_invalid_payloads(): void
    {
        $messages = $this->validateRule(new SquareImage(), 'avatar', 'not-an-image');

        $this->assertSame(['The :attribute is not a valid image.'], $messages);
    }

    private function validateRule(object $rule, string $attribute, mixed $value): array
    {
        $messages = [];

        $rule->validate($attribute, $value, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        return $messages;
    }
}
