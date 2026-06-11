import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

const steps = [
    {
        title: 'Tulis laporan dengan jelas',
        description: 'Isi judul, kategori, lokasi, dan kronologi masalah agar petugas mudah melakukan tindak lanjut.',
    },
    {
        title: 'Simpan nomor tiket',
        description: 'Setelah laporan terkirim, sistem memberikan kode unik yang dipakai untuk mengecek progres laporan.',
    },
    {
        title: 'Pantau prosesnya',
        description: 'Gunakan menu cek status untuk melihat apakah laporan baru diterima, diproses, selesai, atau ditolak.',
    },
];

export default function Home({ stats }) {
    return (
        <PublicLayout>
            <Head title="Beranda" />

            <section className="overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-600 via-indigo-600 to-slate-900 px-6 py-8 text-white shadow-xl sm:px-8 sm:py-10 lg:px-10">
                <div className="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <div className="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-1 text-sm font-medium text-blue-100">
                            Sistem Laporan Masyarakat
                        </div>
                        <h1 className="mt-5 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl">
                            Aspirasi warga, tindak lanjut lebih cepat, dan status laporan lebih transparan.
                        </h1>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-blue-100/90 sm:text-lg">
                            LapMas membantu warga menyampaikan laporan untuk kebutuhan lingkungan,
                            layanan publik, keamanan, kebersihan, dan isu kecamatan lainnya.
                        </p>

                        <div className="mt-8 flex flex-wrap gap-3">
                            <Link
                                href={route('reports.create')}
                                className="rounded-full bg-white px-6 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50"
                            >
                                Buat laporan baru
                            </Link>
                            <Link
                                href={route('reports.lookup')}
                                className="rounded-full border border-white/20 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/15"
                            >
                                Cek status tiket
                            </Link>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                        <div className="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div className="text-sm text-blue-100/80">Total laporan</div>
                            <div className="mt-2 text-3xl font-semibold">{stats.total_reports}</div>
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div className="text-sm text-blue-100/80">Laporan selesai</div>
                            <div className="mt-2 text-3xl font-semibold">{stats.completed_reports}</div>
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div className="text-sm text-blue-100/80">Kategori aktif</div>
                            <div className="mt-2 text-3xl font-semibold">{stats.active_categories}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mt-6 rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm leading-7 text-amber-900 shadow-sm sm:px-8">
                Isi laporan dengan data yang benar dan dapat dipertanggungjawabkan. Hindari spam,
                fitnah, atau laporan palsu. Simpan nomor tiket setelah pengiriman agar status laporan
                dapat dipantau kapan saja.
            </section>

            <section className="mt-8 grid gap-4 lg:grid-cols-3">
                {steps.map((step, index) => (
                    <article key={step.title} className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-base font-semibold text-blue-700">
                            {index + 1}
                        </div>
                        <h2 className="mt-4 text-lg font-semibold text-slate-900">{step.title}</h2>
                        <p className="mt-3 text-sm leading-7 text-slate-600">{step.description}</p>
                    </article>
                ))}
            </section>
        </PublicLayout>
    );
}
