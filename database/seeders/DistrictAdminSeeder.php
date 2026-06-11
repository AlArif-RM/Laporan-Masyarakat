<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;
use App\Support\RegionAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DistrictAdminSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('admin123');

        User::query()->updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'password' => $passwordHash,
                'role' => User::ROLE_SUPER_ADMIN,
                'region_code' => null,
            ],
        );

        $accounts = RegionAdmin::accountsFromRawRegions(
            Region::query()
                ->whereIn('level', RegionAdmin::ADMIN_LEVELS)
                ->get(['code', 'name', 'level'])
                ->map(fn (Region $region) => [
                    'code' => $region->code,
                    'name' => $region->name,
                    'level' => $region->level,
                ])
                ->all(),
        );

        foreach (array_chunk(array_map(fn (array $account) => [
            'username' => $account['username'],
            'password' => $passwordHash,
            'name' => $account['account_name'],
            'role' => $account['role'],
            'region_code' => $account['code'],
            'created_at' => now(),
        ], $accounts), 500) as $chunk) {
            DB::table('users')->upsert(
                $chunk,
                ['username'],
                ['password', 'name', 'role', 'region_code'],
            );
        }
    }

    public static function districtUsernames(): array
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN_KECAMATAN)
            ->pluck('username', 'region_code')
            ->all();
    }
}
