import StatusBadge from '@/Components/StatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

const statCards = [
    { key: 'TOTAL', label: 'Total Laporan', accent: 'bg-slate-900 text-white' },
    { key: 'BARU', label: 'Laporan Baru', accent: 'bg-blue-600 text-white' },
    { key: 'DIPROSES', label: 'Diproses', accent: 'bg-amber-400 text-slate-900' },
    { key: 'SELESAI', label: 'Selesai', accent: 'bg-emerald-500 text-white' },
    { key: 'DITOLAK', label: 'Ditolak', accent: 'bg-rose-500 text-white' },
];

const pruneQuery = (values) =>
    Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== '' && value !== null),
    );

export default function Dashboard({ counts, filters, reports, statuses }) {
    const { data, setData, processing } = useForm({
        status: filters.status || '',
        q: filters.q || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const submit = (e) => {
        e.preventDefault();
        router.get(route('admin.dashboard'), pruneQuery(data), {
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(route('admin.dashboard'));
    };

    const exportQuery = pruneQuery(data);

    return (
        <AdminLayout>
            <Head title="Dashboard Admin" />

            <div className="space-y-6">
                <section className="rounded-[2rem] bg-white p-6 shadow-sm sm:p-8">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Ringkasan</div>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Dashboard Laporan Masyarakat</h1>
                            <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Cari laporan berdasarkan tiket, pelapor, lokasi, kategori, rentang tanggal, atau status.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <a
                                href={route('admin.exports.excel', exportQuery)}
                                className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Export Excel
                            </a>
                            <a
                                href={route('admin.exports.pdf', exportQuery)}
                                className="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Export PDF
                            </a>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    {statCards.map((card) => (
                        <article key={card.key} className={`rounded-[1.75rem] p-5 shadow-sm ${card.accent}`}>
                            <div className="text-sm uppercase tracking-[0.18em] opacity-80">{card.label}</div>
                            <div className="mt-3 text-4xl font-semibold">{counts[card.key]}</div>
                        </article>
                    ))}
                </section>

                <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <form onSubmit={submit} className="grid gap-4 lg:grid-cols-[1fr_180px_180px_180px_auto]">
                        <input
                            type="text"
                            value={data.q}
                            onChange={(e) => setData('q', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Cari tiket, judul, lokasi, kategori, pelapor..."
                        />

                        <select
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">Semua status</option>
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>

                        <input
                            type="date"
                            value={data.date_from}
                            onChange={(e) => setData('date_from', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />

                        <input
                            type="date"
                            value={data.date_to}
                            onChange={(e) => setData('date_to', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />

                        <div className="flex gap-3 lg:justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Terapkan
                            </button>
                            <button
                                type="button"
                                onClick={resetFilters}
                                className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </section>

                <section className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-5 sm:px-8">
                        <h2 className="text-xl font-semibold text-slate-900">Daftar Laporan</h2>
                        <p className="mt-1 text-sm text-slate-500">Maksimal 50 laporan terbaru sesuai filter aktif.</p>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr className="text-left text-xs uppercase tracking-[0.18em] text-slate-500">
                                    <th className="px-6 py-4 font-semibold sm:px-8">Tiket</th>
                                    <th className="px-6 py-4 font-semibold">Judul</th>
                                    <th className="px-6 py-4 font-semibold">Kategori</th>
                                    <th className="px-6 py-4 font-semibold">Status</th>
                                    <th className="px-6 py-4 font-semibold">Pelapor</th>
                                    <th className="px-6 py-4 font-semibold">Tanggal</th>
                                    <th className="px-6 py-4 font-semibold"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {reports.map((report) => (
                                    <tr key={report.id} className="align-top text-sm text-slate-700">
                                        <td className="px-6 py-5 font-mono font-semibold text-slate-900 sm:px-8">{report.ticket_code}</td>
                                        <td className="px-6 py-5">
                                            <div className="font-semibold text-slate-900">{report.title}</div>
                                            <div className="mt-1 text-xs leading-6 text-slate-500">{report.location_text}</div>
                                            {report.location_detail && (
                                                <div className="text-xs leading-6 text-slate-400">{report.location_detail}</div>
                                            )}
                                        </td>
                                        <td className="px-6 py-5 text-slate-600">{report.category}</td>
                                        <td className="px-6 py-5">
                                            <StatusBadge meta={report.status_meta} />
                                        </td>
                                        <td className="px-6 py-5 text-slate-600">{report.reporter_name}</td>
                                        <td className="px-6 py-5 text-slate-600">{report.created_at_human}</td>
                                        <td className="px-6 py-5 text-right">
                                            <Link
                                                href={route('admin.reports.show', report.id)}
                                                className="rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                ))}

                                {reports.length === 0 && (
                                    <tr>
                                        <td colSpan="7" className="px-6 py-10 text-center text-sm text-slate-500 sm:px-8">
                                            Tidak ada laporan yang cocok dengan filter saat ini.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
