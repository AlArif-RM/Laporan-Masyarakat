<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Region;
use App\Models\Report;
use App\Models\StatusLog;
use App\Support\LegacyPath;
use App\Support\RegionBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PublicReportController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Public/Home', [
            'stats' => [
                'total_reports' => Report::count(),
                'completed_reports' => Report::where('status', 'SELESAI')->count(),
                'active_categories' => Category::active()->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Public/Reports/Create', [
            'categories' => Category::active()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'is_other' => $category->isOther(),
                ])
                ->values(),
            'regionRoutes' => [
                'provinces' => route('api.regions.provinces'),
                'regencies' => route('api.regions.regencies'),
                'districts' => route('api.regions.districts'),
                'villages' => route('api.regions.villages'),
            ],
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $category = Category::active()->findOrFail($validated['category_id']);
        $regions = $this->resolveSelectedRegions($validated);

        if ($category->isOther() && blank($validated['other_category'] ?? null)) {
            return back()
                ->withErrors(['other_category' => 'Kategori lainnya wajib diisi.'])
                ->withInput();
        }

        $report = DB::transaction(function () use ($validated, $regions): Report {
            $report = Report::create([
                'ticket_code' => Report::nextTicketCode(),
                'reporter_name' => $this->nullableString($validated['reporter_name'] ?? null),
                'phone' => $this->nullableString($validated['phone'] ?? null),
                'category_id' => $validated['category_id'],
                'other_category' => $this->nullableString($validated['other_category'] ?? null),
                'title' => trim($validated['title']),
                'description' => trim($validated['description']),
                'province_code' => $regions['province']->code,
                'regency_code' => $regions['regency']->code,
                'district_code' => $regions['district']->code,
                'village_code' => $regions['village']?->code,
                'location_text' => trim($validated['location_text']),
                'rt' => $this->nullableString($validated['rt'] ?? null),
                'rw' => $this->nullableString($validated['rw'] ?? null),
                'status' => 'BARU',
            ]);

            StatusLog::create([
                'report_id' => $report->id,
                'user_id' => null,
                'old_status' => null,
                'new_status' => 'BARU',
                'note' => 'Laporan dibuat oleh warga',
            ]);

            return $report;
        });

        $warning = null;

        if ($request->hasFile('photo')) {
            $warning = $this->attachReportPhoto($report, $request->file('photo'));
        }

        $redirect = redirect()
            ->route('reports.success', ['ticket' => $report->ticket_code])
            ->with('success', 'Laporan berhasil dikirim. Simpan nomor tiket Anda.');

        if ($warning !== null) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function success(Request $request): Response|RedirectResponse
    {
        $ticket = trim((string) $request->query('ticket', ''));

        if ($ticket === '') {
            return redirect()->route('home');
        }

        return Inertia::render('Public/Reports/Success', [
            'ticketCode' => $ticket,
        ]);
    }

    public function lookup(Request $request): Response
    {
        return Inertia::render('Public/Reports/Lookup', [
            'filters' => [
                'ticket' => (string) $request->query('ticket', ''),
                'phone' => (string) $request->query('phone', ''),
            ],
        ]);
    }

    public function show(Request $request, string $ticketCode): Response|RedirectResponse
    {
        $phone = trim((string) $request->query('phone', ''));

        $report = Report::with(['category', 'province', 'regency', 'district', 'village', 'attachments', 'statusLogs'])
            ->where('ticket_code', $ticketCode)
            ->when($phone !== '', fn ($query) => $query->where('phone', $phone))
            ->first();

        if (! $report) {
            return redirect()
                ->route('reports.lookup', ['ticket' => $ticketCode, 'phone' => $phone])
                ->with('error', 'Nomor tiket tidak ditemukan atau nomor HP tidak sesuai.');
        }

        return Inertia::render('Public/Reports/Show', [
            'report' => $this->serializeReport($report),
            'lookup' => [
                'phone' => $phone,
            ],
        ]);
    }

    public function pdf(Request $request, string $ticketCode)
    {
        $phone = trim((string) $request->query('phone', ''));

        $report = Report::with([
            'category',
            'province',
            'regency',
            'district',
            'village',
            'attachments' => fn ($query) => $query->orderBy('id')->limit(6),
            'statusLogs' => fn ($query) => $query->orderByDesc('changed_at')->orderByDesc('id')->limit(1),
        ])
            ->where('ticket_code', $ticketCode)
            ->when($phone !== '', fn ($query) => $query->where('phone', $phone))
            ->firstOrFail();

        $attachments = $report->attachments->map(fn (Attachment $attachment) => [
            'file_path' => $attachment->file_path,
            'absolute_path' => LegacyPath::absoluteFromPublic($attachment->file_path),
            'basename' => basename($attachment->file_path),
        ])->values();

        return Pdf::loadView('pdf.report', [
            'report' => $report,
            'statusMeta' => Report::metaFor($report->status),
            'lastLog' => $report->statusLogs->first(),
            'attachments' => $attachments,
            'branding' => RegionBranding::forRegion($report->district),
        ])
            ->setPaper('a4', 'portrait')
            ->download('laporan-'.$report->ticket_code.'.pdf');
    }

    private function serializeReport(Report $report): array
    {
        return [
            'id' => $report->id,
            'ticket_code' => $report->ticket_code,
            'title' => $report->title,
            'description' => $report->description,
            'reporter_name' => $report->reporter_name,
            'phone' => $report->phone,
            'location_text' => $report->location_text,
            'province' => $report->province?->name,
            'regency' => $report->regency?->name,
            'district' => $report->district?->name,
            'village' => $report->village?->name,
            'location_detail' => $report->location_detail,
            'rt' => $report->rt,
            'rw' => $report->rw,
            'status' => $report->status,
            'status_meta' => Report::metaFor($report->status),
            'category' => $report->display_category,
            'created_at_human' => $report->created_at?->locale(app()->getLocale())->translatedFormat('d F Y, H:i'),
            'updated_at_human' => ($report->updated_at ?? $report->created_at)?->locale(app()->getLocale())->translatedFormat('d F Y, H:i'),
            'attachments' => $report->attachments->map(fn (Attachment $attachment) => [
                'id' => $attachment->id,
                'type' => $attachment->type,
                'file_path' => LegacyPath::publicUrl($attachment->file_path),
            ])->values(),
            'logs' => $report->statusLogs->map(fn (StatusLog $log) => [
                'old_status' => $log->old_status,
                'new_status' => $log->new_status,
                'status_meta' => Report::metaFor($log->new_status),
                'note' => $log->note,
                'changed_at_human' => $log->changed_at?->locale(app()->getLocale())->translatedFormat('d M Y H:i'),
            ])->values(),
        ];
    }

    private function nullableString(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    private function attachReportPhoto(Report $report, UploadedFile $photo): ?string
    {
        $validator = Validator::make(
            ['photo' => $photo],
            ['photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240']],
        );

        if ($validator->fails()) {
            return 'Laporan berhasil dikirim, tetapi foto bukti hanya mendukung format JPG, JPEG, PNG, atau WEBP dengan ukuran maksimal 10 MB.';
        }

        $filePath = null;

        try {
            $filePath = LegacyPath::storeUpload($photo, 'reports');

            Attachment::create([
                'report_id' => $report->id,
                'type' => 'REPORT',
                'file_path' => $filePath,
            ]);

            return null;
        } catch (\Throwable $exception) {
            if ($filePath !== null) {
                LegacyPath::deleteUpload($filePath);
            }

            report($exception);

            return 'Laporan berhasil dikirim, tetapi foto bukti gagal diunggah sehingga tidak ikut tersimpan.';
        }
    }

    private function resolveSelectedRegions(array $validated): array
    {
        $province = Region::query()
            ->byLevel(Region::LEVEL_PROVINCE)
            ->findOrFail($validated['province_code']);

        $regency = Region::query()
            ->byLevel(Region::LEVEL_REGENCY)
            ->findOrFail($validated['regency_code']);

        if ($regency->parent_code !== $province->code) {
            throw ValidationException::withMessages([
                'regency_code' => 'Kabupaten/kota tidak sesuai dengan provinsi yang dipilih.',
            ]);
        }

        $district = Region::query()
            ->byLevel(Region::LEVEL_DISTRICT)
            ->findOrFail($validated['district_code']);

        if ($district->parent_code !== $regency->code) {
            throw ValidationException::withMessages([
                'district_code' => 'Kecamatan tidak sesuai dengan kabupaten/kota yang dipilih.',
            ]);
        }

        $village = null;

        if (filled($validated['village_code'] ?? null)) {
            $village = Region::query()
                ->byLevel(Region::LEVEL_VILLAGE)
                ->findOrFail($validated['village_code']);

            if ($village->parent_code !== $district->code) {
                throw ValidationException::withMessages([
                    'village_code' => 'Desa/kelurahan tidak sesuai dengan kecamatan yang dipilih.',
                ]);
            }
        }

        return [
            'province' => $province,
            'regency' => $regency,
            'district' => $district,
            'village' => $village,
        ];
    }
}
