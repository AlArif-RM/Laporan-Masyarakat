<?php

namespace App\Support;

use App\Models\Region;
use App\Models\User;

class RegionBranding
{
    public static function forRegion(?Region $region): array
    {
        if (! $region) {
            return [
                'region_name' => null,
                'organization' => 'Super Admin Nasional',
                'unit' => 'Layanan Aspirasi dan Pengaduan Masyarakat',
                'service_name' => 'Administrasi nasional pengelolaan laporan masyarakat',
                'signature' => 'Super Admin Nasional',
                'logo_path' => null,
            ];
        }

        $region->loadMissing('parent');

        return [
            'region_name' => $region->name,
            'organization' => 'Pemerintah '.RegionAdmin::administrativeName($region->level, $region->name),
            'unit' => $region->parent
                ? RegionAdmin::administrativeName($region->parent->level, $region->parent->name)
                : 'Republik Indonesia',
            'service_name' => 'Layanan Aspirasi dan Pengaduan Masyarakat',
            'signature' => RegionAdmin::accountName($region->level, $region->name),
            'logo_path' => self::resolveLogoPath($region),
        ];
    }

    public static function forAdmin(?User $user): array
    {
        return self::forRegion($user?->region);
    }

    public static function folderForLevel(string $level): ?string
    {
        return match ($level) {
            Region::LEVEL_PROVINCE => 'provinces',
            Region::LEVEL_REGENCY => 'regencies',
            Region::LEVEL_DISTRICT => 'districts',
            default => null,
        };
    }

    public static function relativePathForRegion(Region $region, string $extension): ?string
    {
        $folder = self::folderForLevel($region->level);

        if ($folder === null) {
            return null;
        }

        return 'assets/logos/'.$folder.'/'.$region->code.'.'.$extension;
    }

    public static function publicUrlForRegion(Region $region): ?string
    {
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $relativePath = self::relativePathForRegion($region, $extension);

            if ($relativePath !== null && is_file(public_path($relativePath))) {
                return asset($relativePath).'?v='.filemtime(public_path($relativePath));
            }
        }

        return null;
    }

    private static function resolveLogoPath(Region $region): ?string
    {
        $folder = self::folderForLevel($region->level);

        if ($folder === null) {
            return null;
        }

        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $path = public_path('assets/logos/'.$folder.'/'.$region->code.'.'.$extension);

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
