<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 20px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 70px; vertical-align: top; }
        .header-logo img { width: 54px; height: 54px; object-fit: contain; }
        .header-copy { text-align: center; }
        .header-copy .org { margin: 0; font-size: 16px; font-weight: 700; }
        .header-copy .unit { margin: 2px 0 0; font-size: 12px; font-weight: 700; }
        .header-copy .service { margin: 4px 0 0; color: #475569; }
        h1 { margin: 0 0 4px; font-size: 16px; }
        .meta { margin-bottom: 14px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; }
        th { background: #e2e8f0; text-align: left; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if(filled($branding['logo_path']) && file_exists($branding['logo_path']))
                        <img src="{{ $branding['logo_path'] }}" alt="Logo {{ $branding['organization'] }}">
                    @endif
                </td>
                <td class="header-copy">
                    <div class="org">{{ $branding['organization'] }}</div>
                    <div class="unit">{{ $branding['unit'] }}</div>
                    <div class="service">{{ $branding['service_name'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h1>Rekap Laporan Masyarakat</h1>
    <div class="meta">Dicetak pada {{ now()->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th style="width: 120px;">Tiket</th>
                <th style="width: 150px;">Kategori</th>
                <th>Judul</th>
                <th style="width: 90px;">Status</th>
                <th style="width: 135px;">Tanggal</th>
                <th style="width: 220px;">Lokasi</th>
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
                    <td>{{ optional($report->created_at)->format('d-m-Y H:i') }}</td>
                    <td>
                        {{ $report->location_text }}
                        @if($report->village || $report->district || $report->regency || $report->province || $report->rt || $report->rw)
                            <div class="muted">
                                {{ $report->village?->name ? 'Kel/Desa '.$report->village->name : '' }}
                                {{ $report->district?->name ? ' Kec. '.$report->district->name : '' }}
                                {{ $report->regency?->name ? ' '.$report->regency->name : '' }}
                                {{ $report->province?->name ? ' '.$report->province->name : '' }}
                                {{ $report->rt ? ' RT '.$report->rt : '' }}
                                {{ $report->rw ? ' RW '.$report->rw : '' }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Tidak ada data sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
