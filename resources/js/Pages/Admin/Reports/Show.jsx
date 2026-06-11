import InputError from '@/Components/InputError';
import StatusBadge from '@/Components/StatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ report, statuses }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'patch',
        status: report.status,
        note: '',
        proof: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.reports.update', report.id), { forceFormData: true });
    };

    return (
        <AdminLayout>
            <Head title={`Detail ${report.ticket_code}`} />

            <div className="space-y-6">
                <section className="rounded-[2rem] bg-white p-6 shadow-sm sm:p-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Tiket laporan</div>
                            <h1 className="mt-2 break-all font-mono text-3xl font-semibold text-slate-900">{report.ticket_code}</h1>
                            <p className="mt-4 max-w-3xl text-base font-semibold text-slate-900">{report.title}</p>
                            <p className="mt-2 text-sm leading-7 text-slate-500">Masuk pada {report.created_at_human}</p>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <StatusBadge meta={report.status_meta} className="text-sm" />
                            <Link
                                href={route('admin.dashboard')}
                                className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Kembali ke dashboard
                            </Link>
                            <a
                                href={route('reports.pdf', report.ticket_code)}
                                className="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Download PDF
                            </a>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <section className="space-y-6">
                        <article className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Informasi Laporan</h2>

                            <dl className="mt-6 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <dt className="text-sm font-medium text-slate-500">Kategori</dt>
                                    <dd className="mt-2 text-base font-semibold text-slate-900">{report.category}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-slate-500">Pelapor</dt>
                                    <dd className="mt-2 text-base text-slate-900">{report.reporter_name || 'Anonim'}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-slate-500">No. HP</dt>
                                    <dd className="mt-2 text-base text-slate-900">{report.phone || '-'}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-slate-500">Lokasi</dt>
                                    <dd className="mt-2 text-base text-slate-900">{report.location_text}</dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-slate-500">Wilayah administrasi</dt>
                                    <dd className="mt-2 text-base text-slate-900">
                                        {report.village ? `Kel/Desa ${report.village}` : '-'}
                                        {report.district ? `, Kec. ${report.district}` : ''}
                                        {report.regency ? `, ${report.regency}` : ''}
                                        {report.province ? `, ${report.province}` : ''}
                                    </dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-slate-500">Detail wilayah</dt>
                                    <dd className="mt-2 text-base text-slate-900">{report.location_detail || '-'}</dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-slate-500">Deskripsi</dt>
                                    <dd className="mt-2 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-700 whitespace-pre-wrap">
                                        {report.description}
                                    </dd>
                                </div>
                            </dl>
                        </article>

                        <article className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Lampiran</h2>

                            {report.attachments.length > 0 ? (
                                <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    {report.attachments.map((attachment) => (
                                        <a
                                            key={attachment.id}
                                            href={attachment.file_path}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 transition hover:-translate-y-0.5 hover:shadow-md"
                                        >
                                            <img src={attachment.file_path} alt="Lampiran laporan" className="h-44 w-full object-cover" />
                                            <div className="flex items-center justify-between px-4 py-3 text-xs uppercase tracking-[0.18em] text-slate-500">
                                                <span>{attachment.type}</span>
                                                <span>Lihat</span>
                                            </div>
                                        </a>
                                    ))}
                                </div>
                            ) : (
                                <p className="mt-4 text-sm text-slate-500">Belum ada lampiran pada laporan ini.</p>
                            )}
                        </article>
                    </section>

                    <aside className="space-y-6">
                        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Update Status</h2>
                            <form onSubmit={submit} className="mt-6 space-y-5">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Status baru</label>
                                    <select
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value)}
                                        className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    >
                                        {statuses.map((status) => (
                                            <option key={status.value} value={status.value}>
                                                {status.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} className="mt-2" />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Catatan admin</label>
                                    <textarea
                                        rows="4"
                                        value={data.note}
                                        onChange={(e) => setData('note', e.target.value)}
                                        className="mt-2 block w-full rounded-3xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                        placeholder="Tambahkan catatan singkat untuk riwayat proses"
                                    />
                                    <InputError message={errors.note} className="mt-2" />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Upload bukti tindak lanjut</label>
                                    <input
                                        type="file"
                                        accept=".jpg,.jpeg,.png"
                                        onChange={(e) => setData('proof', e.target.files[0] || null)}
                                        className="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm outline-none transition file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                                    />
                                    <InputError message={errors.proof} className="mt-2" />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan pembaruan'}
                                </button>
                            </form>
                        </section>

                        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Riwayat Proses</h2>
                            <div className="mt-6 space-y-4">
                                {report.logs.map((log, index) => (
                                    <article key={`${log.changed_at_human}-${index}`} className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                                        <div className="flex flex-wrap items-center justify-between gap-3">
                                            <StatusBadge meta={log.status_meta} />
                                            <div className="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">{log.changed_at_human}</div>
                                        </div>
                                        {log.admin_name && <p className="mt-3 text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Oleh {log.admin_name}</p>}
                                        {log.note && <p className="mt-3 text-sm leading-7 text-slate-600">{log.note}</p>}
                                        {log.old_status && (
                                            <p className="mt-2 text-xs text-slate-500">Status sebelumnya: {log.old_status}</p>
                                        )}
                                    </article>
                                ))}
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </AdminLayout>
    );
}
