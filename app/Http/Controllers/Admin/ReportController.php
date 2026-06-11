<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Attachment;
use App\Models\Report;
use App\Models\StatusLog;
use App\Support\LegacyPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function show(Report $report): Response
    {
        $this->abortIfUnauthorized($report);

        $report->load(['category', 'province', 'regency', 'district', 'village', 'attachments', 'statusLogs.user']);

        return Inertia::render('Admin/Reports/Show', [
            'report' => [
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
                'attachments' => $report->attachments->map(fn (Attachment $attachment) => [
                    'id' => $attachment->id,
                    'type' => $attachment->type,
                    'file_path' => LegacyPath::publicUrl($attachment->file_path),
                ])->values(),
                'logs' => $report->statusLogs->map(fn (StatusLog $log) => [
                    'admin_name' => $log->user?->name,
                    'old_status' => $log->old_status,
                    'new_status' => $log->new_status,
                    'status_meta' => Report::metaFor($log->new_status),
                    'note' => $log->note,
                    'changed_at_human' => $log->changed_at?->locale(app()->getLocale())->translatedFormat('d M Y H:i'),
                ])->values(),
            ],
            'statuses' => collect(Report::STATUSES)->map(fn (string $status) => [
                'value' => $status,
                'label' => Report::metaFor($status)['label'],
            ])->values(),
        ]);
    }

    public function update(UpdateReportStatusRequest $request, Report $report): RedirectResponse
    {
        $this->abortIfUnauthorized($report);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $report, $validated): void {
            $oldStatus = $report->status;

            $report->update([
                'status' => $validated['status'],
            ]);

            StatusLog::create([
                'report_id' => $report->id,
                'user_id' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'note' => trim((string) ($validated['note'] ?? '')) ?: null,
            ]);

            if ($request->hasFile('proof')) {
                Attachment::create([
                    'report_id' => $report->id,
                    'type' => 'PROOF',
                    'file_path' => LegacyPath::storeUpload($request->file('proof'), 'proofs'),
                ]);
            }
        });

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Status laporan berhasil diperbarui.');
    }

    private function abortIfUnauthorized(Report $report): void
    {
        abort_unless(request()->user()?->canAccessReport($report), 403);
    }
}
