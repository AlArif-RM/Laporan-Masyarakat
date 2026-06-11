import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

export default function Success({ ticketCode }) {
    return (
        <PublicLayout>
            <Head title="Laporan Terkirim" />

            <div className="mx-auto max-w-3xl rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-sm sm:p-8">
                <div className="rounded-[1.75rem] bg-gradient-to-br from-emerald-500 to-teal-600 px-6 py-7 text-white">
                    <h1 className="text-3xl font-semibold">Laporan berhasil dikirim</h1>
                    <p className="mt-3 text-sm leading-7 text-emerald-50/90 sm:text-base">
                        Simpan nomor tiket berikut untuk memantau perkembangan laporan Anda.
                    </p>
                </div>

                <div className="mt-6 rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center">
                    <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Nomor tiket</div>
                    <div className="mt-3 break-all font-mono text-3xl font-semibold text-slate-900 sm:text-4xl">{ticketCode}</div>
                </div>

                <div className="mt-6 flex flex-wrap justify-center gap-3">
                    <Link
                        href={route('reports.show', ticketCode)}
                        className="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Lihat detail tiket
                    </Link>
                    <Link
                        href={route('reports.lookup', { ticket: ticketCode })}
                        className="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Buka halaman cek status
                    </Link>
                </div>
            </div>
        </PublicLayout>
    );
}
