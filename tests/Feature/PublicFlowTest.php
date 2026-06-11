<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Attachment;
use App\Models\Region;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads'));

        parent::tearDown();
    }

    public function test_home_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_lookup_page_can_be_rendered(): void
    {
        $response = $this->get('/cek-status');

        $response->assertOk();
    }

    public function test_public_can_submit_a_report(): void
    {
        $regionCodes = $this->seedRegionHierarchy();

        $category = Category::create([
            'name' => 'Infrastruktur',
            'is_active' => true,
        ]);

        $response = $this->post('/laporan', [
            'reporter_name' => 'Warga Test',
            'phone' => '08123456789',
            'title' => 'Lampu jalan mati',
            'category_id' => $category->id,
            'description' => 'Lampu jalan di depan kantor kelurahan mati sejak semalam.',
            'province_code' => $regionCodes['province'],
            'regency_code' => $regionCodes['regency'],
            'district_code' => $regionCodes['district'],
            'village_code' => $regionCodes['village'],
            'location_text' => 'Depan kantor kelurahan',
            'rt' => '001',
            'rw' => '002',
        ]);

        $report = Report::first();

        $response->assertRedirect(route('reports.success', ['ticket' => $report->ticket_code]));

        $this->assertDatabaseHas('reports', [
            'ticket_code' => $report->ticket_code,
            'title' => 'Lampu jalan mati',
            'status' => 'BARU',
        ]);

        $this->assertDatabaseHas('status_logs', [
            'report_id' => $report->id,
            'new_status' => 'BARU',
        ]);
    }

    public function test_public_can_submit_a_report_with_photo(): void
    {
        $regionCodes = $this->seedRegionHierarchy();

        $category = Category::create([
            'name' => 'Infrastruktur',
            'is_active' => true,
        ]);

        $response = $this->post('/laporan', [
            'reporter_name' => 'Warga Test',
            'phone' => '08123456789',
            'title' => 'Lampu jalan mati',
            'category_id' => $category->id,
            'description' => 'Lampu jalan di depan kantor kelurahan mati sejak semalam.',
            'province_code' => $regionCodes['province'],
            'regency_code' => $regionCodes['regency'],
            'district_code' => $regionCodes['district'],
            'village_code' => $regionCodes['village'],
            'location_text' => 'Depan kantor kelurahan',
            'photo' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $report = Report::first();
        $attachment = Attachment::first();

        $response
            ->assertRedirect(route('reports.success', ['ticket' => $report->ticket_code]))
            ->assertSessionMissing('warning');

        $this->assertNotNull($attachment);
        $this->assertDatabaseHas('attachments', [
            'report_id' => $report->id,
            'type' => 'REPORT',
        ]);
        $this->assertFileExists(public_path(ltrim($attachment->file_path, '/')));
    }

    public function test_public_report_still_created_when_photo_is_invalid(): void
    {
        $regionCodes = $this->seedRegionHierarchy();

        $category = Category::create([
            'name' => 'Infrastruktur',
            'is_active' => true,
        ]);

        $response = $this->post('/laporan', [
            'reporter_name' => 'Warga Test',
            'phone' => '08123456789',
            'title' => 'Lampu jalan mati',
            'category_id' => $category->id,
            'description' => 'Lampu jalan di depan kantor kelurahan mati sejak semalam.',
            'province_code' => $regionCodes['province'],
            'regency_code' => $regionCodes['regency'],
            'district_code' => $regionCodes['district'],
            'village_code' => $regionCodes['village'],
            'location_text' => 'Depan kantor kelurahan',
            'photo' => UploadedFile::fake()->create('bukti.pdf', 500, 'application/pdf'),
        ]);

        $report = Report::first();

        $response
            ->assertRedirect(route('reports.success', ['ticket' => $report->ticket_code]))
            ->assertSessionHas('warning', 'Laporan berhasil dikirim, tetapi foto bukti hanya mendukung format JPG, JPEG, PNG, atau WEBP dengan ukuran maksimal 10 MB.');

        $this->assertDatabaseHas('reports', [
            'ticket_code' => $report->ticket_code,
            'title' => 'Lampu jalan mati',
        ]);
        $this->assertDatabaseCount('attachments', 0);
    }

    private function seedRegionHierarchy(): array
    {
        Region::insert([
            ['code' => '12', 'name' => 'SUMATERA UTARA', 'level' => Region::LEVEL_PROVINCE, 'parent_code' => null],
            ['code' => '12.07', 'name' => 'KABUPATEN DELI SERDANG', 'level' => Region::LEVEL_REGENCY, 'parent_code' => '12'],
            ['code' => '12.07.05', 'name' => 'PERCUT SEI TUAN', 'level' => Region::LEVEL_DISTRICT, 'parent_code' => '12.07'],
            ['code' => '12.07.05.2001', 'name' => 'CINTA DAMAI', 'level' => Region::LEVEL_VILLAGE, 'parent_code' => '12.07.05'],
        ]);

        return [
            'province' => '12',
            'regency' => '12.07',
            'district' => '12.07.05',
            'village' => '12.07.05.2001',
        ];
    }
}
