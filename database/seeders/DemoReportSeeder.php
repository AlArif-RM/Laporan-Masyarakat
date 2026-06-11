<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'name');
        $districtUsernames = DistrictAdminSeeder::districtUsernames();

        foreach ($this->reports() as $index => $reportData) {
            $district = Region::query()->findOrFail($reportData['district_code']);
            $regency = Region::query()->findOrFail($district->parent_code);
            $province = Region::query()->findOrFail($regency->parent_code);
            $village = Region::query()
                ->byLevel(Region::LEVEL_VILLAGE)
                ->where('parent_code', $district->code)
                ->ordered()
                ->first();

            $createdAt = Carbon::now()->subDays(6 - $index)->setTime(9 + $index, 15);
            $updatedAt = $reportData['status'] === 'BARU'
                ? $createdAt
                : $createdAt->copy()->addHours(6 + $index);

            $report = Report::query()->updateOrCreate(
                ['ticket_code' => $reportData['ticket_code']],
                [
                    'reporter_name' => $reportData['reporter_name'],
                    'phone' => $reportData['phone'],
                    'category_id' => $categories[$reportData['category_name']],
                    'other_category' => null,
                    'title' => $reportData['title'],
                    'description' => $reportData['description'],
                    'province_code' => $province->code,
                    'regency_code' => $regency->code,
                    'district_code' => $district->code,
                    'village_code' => $village?->code,
                    'location_text' => $reportData['location_text'],
                    'rt' => $reportData['rt'],
                    'rw' => $reportData['rw'],
                    'status' => $reportData['status'],
                ],
            );

            DB::table('reports')
                ->where('id', $report->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

            DB::table('status_logs')->where('report_id', $report->id)->delete();

            $logs = [[
                'report_id' => $report->id,
                'user_id' => null,
                'old_status' => null,
                'new_status' => 'BARU',
                'note' => 'Laporan contoh awal dimasukkan ke sistem.',
                'changed_at' => $createdAt,
            ]];

            if ($reportData['status'] !== 'BARU') {
                $adminId = User::query()
                    ->where('username', $districtUsernames[$reportData['district_code']] ?? null)
                    ->value('id');

                $logs[] = [
                    'report_id' => $report->id,
                    'user_id' => $adminId,
                    'old_status' => 'BARU',
                    'new_status' => $reportData['status'],
                    'note' => $reportData['status_note'],
                    'changed_at' => $updatedAt,
                ];
            }

            DB::table('status_logs')->insert($logs);
        }
    }

    private function reports(): array
    {
        $districtTemplates = [
            ['code' => '12.07.01', 'category' => 'Infrastruktur', 'title' => 'Perbaikan jalan lingkungan berlubang', 'location' => 'Jalan penghubung dusun utama'],
            ['code' => '12.07.02', 'category' => 'Infrastruktur', 'title' => 'Lampu jalan mati di jalan penghubung desa', 'location' => 'Jalan penghubung desa menuju kantor camat'],
            ['code' => '12.07.03', 'category' => 'Drainase/Banjir', 'title' => 'Saluran air meluap saat hujan', 'location' => 'Dekat jembatan kecil desa'],
            ['code' => '12.07.04', 'category' => 'Pelayanan Publik', 'title' => 'Pelayanan administrasi perlu tambahan loket', 'location' => 'Area pelayanan kecamatan'],
            ['code' => '12.07.05', 'category' => 'Keamanan/Ketertiban', 'title' => 'Balap liar pada malam akhir pekan', 'location' => 'Jalan besar dekat simpang empat'],
            ['code' => '12.07.06', 'category' => 'Kebersihan', 'title' => 'Sampah rumah tangga belum terangkut rutin', 'location' => 'Ujung jalan permukiman warga'],
            ['code' => '12.07.07', 'category' => 'Infrastruktur', 'title' => 'Jembatan kecil butuh perbaikan', 'location' => 'Akses warga menuju lahan pertanian'],
            ['code' => '12.07.08', 'category' => 'Sosial', 'title' => 'Posko bantuan warga perlu penataan', 'location' => 'Balai warga pusat dusun'],
            ['code' => '12.07.09', 'category' => 'Pelayanan Publik', 'title' => 'Jadwal pelayanan belum informatif', 'location' => 'Papan informasi kantor kecamatan'],
            ['code' => '12.07.19', 'category' => 'Kebersihan', 'title' => 'Area pasar tradisional perlu pembersihan', 'location' => 'Sekitar pintu masuk pasar'],
            ['code' => '12.07.20', 'category' => 'Drainase/Banjir', 'title' => 'Drainase permukiman tersumbat lumpur', 'location' => 'Sisi jalan utama desa'],
            ['code' => '12.07.21', 'category' => 'Keamanan/Ketertiban', 'title' => 'Parkir liar mengganggu arus lalu lintas', 'location' => 'Depan deretan pertokoan'],
            ['code' => '12.07.22', 'category' => 'Sosial', 'title' => 'Lampu penerangan balai warga padam', 'location' => 'Balai warga kecamatan'],
            ['code' => '12.07.23', 'category' => 'Kebersihan', 'title' => 'Sampah menumpuk di dekat drainase', 'location' => 'Sudut lingkungan padat penduduk'],
            ['code' => '12.07.24', 'category' => 'Pelayanan Publik', 'title' => 'Antrean pelayanan administrasi terlalu lama', 'location' => 'Kantor pelayanan kecamatan'],
            ['code' => '12.07.25', 'category' => 'Drainase/Banjir', 'title' => 'Genangan air bertahan hingga sore', 'location' => 'Dekat akses sekolah dan masjid'],
            ['code' => '12.07.26', 'category' => 'Kebersihan', 'title' => 'Sampah menumpuk di sekitar pasar', 'location' => 'Area pasar dekat jalan utama'],
            ['code' => '12.07.27', 'category' => 'Sosial', 'title' => 'Permintaan penataan posko bantuan warga', 'location' => 'Balai warga dekat lapangan'],
            ['code' => '12.07.28', 'category' => 'Drainase/Banjir', 'title' => 'Drainase tersumbat di lingkungan sekolah', 'location' => 'Depan sekolah dasar setempat'],
            ['code' => '12.07.31', 'category' => 'Infrastruktur', 'title' => 'Jalan rabat beton mulai retak', 'location' => 'Lorong permukiman baru'],
            ['code' => '12.07.32', 'category' => 'Pelayanan Publik', 'title' => 'Pengumuman pelayanan belum diperbarui', 'location' => 'Halaman depan kantor desa'],
            ['code' => '12.07.33', 'category' => 'Keamanan/Ketertiban', 'title' => 'Butuh penertiban pedagang di bahu jalan', 'location' => 'Sekitar simpang jalan lintas'],
        ];

        $statuses = ['BARU', 'DIPROSES', 'SELESAI', 'DITOLAK'];
        $statusNotes = [
            'BARU' => '',
            'DIPROSES' => 'Petugas kecamatan sudah menjadwalkan tindak lanjut lapangan.',
            'SELESAI' => 'Tindak lanjut laporan telah selesai dan lokasi dinyatakan tertangani.',
            'DITOLAK' => 'Laporan ditolak karena data pendukung belum cukup untuk ditindaklanjuti.',
        ];
        $reporters = ['Nurhayati', 'Budi Santoso', 'Ratna Dewi', 'Mhd. Fajar', 'Sulastri', 'Andri Syahputra', 'Lina Wati', 'Rizki Ananda'];
        $reports = [];

        foreach ($districtTemplates as $index => $template) {
            $status = $statuses[$index % count($statuses)];

            $reports[] = [
                'ticket_code' => 'LM-DEMO-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'reporter_name' => $reporters[$index % count($reporters)],
                'phone' => '08126111'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'category_name' => $template['category'],
                'district_code' => $template['code'],
                'title' => $template['title'],
                'description' => $template['title'].' di '.$template['location'].'. Warga berharap ada tindak lanjut yang jelas dari kecamatan.',
                'location_text' => $template['location'],
                'rt' => str_pad((string) (($index % 5) + 1), 3, '0', STR_PAD_LEFT),
                'rw' => str_pad((string) (($index % 4) + 1), 3, '0', STR_PAD_LEFT),
                'status' => $status,
                'status_note' => $statusNotes[$status],
            ];
        }

        return $reports;
    }
}
