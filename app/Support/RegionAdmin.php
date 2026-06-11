<?php

namespace App\Support;

use App\Models\Region;
use App\Models\User;

class RegionAdmin
{
    public const ADMIN_LEVELS = [
        Region::LEVEL_PROVINCE,
        Region::LEVEL_REGENCY,
        Region::LEVEL_DISTRICT,
    ];

    public const REPORT_COLUMN_BY_ROLE = [
        User::ROLE_ADMIN_PROVINSI => 'province_code',
        User::ROLE_ADMIN_KABUPATEN_KOTA => 'regency_code',
        User::ROLE_ADMIN_KECAMATAN => 'district_code',
    ];

    public static function roleForLevel(string $level): ?string
    {
        return match ($level) {
            Region::LEVEL_PROVINCE => User::ROLE_ADMIN_PROVINSI,
            Region::LEVEL_REGENCY => User::ROLE_ADMIN_KABUPATEN_KOTA,
            Region::LEVEL_DISTRICT => User::ROLE_ADMIN_KECAMATAN,
            default => null,
        };
    }

    public static function reportColumnForRole(?string $role): ?string
    {
        return self::REPORT_COLUMN_BY_ROLE[$role] ?? null;
    }

    public static function roleLabel(?string $role): string
    {
        return match ($role) {
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_ADMIN_PROVINSI => 'Admin Provinsi',
            User::ROLE_ADMIN_KABUPATEN_KOTA => 'Admin Kabupaten/Kota',
            User::ROLE_ADMIN_KECAMATAN => 'Admin Kecamatan',
            default => 'Admin',
        };
    }

    public static function administrativeName(string $level, string $name): string
    {
        return match ($level) {
            Region::LEVEL_PROVINCE => str_starts_with($name, 'Provinsi ') ? $name : 'Provinsi '.$name,
            Region::LEVEL_REGENCY => $name,
            Region::LEVEL_DISTRICT => str_starts_with($name, 'Kecamatan ') ? $name : 'Kecamatan '.$name,
            default => $name,
        };
    }

    public static function accountName(string $level, string $name): string
    {
        return match ($level) {
            Region::LEVEL_PROVINCE => 'Admin Provinsi '.$name,
            Region::LEVEL_REGENCY => 'Admin '.$name,
            Region::LEVEL_DISTRICT => 'Admin Kecamatan '.$name,
            default => 'Admin '.$name,
        };
    }

    public static function accountsFromRawRegions(array $regions): array
    {
        $eligibleRegions = array_values(array_filter($regions, fn (array $region): bool => in_array($region['level'], self::ADMIN_LEVELS, true)));

        usort($eligibleRegions, fn (array $left, array $right): int => strcmp($left['code'], $right['code']));

        $seenUsernames = [];
        $accounts = [];

        foreach ($eligibleRegions as $region) {
            $role = self::roleForLevel($region['level']);

            if ($role === null) {
                continue;
            }

            $accounts[] = [
                'code' => $region['code'],
                'name' => $region['name'],
                'level' => $region['level'],
                'role' => $role,
                'username' => self::usernameForRegion($region['level'], $region['name'], $region['code'], $seenUsernames),
                'account_name' => self::accountName($region['level'], $region['name']),
            ];
        }

        return $accounts;
    }

    public static function usernameMapForLevel(array $accounts, string $level): array
    {
        $usernames = [];

        foreach ($accounts as $account) {
            if (($account['level'] ?? null) !== $level) {
                continue;
            }

            $usernames[$account['code']] = $account['username'];
        }

        return $usernames;
    }

    private static function usernameForRegion(string $level, string $name, string $code, array &$seenUsernames): string
    {
        $base = match ($level) {
            Region::LEVEL_PROVINCE => 'provinsi_'.self::slugify($name),
            Region::LEVEL_REGENCY => self::slugify($name),
            Region::LEVEL_DISTRICT => 'kecamatan_'.self::slugify($name),
            default => self::slugify($name),
        };

        $username = $base;

        if (isset($seenUsernames[$username])) {
            $username .= '_'.str_replace('.', '', $code);
        }

        $seenUsernames[$username] = true;

        return $username;
    }

    private static function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;
        $value = trim($value, '_');

        return $value === '' ? 'wilayah' : $value;
    }
}
