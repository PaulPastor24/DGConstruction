<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PdfImageService
{
    public function canRenderImages(): bool
    {
        return extension_loaded('gd');
    }

    public function toDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
