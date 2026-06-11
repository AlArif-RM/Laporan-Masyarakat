import FlashBanner from '@/Components/FlashBanner';
import { Link } from '@inertiajs/react';

const links = [
    { href: route('home'), label: 'Beranda' },
    { href: route('reports.create'), label: 'Buat Laporan' },
    { href: route('reports.lookup'), label: 'Cek Status' },
];

export default function PublicLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-100">
            <header className="border-b border-slate-200 bg-white/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <Link href={route('home')} className="text-lg font-semibold tracking-tight text-slate-900">
                            LapMas Kecamatan
                        </Link>
                        <p className="text-sm text-slate-500">Layanan aspirasi dan laporan warga berbasis Laravel + React.</p>
                    </div>

                    <nav className="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-600">
                        {links.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </nav>
                </div>
            </header>

            <main className="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                <FlashBanner />
                {children}
            </main>

            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <p>LapMas membantu warga menyampaikan laporan dengan nomor tiket yang bisa dipantau mandiri.</p>
                    <p>Gunakan layanan ini secara bijak dan sampaikan laporan yang dapat dipertanggungjawabkan.</p>
                </div>
            </footer>
        </div>
    );
}
