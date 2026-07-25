<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(Media $media, ?string $conversion = null): BinaryFileResponse
    {
        abort_unless($media->model_type === User::class, 404);

        if ($conversion) {
            if ($media->hasGeneratedConversion($conversion) && is_file($media->getPath($conversion))) {
                return response()->file($media->getPath($conversion));
            }

            abort(404);
        }

        abort_unless(is_file($media->getPath()), 404);

        return response()->file($media->getPath());
    }
}
