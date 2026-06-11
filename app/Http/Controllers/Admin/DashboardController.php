<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in(Report::STATUSES)],
            'q' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $counts = collect(Report::query()
            ->visibleToAdmin($user)
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status'));

        $reports = Report::with(['category', 'province', 'regency', 'district', 'village'])
            ->visibleToAdmin($user)
            ->applyAdminFilters($filters)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'filters' => [
                'status' => $filters['status'] ?? '',
                'q' => $filters['q'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
            'counts' => [
                'TOTAL' => (int) $counts->sum(),
                'BARU' => (int) $counts->get('BARU', 0),
                'DIPROSES' => (int) $counts->get('DIPROSES', 0),
                'SELESAI' => (int) $counts->get('SELESAI', 0),
                'DITOLAK' => (int) $counts->get('DITOLAK', 0),
            ],
            'statuses' => collect(Report::STATUSES)->map(fn (string $status) => [
                'value' => $status,
                'label' => Report::metaFor($status)['label'],
            ])->values(),
            'reports' => $reports->map(fn (Report $report) => [
                'id' => $report->id,
                'ticket_code' => $report->ticket_code,
                'title' => $report->title,
                'location_text' => $report->location_text,
                'location_detail' => $report->location_detail,
                'status' => $report->status,
                'status_meta' => Report::metaFor($report->status),
                'category' => $report->display_category,
                'reporter_name' => $report->reporter_name ?: 'Anonim',
                'created_at_human' => $report->created_at?->locale(app()->getLocale())->translatedFormat('d M Y H:i'),
            ])->values(),
        ]);
    }
}
