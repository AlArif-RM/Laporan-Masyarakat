<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 18px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 76px; vertical-align: top; }
        .header-logo img { width: 58px; height: 58px; object-fit: contain; }
        .header-copy { text-align: center; }
        .header-copy .org { font-size: 15px; font-weight: 700; }
        .header-copy .unit { font-size: 12px; font-weight: 700; margin-top: 2px; }
        .header-copy .meta { font-size: 10px; color: #475569; margin-top: 4px; }
        .title { font-size: 13px; font-weight: 700; text-transform: uppercase; text-align: center; margin: 0 0 14px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { padding: 4px 0; vertical-align: top; }
        .label { width: 120px; color: #475569; }
        .box { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; color: #334155; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; border: 1px solid #0f172a; }
        .photos { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .photos td { width: 50%; padding: 6px; vertical-align: top; }
        .photo-card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px; }
        .photo-card img { width: 100%; height: 180px; object-fit: cover; border-radius: 6px; }
        .photo-card p { margin: 6px 0 0; font-size: 9px; color: #475569; }
        .footer { margin-top: 28px; text-align: right; font-size: 11px; }
        .signature { margin-top: 54px; font-weight: 700; }
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
                    <div class="meta">{{ $branding['service_name'] }} • Dokumen laporan warga</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">Detail Laporan Masyarakat</div>

    <div class="box">
        <table class="grid">
            <tr>
                <td class="label">Nomor tiket</td>
                <td>{{ $report->ticket_code }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td><span class="status">{{ $statusMeta['label'] }}</span></td>
            </tr>
            <tr>
                <td class="label">Tanggal laporan</td>
                <td>{{ optional($report->created_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Pelapor</td>
                <td>{{ $report->reporter_name ?: 'Anonim' }}</td>
            </tr>
            <tr>
                <td class="label">Kategori</td>
                <td>{{ $report->display_category }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi</td>
                <td>
                    {{ $report->location_text }}
                    @if($report->village || $report->district || $report->regency || $report->province || $report->rt || $report->rw)
                        <br>
                        <span style="color:#475569;">
                            {{ $report->village?->name ? 'Kel/Desa '.$report->village->name : '' }}
                            {{ $report->district?->name ? ' Kec. '.$report->district->name : '' }}
                            {{ $report->regency?->name ? ' '.$report->regency->name : '' }}
                            {{ $report->province?->name ? ' '.$report->province->name : '' }}
                            {{ $report->rt ? ' RT '.$report->rt : '' }}
                            {{ $report->rw ? ' RW '.$report->rw : '' }}
                        </span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="section-title">Deskripsi Masalah</div>
        <div style="white-space: pre-wrap; line-height: 1.5;">{{ $report->description }}</div>
    </div>

    @if($lastLog)
        <div class="box">
            <div class="section-title">Update Terakhir</div>
            <table class="grid">
                <tr>
                    <td class="label">Waktu</td>
                    <td>{{ optional($lastLog->changed_at)->locale(app()->getLocale())->translatedFormat('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td>{{ \App\Models\Report::metaFor($lastLog->new_status)['label'] }}</td>
                </tr>
                <tr>
                    <td class="label">Catatan</td>
                    <td>{{ $lastLog->note ?: '-' }}</td>
                </tr>
            </table>
        </div>
    @endif

    @if($attachments->count() > 0)
        <div class="box">
            <div class="section-title">Lampiran Bukti</div>
            <table class="photos">
                <tr>
                    @foreach($attachments as $index => $attachment)
                        <td>
                            <div class="photo-card">
                                @if($attachment['absolute_path'] && file_exists($attachment['absolute_path']))
                                    <img src="{{ $attachment['absolute_path'] }}" alt="Lampiran laporan {{ $index + 1 }}">
                                @else
                                    <div style="height:180px; display:flex; align-items:center; justify-content:center; background:#f8fafc; border-radius:6px; color:#64748b;">File tidak tersedia</div>
                                @endif
                                <p>{{ $attachment['basename'] }}</p>
                            </div>
                        </td>
                        @if($index % 2 === 1)
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <div>{{ now()->locale(app()->getLocale())->translatedFormat('d F Y') }}</div>
        <div class="signature">{{ $branding['signature'] }}</div>
    </div>
</body>
</html>
