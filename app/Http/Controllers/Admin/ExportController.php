<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Support\RegionBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $reports = Report::with(['category', 'province', 'regency', 'district', 'village'])
            ->visibleToAdmin($request->user())
            ->applyAdminFilters($filters)
            ->orderByDesc('created_at')
            ->get();

        return response()
            ->view('exports.recap-xls', [
                'reports' => $reports,
            ], 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="rekap_laporan_'.now()->format('Ymd_His').'.xls"',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function pdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $reports = Report::with(['category', 'province', 'regency', 'district', 'village'])
            ->visibleToAdmin($request->user())
            ->applyAdminFilters($filters)
            ->orderByDesc('created_at')
            ->get();

        return Pdf::loadView('pdf.recap', [
            'reports' => $reports,
            'filters' => $filters,
            'branding' => RegionBranding::forAdmin($request->user()),
        ])
            ->setPaper('a4', 'landscape')
            ->download('rekap_laporan_'.now()->format('Ymd_His').'.pdf');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', 'string', Rule::in(Report::STATUSES)],
            'q' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
    }
}
