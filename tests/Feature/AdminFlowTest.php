<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
    }

    public function test_admin_can_authenticate_using_username_and_password(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_update_report_status(): void
    {
        $regionCodes = $this->seedPercutSeiTuan();

        $admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_KECAMATAN,
            'region_code' => $regionCodes['district'],
        ]);

        $category = Category::create([
            'name' => 'Kebersihan',
            'is_active' => true,
        ]);

        $report = Report::create([
            'ticket_code' => 'LM-2026-000001',
            'category_id' => $category->id,
            'title' => 'Sampah menumpuk',
            'description' => 'Ada sampah menumpuk di pinggir jalan.',
            'province_code' => $regionCodes['province'],
            'regency_code' => $regionCodes['regency'],
            'district_code' => $regionCodes['district'],
            'village_code' => $regionCodes['village'],
            'location_text' => 'Jalan Melati',
            'status' => 'BARU',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.update', $report), [
            '_method' => 'patch',
            'status' => 'DIPROSES',
            'note' => 'Petugas sudah dijadwalkan ke lokasi.',
        ]);

        $response->assertRedirect(route('admin.reports.show', $report, absolute: false));

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'DIPROSES',
        ]);

        $this->assertDatabaseHas('status_logs', [
            'report_id' => $report->id,
            'user_id' => $admin->id,
            'old_status' => 'BARU',
            'new_status' => 'DIPROSES',
        ]);
    }

    public function test_district_admin_cannot_open_report_from_other_district(): void
    {
        $percut = $this->seedPercutSeiTuan();
        $lubukPakam = $this->seedLubukPakam();

        $admin = User::create([
            'name' => 'Admin Percut',
            'username' => 'admin-percut',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_KECAMATAN,
            'region_code' => $percut['district'],
        ]);

        $category = Category::create([
            'name' => 'Kebersihan',
            'is_active' => true,
        ]);

        $report = Report::create([
            'ticket_code' => 'LM-2026-000002',
            'category_id' => $category->id,
            'title' => 'Sampah menumpuk',
            'description' => 'Ada sampah menumpuk di pinggir jalan.',
            'province_code' => $lubukPakam['province'],
            'regency_code' => $lubukPakam['regency'],
            'district_code' => $lubukPakam['district'],
            'village_code' => $lubukPakam['village'],
            'location_text' => 'Jalan Melati',
            'status' => 'BARU',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertForbidden();
    }

    public function test_regency_admin_cannot_open_report_from_other_regency(): void
    {
        $percut = $this->seedPercutSeiTuan();
        $medan = $this->seedMedanKota();

        $admin = User::create([
            'name' => 'Admin Deli Serdang',
            'username' => 'admin-ds',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_KABUPATEN_KOTA,
            'region_code' => $percut['regency'],
        ]);

        $category = Category::create([
            'name' => 'Kebersihan',
            'is_active' => true,
        ]);

        $report = Report::create([
            'ticket_code' => 'LM-2026-000003',
            'category_id' => $category->id,
            'title' => 'Sampah menumpuk',
            'description' => 'Ada sampah menumpuk di pinggir jalan.',
            'province_code' => $medan['province'],
            'regency_code' => $medan['regency'],
            'district_code' => $medan['district'],
            'village_code' => $medan['village'],
            'location_text' => 'Jalan Melati',
            'status' => 'BARU',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertForbidden();
    }

    public function test_province_admin_can_open_report_from_same_province(): void
    {
        $percut = $this->seedPercutSeiTuan();

        $admin = User::create([
            'name' => 'Admin Sumut',
            'username' => 'admin-sumut',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_PROVINSI,
            'region_code' => $percut['province'],
        ]);

        $category = Category::create([
            'name' => 'Kebersihan',
            'is_active' => true,
        ]);

        $report = Report::create([
            'ticket_code' => 'LM-2026-000004',
            'category_id' => $category->id,
            'title' => 'Sampah menumpuk',
            'description' => 'Ada sampah menumpuk di pinggir jalan.',
            'province_code' => $percut['province'],
            'regency_code' => $percut['regency'],
            'district_code' => $percut['district'],
            'village_code' => $percut['village'],
            'location_text' => 'Jalan Melati',
            'status' => 'BARU',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertOk();
    }

    public function test_super_admin_can_access_accounts_page(): void
    {
        $this->withoutVite();

        $admin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.accounts.index'))
            ->assertOk();
    }

    public function test_district_admin_cannot_access_accounts_page(): void
    {
        $regionCodes = $this->seedPercutSeiTuan();

        $admin = User::create([
            'name' => 'Admin District',
            'username' => 'admin-district',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_KECAMATAN,
            'region_code' => $regionCodes['district'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.accounts.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_update_other_user_password(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admin-test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_PROVINSI,
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('admin.accounts.password.update', $user), [
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_super_admin_can_export_accounts_csv(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $province = $this->seedNorthSumatraProvinceCode();

        User::create([
            'name' => 'Admin Provinsi Sumut',
            'username' => 'provinsi_sumatera_utara',
            'password' => 'password',
            'role' => User::ROLE_ADMIN_PROVINSI,
            'region_code' => $province,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.accounts.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('provinsi_sumatera_utara', $response->streamedContent());
    }

    public function test_super_admin_can_upload_region_logo(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $provinceCode = $this->seedNorthSumatraProvinceCode();
        $targetPath = public_path('assets/logos/provinces/'.$provinceCode.'.png');

        if (is_file($targetPath)) {
            File::delete($targetPath);
        }

        $this->actingAs($superAdmin)
            ->post(route('admin.logos.store'), [
                'region_code' => $provinceCode,
                'logo' => UploadedFile::fake()->image('sumut.png'),
            ])
            ->assertRedirect();

        $this->assertFileExists($targetPath);

        File::delete($targetPath);
    }

    public function test_super_admin_can_import_region_logo_zip(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $provinceCode = $this->seedNorthSumatraProvinceCode();
        $percut = $this->seedPercutSeiTuan();
        $provinceTarget = public_path('assets/logos/provinces/'.$provinceCode.'.png');
        $districtTarget = public_path('assets/logos/districts/'.$percut['district'].'.png');

        File::delete([$provinceTarget, $districtTarget]);

        $archivePath = tempnam(sys_get_temp_dir(), 'logos-');
        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('provinces/12.png', 'province-logo');
        $zip->addFromString('districts/12.07.26.png', 'district-logo');
        $zip->addFromString('ignored/readme.txt', 'text');
        $zip->close();

        $upload = new UploadedFile($archivePath, 'logos.zip', 'application/zip', null, true);

        $this->actingAs($superAdmin)
            ->post(route('admin.logos.import'), [
                'archive' => $upload,
            ])
            ->assertRedirect();

        $this->assertFileExists($provinceTarget);
        $this->assertFileExists($districtTarget);

        File::delete([$provinceTarget, $districtTarget]);
    }

    private function seedPercutSeiTuan(): array
    {
        $this->seedNorthSumatraProvince();
        $this->insertRegionIfMissing(['code' => '12.07', 'name' => 'Kabupaten Deli Serdang', 'level' => Region::LEVEL_REGENCY, 'parent_code' => '12']);
        $this->insertRegionIfMissing(['code' => '12.07.26', 'name' => 'Percut Sei Tuan', 'level' => Region::LEVEL_DISTRICT, 'parent_code' => '12.07']);
        $this->insertRegionIfMissing(['code' => '12.07.26.2001', 'name' => 'Cinta Damai', 'level' => Region::LEVEL_VILLAGE, 'parent_code' => '12.07.26']);

        return [
            'province' => '12',
            'regency' => '12.07',
            'district' => '12.07.26',
            'village' => '12.07.26.2001',
        ];
    }

    private function seedLubukPakam(): array
    {
        $this->seedNorthSumatraProvince();
        $this->insertRegionIfMissing(['code' => '12.07', 'name' => 'Kabupaten Deli Serdang', 'level' => Region::LEVEL_REGENCY, 'parent_code' => '12']);
        $this->insertRegionIfMissing(['code' => '12.07.28', 'name' => 'Lubuk Pakam', 'level' => Region::LEVEL_DISTRICT, 'parent_code' => '12.07']);
        $this->insertRegionIfMissing(['code' => '12.07.28.1001', 'name' => 'Lubuk Pakam Pekan', 'level' => Region::LEVEL_VILLAGE, 'parent_code' => '12.07.28']);

        return [
            'province' => '12',
            'regency' => '12.07',
            'district' => '12.07.28',
            'village' => '12.07.28.1001',
        ];
    }

    private function seedMedanKota(): array
    {
        $this->seedNorthSumatraProvince();
        $this->insertRegionIfMissing(['code' => '12.71', 'name' => 'Kota Medan', 'level' => Region::LEVEL_REGENCY, 'parent_code' => '12']);
        $this->insertRegionIfMissing(['code' => '12.71.01', 'name' => 'Medan Kota', 'level' => Region::LEVEL_DISTRICT, 'parent_code' => '12.71']);
        $this->insertRegionIfMissing(['code' => '12.71.01.1001', 'name' => 'Pasar Baru', 'level' => Region::LEVEL_VILLAGE, 'parent_code' => '12.71.01']);

        return [
            'province' => '12',
            'regency' => '12.71',
            'district' => '12.71.01',
            'village' => '12.71.01.1001',
        ];
    }

    private function seedNorthSumatraProvince(): void
    {
        $this->seedNorthSumatraProvinceCode();
    }

    private function seedNorthSumatraProvinceCode(): string
    {
        $this->insertRegionIfMissing(['code' => '12', 'name' => 'Sumatera Utara', 'level' => Region::LEVEL_PROVINCE, 'parent_code' => null]);

        return '12';
    }

    private function insertRegionIfMissing(array $attributes): void
    {
        Region::query()->firstOrCreate(['code' => $attributes['code']], $attributes);
    }
}
