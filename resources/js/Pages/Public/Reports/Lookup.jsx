import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Lookup({ filters }) {
    const { data, setData, processing } = useForm({
        ticket: filters.ticket || '',
        phone: filters.phone || '',
    });

    const submit = (e) => {
        e.preventDefault();

        const ticket = data.ticket.trim().toUpperCase();
        if (!ticket) {
            return;
        }

        router.get(
            route('reports.show', ticket),
            data.phone ? { phone: data.phone } : {},
            { preserveState: false },
        );
    };

    return (
        <PublicLayout>
            <Head title="Cek Status" />

            <div className="mx-auto max-w-2xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div className="rounded-[1.75rem] bg-gradient-to-br from-blue-600 to-indigo-700 px-6 py-6 text-white">
                    <h1 className="text-3xl font-semibold">Cek Status Laporan</h1>
                    <p className="mt-3 text-sm leading-7 text-blue-100/90 sm:text-base">
                        Masukkan nomor tiket Anda. Jika saat pelaporan mengisi nomor HP, tambahkan juga untuk verifikasi tambahan.
                    </p>
                </div>

                <form onSubmit={submit} className="mt-6 space-y-5">
                    <div>
                        <label className="block text-sm font-medium text-slate-700">Nomor tiket</label>
                        <input
                            type="text"
                            value={data.ticket}
                            onChange={(e) => setData('ticket', e.target.value.toUpperCase().replace(/\s+/g, ''))}
                            className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="LM-2026-000001"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">No. HP (opsional)</label>
                        <input
                            type="text"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value.replace(/[^0-9+]/g, ''))}
                            className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="0812xxxx"
                        />
                    </div>

                    <div className="flex flex-wrap gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Mencari...' : 'Cari laporan'}
                        </button>
                        <Link
                            href={route('home')}
                            className="rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Kembali ke beranda
                        </Link>
                    </div>
                </form>
            </div>
        </PublicLayout>
    );
}
