<?php

namespace App\Library;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileManagerLibrary
{
    public function __construct(private Filesystem $publicStorage)
    {
        $this->publicStorage = Storage::disk('public');
    }

    public function profileImageUpload(User $user, string $base64String): string
    {
        // Delete existing avatar if present
        if ($user->avatar && $this->publicStorage->exists($user->avatar)) {
            $this->publicStorage->delete($user->avatar);
        }

        return $this->uploadImage($base64String, 'images/users', 'user_image');
    }

    public function postImageUpload(string $base64String): string
    {
        return $this->uploadImage($base64String, 'images/posts', 'post_image');
    }

    public function postImageDelete(string $path): array
    {
        // Remove the base URL part to get the relative path
        $relativePath = str_replace(config('filesystems.disks.public.url').'/', '', $path);
        // Check if the image exists
        if ($relativePath && $this->publicStorage->exists($relativePath)) {
            $this->publicStorage->delete($relativePath);

            return [
                'message' => 'Image deleted successfully.',
                'status' => true,
            ];
        }

        return [
            'message' => 'Image not found.',
            'status' => false,
        ];
    }

    private function uploadImage(string $base64String, string $directory, string $prefix): string
    {
        $imageData = $this->base64ToImage($base64String);
        $dateTime = now()->format('His');
        $fileName = $prefix.'_'.$dateTime.'.'.$imageData['extension'];
        $path = $directory.'/'.$fileName;

        $this->publicStorage->put($path, $imageData['string']);

        return $path;
    }

    private function base64ToImage(string $base64String): array
    {
        $defaultExtension = 'jpg';
        $imageType = '';

        // Check if metadata is present in base64 string
        if (str_contains($base64String, ';base64,')) {
            [$metadata, $base64String] = explode(';base64,', $base64String);
            if ($metadata) {
                $imageType = explode('/', $metadata)[1] ?? '';
            }
        }

        $extension = $imageType ?: $defaultExtension;

        return [
            'string' => base64_decode($base64String),
            'extension' => $extension,
        ];
    }
}
