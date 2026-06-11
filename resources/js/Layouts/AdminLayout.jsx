import FlashBanner from '@/Components/FlashBanner';
import { Link, usePage } from '@inertiajs/react';

export default function AdminLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    return (
        <div className="min-h-screen bg-slate-100">
            <header className="border-b border-slate-200 bg-slate-950 text-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <Link href={route('admin.dashboard')} className="text-lg font-semibold tracking-tight text-white">
                            Dashboard LapMas
                        </Link>
                        <p className="text-sm text-slate-300">Kelola verifikasi, tindak lanjut, dan rekap laporan warga.</p>
                        {user?.region?.label && <p className="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">{user.region.label}</p>}
                    </div>

                    <div className="flex flex-wrap items-center gap-3 text-sm">
                        <Link
                            href={route('admin.dashboard')}
                            className="rounded-full border border-white/10 bg-white/5 px-4 py-2 font-medium text-white transition hover:bg-white/10"
                        >
                            Dashboard
                        </Link>
                        {user?.role === 'SUPER_ADMIN' && (
                            <>
                                <Link
                                    href={route('admin.accounts.index')}
                                    className="rounded-full border border-white/10 bg-white/5 px-4 py-2 font-medium text-white transition hover:bg-white/10"
                                >
                                    Akun Wilayah
                                </Link>
                                <Link
                                    href={route('admin.logos.index')}
                                    className="rounded-full border border-white/10 bg-white/5 px-4 py-2 font-medium text-white transition hover:bg-white/10"
                                >
                                    Logo Wilayah
                                </Link>
                            </>
                        )}
                        <div className="rounded-full border border-white/10 px-4 py-2 text-slate-200">
                            {user?.name} ({user?.username})
                            {user?.role_label ? ` • ${user.role_label}` : ''}
                        </div>
                        <Link
                            href={route('admin.logout')}
                            method="post"
                            as="button"
                            className="rounded-full bg-rose-500 px-4 py-2 font-medium text-white transition hover:bg-rose-400"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <FlashBanner />
                {children}
            </main>
        </div>
    );
}
