<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background: #f1f5f9; font-weight: bold;">
                <th>No</th>
                <th>Tiket</th>
                <th>Kategori</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Wilayah</th>
                <th>RT</th>
                <th>RW</th>
                <th>Pelapor</th>
                <th>No. HP</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $report->ticket_code }}</td>
                    <td>{{ $report->display_category }}</td>
                    <td>{{ $report->title }}</td>
                    <td>{{ $report->status }}</td>
                    <td>{{ optional($report->created_at)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $report->location_text }}</td>
                    <td>
                        {{ $report->village?->name ? 'Kel/Desa '.$report->village->name : '-' }}
                        {{ $report->district?->name ? ', Kec. '.$report->district->name : '' }}
                        {{ $report->regency?->name ? ', '.$report->regency->name : '' }}
                        {{ $report->province?->name ? ', '.$report->province->name : '' }}
                    </td>
                    <td>{{ $report->rt }}</td>
                    <td>{{ $report->rw }}</td>
                    <td>{{ $report->reporter_name }}</td>
                    <td>{{ $report->phone }}</td>
                    <td>{{ str_replace(["\r\n", "\n", "\r"], ' ', $report->description) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13">Tidak ada data sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
