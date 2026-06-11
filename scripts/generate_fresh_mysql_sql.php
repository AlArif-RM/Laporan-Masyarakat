<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
require_once $projectRoot.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$outputDirectory = $projectRoot.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'sql';
$outputFile = $outputDirectory.DIRECTORY_SEPARATOR.'lapmas_fresh_mysql.sql';
$sourceUrl = 'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql';

ensureDirectory($outputDirectory);

$sourceSql = fetchSource($sourceUrl);
$regions = parseRegions($sourceSql);

$percutSeiTuan = findDistrictByName($regions, 'PERCUT SEI TUAN');

if ($percutSeiTuan === null) {
    fwrite(STDERR, "District 'PERCUT SEI TUAN' was not found in the source data.\n");
    exit(1);
}

$sql = buildSqlDocument($regions, $percutSeiTuan['code']);

file_put_contents($outputFile, $sql);

printf(
    "Generated %s with %d regions. Sample district admin mapped to %s (%s).\n",
    $outputFile,
    count($regions),
    $percutSeiTuan['name'],
    $percutSeiTuan['code'],
);

function ensureDirectory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        throw new RuntimeException(sprintf('Failed to create directory: %s', $directory));
    }
}

function fetchSource(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: LapMas SQL Generator\r\n",
            'timeout' => 120,
        ],
        'https' => [
            'header' => "User-Agent: LapMas SQL Generator\r\n",
            'timeout' => 120,
        ],
    ]);

    $contents = @file_get_contents($url, false, $context);

    if ($contents === false) {
        throw new RuntimeException(sprintf('Failed to fetch source SQL from %s', $url));
    }

    return $contents;
}

function parseRegions(string $sourceSql): array
{
    preg_match_all("/\('([^']+)','((?:[^']|'')*)'\)/", $sourceSql, $matches, PREG_SET_ORDER);

    $regions = [];

    foreach ($matches as $match) {
        $code = $match[1];
        $name = str_replace("''", "'", $match[2]);
        $segments = explode('.', $code);
        $level = match (count($segments)) {
            1 => 'PROVINCE',
            2 => 'REGENCY',
            3 => 'DISTRICT',
            4 => 'VILLAGE',
            default => null,
        };

        if ($level === null) {
            continue;
        }

        $regions[$code] = [
            'code' => $code,
            'name' => $name,
            'level' => $level,
            'parent_code' => count($segments) > 1 ? implode('.', array_slice($segments, 0, -1)) : null,
        ];
    }

    uasort($regions, static function (array $left, array $right): int {
        $lengthCompare = strlen($left['code']) <=> strlen($right['code']);

        if ($lengthCompare !== 0) {
            return $lengthCompare;
        }

        return strcmp($left['code'], $right['code']);
    });

    return array_values($regions);
}

function findDistrictByName(array $regions, string $districtName): ?array
{
    foreach ($regions as $region) {
        if ($region['level'] !== 'DISTRICT') {
            continue;
        }

        if (mb_strtoupper($region['name']) === mb_strtoupper($districtName)) {
            return $region;
        }
    }

    return null;
}

