import AdminLayout from '@/Layouts/AdminLayout';
import InputError from '@/Components/InputError';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const pruneQuery = (values) =>
    Object.fromEntries(Object.entries(values).filter(([, value]) => value !== '' && value !== null));

function PasswordRowForm({ user }) {
    const [open, setOpen] = useState(false);
    const { data, setData, patch, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        patch(route('admin.accounts.password.update', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };

    return (
        <div className="space-y-3">
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className="rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700 transition hover:bg-slate-50"
            >
                {open ? 'Tutup Form' : 'Ubah Password'}
            </button>

            {open && (
                <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Password baru</label>
                        <input
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Konfirmasi password</label>
                        <input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {processing ? 'Menyimpan...' : 'Simpan Password'}
                    </button>
                </form>
            )}
        </div>
    );
}

function PaginationLinks({ links }) {
    return (
        <div className="flex flex-wrap gap-2">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url || '#'}
                    preserveScroll
                    className={`rounded-full px-4 py-2 text-sm font-medium transition ${link.active ? 'bg-slate-900 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'} ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
}

export default function Index({ counts, filters, roles, users }) {
    const { data, setData } = useForm({
        q: filters.q || '',
        role: filters.role || '',
    });

    const submit = (e) => {
        e.preventDefault();
        router.get(route('admin.accounts.index'), pruneQuery(data), {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout>
            <Head title="Akun Wilayah" />

            <div className="space-y-6">
                <section className="rounded-[2rem] bg-white p-6 shadow-sm sm:p-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Super Admin</div>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Akun Wilayah Nasional</h1>
                            <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                                Kelola password akun admin provinsi, kabupaten/kota, kecamatan, dan unduh daftar akun nasional dalam format CSV.
                            </p>
                        </div>

                        <a
                            href={route('admin.accounts.export', pruneQuery(data))}
                            className="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Download CSV Akun
                        </a>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    {[
                        ['TOTAL', 'Total Akun'],
                        ['SUPER_ADMIN', 'Super Admin'],
                        ['ADMIN_PROVINSI', 'Admin Provinsi'],
                        ['ADMIN_KABUPATEN_KOTA', 'Admin Kab/Kota'],
                        ['ADMIN_KECAMATAN', 'Admin Kecamatan'],
                    ].map(([key, label]) => (
                        <article key={key} className="rounded-[1.75rem] bg-white p-5 shadow-sm">
                            <div className="text-sm uppercase tracking-[0.18em] text-slate-500">{label}</div>
                            <div className="mt-3 text-4xl font-semibold text-slate-900">{counts[key]}</div>
                        </article>
                    ))}
                </section>

                <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <form onSubmit={submit} className="grid gap-4 lg:grid-cols-[1fr_240px_auto]">
                        <input
                            type="text"
                            value={data.q}
                            onChange={(e) => setData('q', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Cari username, nama akun, kode wilayah, atau nama wilayah..."
                        />
                        <select
                            value={data.role}
                            onChange={(e) => setData('role', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">Semua role</option>
                            {roles.map((role) => (
                                <option key={role.value} value={role.value}>
                                    {role.label}
                                </option>
                            ))}
                        </select>
                        <div className="flex gap-3 lg:justify-end">
                            <button type="submit" className="rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
                                Terapkan
                            </button>
                            <Link
                                href={route('admin.accounts.index')}
                                className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Reset
                            </Link>
                        </div>
                    </form>
                </section>

                <section className="space-y-4">
                    {users.data.map((user) => (
                        <article key={user.id} className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div className="grid gap-4 lg:grid-cols-[1fr_280px] lg:items-start">
                                <div className="space-y-2">
                                    <div className="text-xs uppercase tracking-[0.18em] text-slate-500">{user.role_label}</div>
                                    <h2 className="text-2xl font-semibold text-slate-900">{user.name}</h2>
                                    <div className="font-mono text-sm text-slate-600">{user.username}</div>
                                    <div className="text-sm text-slate-600">{user.region_label}</div>
                                    {user.region_code && <div className="text-xs uppercase tracking-[0.18em] text-slate-400">Kode wilayah: {user.region_code}</div>}
                                    <div className="text-xs text-slate-400">Dibuat: {user.created_at_human || '-'}</div>
                                </div>

                                <PasswordRowForm user={user} />
                            </div>
                        </article>
                    ))}

                    {users.data.length === 0 && (
                        <section className="rounded-[2rem] border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm">
                            Tidak ada akun yang cocok dengan filter saat ini.
                        </section>
                    )}
                </section>

                <PaginationLinks links={users.links} />
            </div>
        </AdminLayout>
    );
}
