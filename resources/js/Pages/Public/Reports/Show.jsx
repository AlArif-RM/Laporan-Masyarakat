import StatusBadge from '@/Components/StatusBadge';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ report, lookup }) {
    const pdfLink = lookup.phone
        ? `${route('reports.pdf', report.ticket_code)}?phone=${encodeURIComponent(lookup.phone)}`
        : route('reports.pdf', report.ticket_code);

    return (
        <PublicLayout>
            <Head title={`Tiket ${report.ticket_code}`} />

            <div className="space-y-6">
                <section className="overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-xl">
                    <div className="grid gap-6 px-6 py-8 sm:px-8 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <div className="text-sm uppercase tracking-[0.2em] text-blue-100/70">Nomor tiket</div>
                            <h1 className="mt-2 break-all font-mono text-3xl font-semibold sm:text-4xl">{report.ticket_code}</h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-blue-100/90 sm:text-base">{report.title}</p>
                        </div>

                        <div className="space-y-3 rounded-[1.75rem] border border-white/10 bg-white/10 p-5 backdrop-blur lg:min-w-[260px]">
                            <StatusBadge meta={report.status_meta} className="bg-white/90 text-slate-900 ring-white/80" />
                            <div className="text-sm text-blue-100/80">Terakhir diperbarui</div>
                            <div className="text-lg font-semibold">{report.updated_at_human}</div>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <h2 className="text-xl font-semibold text-slate-900">Informasi Laporan</h2>

                        <dl className="mt-6 grid gap-5 sm:grid-cols-2">
                            <div className="sm:col-span-2">
                                <dt className="text-sm font-medium text-slate-500">Kategori</dt>
                                <dd className="mt-2 text-base font-semibold text-slate-900">{report.category}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-slate-500">Tanggal masuk</dt>
                                <dd className="mt-2 text-base text-slate-900">{report.created_at_human}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-slate-500">Pelapor</dt>
                                <dd className="mt-2 text-base text-slate-900">{report.reporter_name || 'Anonim'}</dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-sm font-medium text-slate-500">Lokasi</dt>
                                <dd className="mt-2 rounded-2xl bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-700">
                                    <div className="font-semibold text-slate-900">{report.location_text}</div>
                                    {(report.village || report.district || report.regency || report.province) && (
                                        <div className="mt-1 text-slate-500">
                                            {report.village ? `Kel/Desa ${report.village}` : null}
                                            {report.district ? `, Kec. ${report.district}` : null}
                                            {report.regency ? `, ${report.regency}` : null}
                                            {report.province ? `, ${report.province}` : null}
                                        </div>
                                    )}
                                    {(report.rt || report.rw) && <div className="mt-1 text-slate-500">{report.rt ? `RT ${report.rt}` : null}{report.rw ? ` RW ${report.rw}` : null}</div>}
                                </dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-sm font-medium text-slate-500">Deskripsi</dt>
                                <dd className="mt-2 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-700 whitespace-pre-wrap">
                                    {report.description}
                                </dd>
                            </div>
                        </dl>

                        {report.attachments.length > 0 && (
                            <div className="mt-8">
                                <h3 className="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Lampiran Bukti</h3>
                                <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {report.attachments.map((attachment) => (
                                        <a
                                            key={attachment.id}
                                            href={attachment.file_path}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 transition hover:-translate-y-0.5 hover:shadow-md"
                                        >
                                            <img src={attachment.file_path} alt="Lampiran laporan" className="h-44 w-full object-cover" />
                                        </a>
                                    ))}
                                </div>
                            </div>
                        )}
                    </section>

                    <aside className="space-y-6">
                        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Riwayat Proses</h2>
                            <div className="mt-6 space-y-4">
                                {report.logs.map((log, index) => (
                                    <div key={`${log.changed_at_human}-${index}`} className="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                                        <div className="flex flex-wrap items-center justify-between gap-3">
                                            <StatusBadge meta={log.status_meta} />
                                            <div className="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">{log.changed_at_human}</div>
                                        </div>
                                        {log.note && <p className="mt-3 text-sm leading-7 text-slate-600">{log.note}</p>}
                                        {log.old_status && (
                                            <p className="mt-2 text-xs text-slate-500">Status sebelumnya: {log.old_status}</p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <h2 className="text-xl font-semibold text-slate-900">Aksi</h2>
                            <div className="mt-5 flex flex-col gap-3">
                                <Link
                                    href={route('reports.lookup')}
                                    className="rounded-full border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Cek tiket lain
                                </Link>
                                <a
                                    href={pdfLink}
                                    className="rounded-full bg-blue-600 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-500"
                                >
                                    Download PDF
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </PublicLayout>
    );
}
