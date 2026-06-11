<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['Infrastruktur', 'Kebersihan', 'Drainase/Banjir', 'Keamanan/Ketertiban', 'Pelayanan Publik', 'Sosial', 'Lainnya'] as $categoryName) {
            Category::query()->firstOrCreate(['name' => $categoryName], ['is_active' => true]);
        }

        User::query()->firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_SUPER_ADMIN,
            ],
        );
    }
}
