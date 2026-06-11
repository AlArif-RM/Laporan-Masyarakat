<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LegacyPath
{
    public static function projectRootName(): string
    {
        return basename(dirname(base_path()));
    }

    public static function fromLegacyRoot(string $relativePath): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relativePath, '/\\'));

        return dirname(base_path()).DIRECTORY_SEPARATOR.$normalized;
    }

    public static function absoluteFromPublic(string $publicPath): ?string
    {
        $normalized = str_replace('\\', '/', $publicPath);

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '/uploads/')) {
            return public_path(ltrim($normalized, '/'));
        }

        $prefix = '/'.self::projectRootName().'/';

        if (! str_starts_with($normalized, $prefix)) {
            return null;
        }

        return self::fromLegacyRoot(substr($normalized, strlen($prefix)));
    }

    public static function storeUpload(UploadedFile $file, string $folder): string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');
        $relativeDirectory = trim('uploads/'.$folder, '/');
        $targetDirectory = public_path($relativeDirectory);

        File::ensureDirectoryExists($targetDirectory, 0755, true);

        $filename = Str::random(32).'.'.$file->getClientOriginalExtension();
        $file->move($targetDirectory, $filename);

        return '/'.$relativeDirectory.'/'.$filename;
    }

    public static function deleteUpload(string $publicPath): void
    {
        $absolutePath = self::absoluteFromPublic($publicPath);

        if ($absolutePath && is_file($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    public static function publicUrl(string $publicPath): string
    {
        if (str_starts_with($publicPath, 'http://') || str_starts_with($publicPath, 'https://')) {
            return $publicPath;
        }

        return asset(ltrim(str_replace('\\', '/', $publicPath), '/'));
    }
}
