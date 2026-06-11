import FlashBanner from '@/Components/FlashBanner';
import InputError from '@/Components/InputError';
import { Head, Link, useForm } from '@inertiajs/react';

const securityHighlights = [
    {
        title: 'Akses Terkendali',
        description: 'Hak akses dashboard otomatis menyesuaikan wilayah dan kewenangan akun admin.',
    },
    {
        title: 'Tindak Lanjut Terpusat',
        description: 'Verifikasi laporan, pembaruan status, dan rekap berjalan dari satu panel yang sama.',
    },
    {
        title: 'Dokumen Resmi',
        description: 'Dokumen rekap dan PDF mengikuti identitas wilayah yang dikelola di sistem.',
    },
];

export default function Login({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        username: '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Login Admin" />

            <div className="relative min-h-screen overflow-hidden bg-slate-950 text-white">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.26),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(99,102,241,0.18),_transparent_25%)]" />
                <div className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

                <div className="relative mx-auto grid min-h-screen max-w-7xl items-center gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:py-10">
                    <section className="lg:pr-4">
                        <div className="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur sm:p-8 lg:p-9">
                            <div className="inline-flex rounded-full border border-cyan-300/20 bg-cyan-400/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-100">
                                Portal Admin Wilayah Nasional
                            </div>

                            <h1 className="mt-5 max-w-2xl text-4xl font-semibold leading-tight tracking-tight text-white sm:text-[2.8rem]">
                                Kelola laporan masyarakat dari panel admin yang lebih terpusat dan rapi.
                            </h1>

                            <p className="mt-4 max-w-xl text-sm leading-7 text-slate-200 sm:text-base">
                                Masuk menggunakan akun admin resmi untuk memverifikasi laporan, memantau tindak lanjut, dan mengelola dokumen wilayah sesuai akses Anda.
                            </p>

                            <div className="mt-8 space-y-3">
                                {securityHighlights.map((item) => (
                                    <article key={item.title} className="rounded-[1.35rem] border border-white/10 bg-slate-900/35 px-5 py-4">
                                        <h2 className="text-sm font-semibold uppercase tracking-[0.18em] text-blue-100">{item.title}</h2>
                                        <p className="mt-2 text-sm leading-6 text-slate-200">{item.description}</p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    </section>

                    <div className="flex items-center justify-center lg:justify-end">
                        <div className="w-full max-w-2xl rounded-[2rem] border border-slate-200/80 bg-white p-6 text-slate-900 shadow-2xl shadow-slate-950/30 sm:p-8 lg:p-10">
                            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div className="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Akses Operator</div>
                                    <h2 className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Login Admin</h2>
                                    <p className="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                                        Masuk menggunakan akun admin yang sah untuk mengakses dashboard sesuai kewenangan wilayah Anda.
                                    </p>
                                </div>

                                <Link href={route('home')} className="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Kembali ke beranda
                                </Link>
                            </div>

                            <FlashBanner />

                            {status && (
                                <div className="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} className="space-y-5">
                                <div>
                                    <label htmlFor="username" className="block text-sm font-medium text-slate-700">
                                        Username admin wilayah
                                    </label>
                                    <input
                                        id="username"
                                        type="text"
                                        name="username"
                                        value={data.username}
                                        autoComplete="username"
                                        autoFocus
                                        onChange={(e) => setData('username', e.target.value)}
                                        placeholder="Masukkan username admin"
                                        className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    />
                                    <InputError message={errors.username} className="mt-2" />
                                    <p className="mt-2 text-xs leading-5 text-slate-500">
                                        Gunakan username akun yang diberikan oleh pengelola sistem.
                                    </p>
                                </div>

                                <div>
                                    <div className="flex items-center justify-between gap-3">
                                        <label htmlFor="password" className="block text-sm font-medium text-slate-700">
                                            Password
                                        </label>
                                        <span className="text-xs uppercase tracking-[0.18em] text-slate-400">Rahasia</span>
                                    </div>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        value={data.password}
                                        autoComplete="current-password"
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Masukkan password akun wilayah"
                                        className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    />
                                    <InputError message={errors.password} className="mt-2" />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {processing ? 'Memproses login...' : 'Masuk ke dashboard admin'}
                                </button>
                            </form>

                            <div className="mt-6 grid gap-3 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600 sm:grid-cols-2">
                                <div>
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Untuk operator wilayah</div>
                                    <p className="mt-2 leading-6">Pastikan Anda menggunakan akun resmi yang diberikan pengelola sistem dan jangan membagikan kredensial kepada pihak lain.</p>
                                </div>
                                <div>
                                    <div className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Butuh akses publik?</div>
                                    <p className="mt-2 leading-6">
                                        Gunakan menu <Link href={route('reports.lookup')} className="font-semibold text-blue-600 hover:text-blue-700">Cek Status</Link> atau <Link href={route('reports.create')} className="font-semibold text-blue-600 hover:text-blue-700">Buat Laporan</Link> dari halaman publik.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