function buildSqlDocument(array $regions, string $districtCode): string
{
    $passwordHash = '$2y$12$q.3hPabXN0Okli96Ztj1WuFgiV9Cuw81f1kDUmRkIwTjqWfY1C/I6';
    $generatedAt = date('Y-m-d H:i:s');
    $adminAccounts = \App\Support\RegionAdmin::accountsFromRawRegions($regions);
    $districtAdminMap = \App\Support\RegionAdmin::usernameMapForLevel($adminAccounts, 'DISTRICT');
    $lines = [];

    $lines[] = '-- LapMas fresh MySQL import';
    $lines[] = '-- Generated automatically by scripts/generate_fresh_mysql_sql.php';
    $lines[] = '-- Source wilayah: Kepmendagri No 300.2.2-2138 Tahun 2025 via github.com/cahyadsn/wilayah';
    $lines[] = sprintf('-- Generated at: %s', $generatedAt);
    $lines[] = '-- Sample login:';
    $lines[] = '--   superadmin / admin123';
    $lines[] = '--   provinsi_sumatera_utara / admin123';
    $lines[] = '--   kabupaten_deli_serdang / admin123';
    $lines[] = '--   kecamatan_percut_sei_tuan / admin123';
    $lines[] = '';
    $lines[] = 'SET NAMES utf8mb4;';
    $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
    $lines[] = '';

    foreach ([
        'attachments',
        'status_logs',
        'reports',
        'categories',
        'users',
        'sessions',
        'failed_jobs',
        'job_batches',
        'jobs',
        'cache_locks',
        'cache',
        'migrations',
        'regions',
    ] as $table) {
        $lines[] = sprintf('DROP TABLE IF EXISTS `%s`;', $table);
    }

    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `regions` (
  `code` varchar(13) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` enum('PROVINCE','REGENCY','DISTRICT','VILLAGE') NOT NULL,
  `parent_code` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `regions_level_name_index` (`level`,`name`),
  KEY `regions_parent_code_index` (`parent_code`),
  CONSTRAINT `regions_parent_code_foreign` FOREIGN KEY (`parent_code`) REFERENCES `regions` (`code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'ADMIN_KECAMATAN',
  `region_code` varchar(13) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_region_code_foreign` (`region_code`),
  CONSTRAINT `users_region_code_foreign` FOREIGN KEY (`region_code`) REFERENCES `regions` (`code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_code` varchar(50) NOT NULL,
  `reporter_name` varchar(100) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `other_category` varchar(100) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` longtext NOT NULL,
  `province_code` varchar(13) NOT NULL,
  `regency_code` varchar(13) NOT NULL,
  `district_code` varchar(13) NOT NULL,
  `village_code` varchar(13) DEFAULT NULL,
  `location_text` varchar(255) NOT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `status` enum('BARU','DIPROSES','SELESAI','DITOLAK') NOT NULL DEFAULT 'BARU',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reports_ticket_code_unique` (`ticket_code`),
  KEY `reports_category_id_foreign` (`category_id`),
  KEY `reports_province_code_foreign` (`province_code`),
  KEY `reports_regency_code_foreign` (`regency_code`),
  KEY `reports_district_code_status_created_at_index` (`district_code`,`status`,`created_at`),
  KEY `reports_village_code_foreign` (`village_code`),
  CONSTRAINT `reports_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `reports_district_code_foreign` FOREIGN KEY (`district_code`) REFERENCES `regions` (`code`) ON DELETE RESTRICT,
  CONSTRAINT `reports_province_code_foreign` FOREIGN KEY (`province_code`) REFERENCES `regions` (`code`) ON DELETE RESTRICT,
  CONSTRAINT `reports_regency_code_foreign` FOREIGN KEY (`regency_code`) REFERENCES `regions` (`code`) ON DELETE RESTRICT,
  CONSTRAINT `reports_village_code_foreign` FOREIGN KEY (`village_code`) REFERENCES `regions` (`code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `type` enum('REPORT','PROOF') NOT NULL DEFAULT 'REPORT',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `attachments_report_id_foreign` (`report_id`),
  CONSTRAINT `attachments_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = <<<'SQL'
CREATE TABLE `status_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `old_status` enum('BARU','DIPROSES','SELESAI','DITOLAK') DEFAULT NULL,
  `new_status` enum('BARU','DIPROSES','SELESAI','DITOLAK') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status_logs_report_id_foreign` (`report_id`),
  KEY `status_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `status_logs_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `status_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    $lines[] = '';
    $lines[] = '-- Regions master';

    foreach (chunkRows($regions, 1000) as $chunk) {
        $values = array_map(static function (array $region): string {
            return sprintf(
                "(%s,%s,%s,%s)",
                sqlValue($region['code']),
                sqlValue($region['name']),
                sqlValue($region['level']),
                sqlValue($region['parent_code']),
            );
        }, $chunk);

        $lines[] = 'INSERT INTO `regions` (`code`, `name`, `level`, `parent_code`) VALUES';
        $lines[] = implode(",\n", $values).';';
        $lines[] = '';
    }

    $lines[] = '-- Migration records';
    $lines[] = <<<'SQL'
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0000_01_01_000000_create_regions_table', 1),
(2, '0001_01_01_000000_create_users_table', 1),
(3, '0001_01_01_000001_create_cache_table', 1),
(4, '0001_01_01_000002_create_jobs_table', 1),
(5, '0001_01_01_000003_create_sessions_table', 1),
(6, '2026_04_17_000100_create_categories_table', 1),
(7, '2026_04_17_000200_create_reports_table', 1),
(8, '2026_04_17_000300_create_attachments_table', 1),
(9, '2026_04_17_000400_create_status_logs_table', 1),
(10, '2026_04_19_020000_generalize_users_region_scope', 1);
SQL;
    $lines[] = '';
    $lines[] = '-- Categories';
    $lines[] = <<<'SQL'
INSERT INTO `categories` (`id`, `name`, `is_active`) VALUES
(1, 'Infrastruktur', 1),
(2, 'Kebersihan', 1),
(3, 'Drainase/Banjir', 1),
(4, 'Keamanan/Ketertiban', 1),
(5, 'Pelayanan Publik', 1),
(6, 'Sosial', 1),
(7, 'Lainnya', 1);
SQL;
    $lines[] = '';
    $lines[] = '-- Initial users';
    $userRows = [sprintf(
        "(1, %s, %s, %s, %s, NULL, '2026-04-18 00:00:00')",
        sqlValue('superadmin'),
        sqlValue($passwordHash),
        sqlValue('Super Admin'),
        sqlValue('SUPER_ADMIN'),
    )];

    $regionsByCode = [];

    foreach ($regions as $region) {
        $regionsByCode[$region['code']] = $region;
    }

    $nextUserId = 2;
    $userIdsByUsername = ['superadmin' => 1];

    foreach ($adminAccounts as $account) {
        $userRows[] = sprintf(
            "(%d, %s, %s, %s, %s, %s, '2026-04-18 00:00:00')",
            $nextUserId++,
            sqlValue($account['username']),
            sqlValue($passwordHash),
            sqlValue($account['account_name']),
            sqlValue($account['role']),
            sqlValue($account['code']),
        );

        $userIdsByUsername[$account['username']] = $nextUserId - 1;
    }

    $lines[] = "INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `region_code`, `created_at`) VALUES\n".implode(",\n", $userRows).';';
    $lines[] = '';

    $categoryIdsByName = [
        'Infrastruktur' => 1,
        'Kebersihan' => 2,
        'Drainase/Banjir' => 3,
        'Keamanan/Ketertiban' => 4,
        'Pelayanan Publik' => 5,
        'Sosial' => 6,
        'Lainnya' => 7,
    ];

    $demoReports = buildDemoReports($districtAdminMap);
    $reportRows = [];
    $statusLogRows = [];
    $baseDate = new DateTimeImmutable(date('Y').'-04-01 09:15:00');

    foreach ($demoReports as $index => $demoReport) {
        $district = $regionsByCode[$demoReport['district_code']] ?? null;
        $regency = $district ? ($regionsByCode[$district['parent_code']] ?? null) : null;
        $province = $regency ? ($regionsByCode[$regency['parent_code']] ?? null) : null;
        $villageCode = firstVillageCodeForDistrict($regions, $demoReport['district_code']);

        if (! $district || ! $regency || ! $province) {
            continue;
        }

        $reportId = $index + 1;
        $createdAt = $baseDate->modify(sprintf('+%d days', $index));
        $updatedAt = $demoReport['status'] === 'BARU'
            ? $createdAt
            : $createdAt->modify('+6 hours');

        $reportRows[] = sprintf(
            "(%d, %s, %s, %s, %d, NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            $reportId,
            sqlValue($demoReport['ticket_code']),
            sqlValue($demoReport['reporter_name']),
            sqlValue($demoReport['phone']),
            $categoryIdsByName[$demoReport['category_name']],
            sqlValue($demoReport['title']),
            sqlValue($demoReport['description']),
            sqlValue($province['code']),
            sqlValue($regency['code']),
            sqlValue($district['code']),
            sqlValue($villageCode),
            sqlValue($demoReport['location_text']),
            sqlValue($demoReport['rt']),
            sqlValue($demoReport['rw']),
            sqlValue($demoReport['status']),
            sqlValue($createdAt->format('Y-m-d H:i:s')),
            sqlValue($updatedAt->format('Y-m-d H:i:s')),
        );

        $statusLogRows[] = sprintf(
            "(%d, %d, NULL, NULL, %s, %s, %s)",
            count($statusLogRows) + 1,
            $reportId,
            sqlValue('BARU'),
            sqlValue('Laporan contoh awal dimasukkan ke sistem.'),
            sqlValue($createdAt->format('Y-m-d H:i:s')),
        );

        if ($demoReport['status'] !== 'BARU') {
            $statusLogRows[] = sprintf(
                "(%d, %d, %d, %s, %s, %s, %s)",
                count($statusLogRows) + 1,
                $reportId,
                $userIdsByUsername[$demoReport['admin_username']] ?? 1,
                sqlValue('BARU'),
                sqlValue($demoReport['status']),
                sqlValue($demoReport['status_note']),
                sqlValue($updatedAt->format('Y-m-d H:i:s')),
            );
        }
    }

    $lines[] = '-- Demo reports';
    $lines[] = "INSERT INTO `reports` (`id`, `ticket_code`, `reporter_name`, `phone`, `category_id`, `other_category`, `title`, `description`, `province_code`, `regency_code`, `district_code`, `village_code`, `location_text`, `rt`, `rw`, `status`, `created_at`, `updated_at`) VALUES\n".implode(",\n", $reportRows).';';
    $lines[] = '';
    $lines[] = '-- Demo status logs';
    $lines[] = "INSERT INTO `status_logs` (`id`, `report_id`, `user_id`, `old_status`, `new_status`, `note`, `changed_at`) VALUES\n".implode(",\n", $statusLogRows).';';
    $lines[] = '';
    $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
    $lines[] = '';

    return implode(PHP_EOL, $lines).PHP_EOL;
}

function buildDemoReports(array $districtAdminMap): array
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
            'admin_username' => $districtAdminMap[$template['code']] ?? null,
            'status_note' => $statusNotes[$status],
        ];
    }

    return $reports;
}

function firstVillageCodeForDistrict(array $regions, string $districtCode): ?string
{
    $villages = array_values(array_filter($regions, static function (array $region) use ($districtCode): bool {
        return $region['level'] === 'VILLAGE' && $region['parent_code'] === $districtCode;
    }));

    usort($villages, static fn (array $left, array $right): int => strcmp($left['name'], $right['name']));

    return $villages[0]['code'] ?? null;
}

function chunkRows(array $rows, int $size): array
{
    return array_chunk($rows, $size);
}

function sqlValue(?string $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    return "'".str_replace("'", "''", $value)."'";
}
