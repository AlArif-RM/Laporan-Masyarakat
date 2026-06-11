import AdminLayout from '@/Layouts/AdminLayout';
import InputError from '@/Components/InputError';
import { Head, Link, router, useForm } from '@inertiajs/react';

const pruneQuery = (values) =>
    Object.fromEntries(Object.entries(values).filter(([, value]) => value !== '' && value !== null));

function ZipImportForm() {
    const { data, setData, post, processing, errors, reset } = useForm({
        archive: null,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.logos.import'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => reset('archive'),
        });
    };

    return (
        <form onSubmit={submit} className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Import Massal</div>
                    <h2 className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Upload ZIP Logo Wilayah</h2>
                    <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        Gunakan file ZIP berisi logo wilayah dengan nama file sesuai kode wilayah. Anda bisa menyusun file di dalam folder `provinces/`, `regencies/`, `districts/`, atau langsung di root ZIP.
                    </p>
                    <div className="mt-3 rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        Contoh isi ZIP: <code className="font-mono">provinces/12.png</code>, <code className="font-mono">regencies/12.07.png</code>, <code className="font-mono">districts/12.07.26.png</code>
                    </div>
                </div>

                <div className="w-full max-w-md space-y-3">
                    <input
                        type="file"
                        accept=".zip"
                        onChange={(e) => setData('archive', e.target.files?.[0] || null)}
                        className="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    />
                    <InputError message={errors.archive} />
                    <button
                        type="submit"
                        disabled={processing || !data.archive}
                        className="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {processing ? 'Mengimpor...' : 'Import ZIP Logo'}
                    </button>
                </div>
            </div>
        </form>
    );
}

function LogoRowForm({ region }) {
    const { data, setData, post, processing, errors, reset, delete: destroy } = useForm({
        region_code: region.code,
        logo: null,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.logos.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => reset('logo'),
        });
    };

    const remove = () => {
        destroy(route('admin.logos.destroy', region.code), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div className="flex items-center gap-4">
                <div className="h-16 w-16 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    {region.logo_url ? (
                        <img src={region.logo_url} alt={region.name} className="h-full w-full object-contain p-2" />
                    ) : (
                        <div className="flex h-full w-full items-center justify-center text-[10px] uppercase tracking-[0.18em] text-slate-400">No Logo</div>
                    )}
                </div>
                <div>
                    <div className="text-xs uppercase tracking-[0.18em] text-slate-500">{region.level_label}</div>
                    <div className="font-semibold text-slate-900">{region.name}</div>
                    <div className="text-xs text-slate-500">{region.code}{region.parent_name ? ` • ${region.parent_name}` : ''}</div>
                </div>
            </div>

            <div>
                <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">File logo</label>
                <input
                    type="file"
                    accept=".png,.jpg,.jpeg,.webp"
                    onChange={(e) => setData('logo', e.target.files?.[0] || null)}
                    className="mt-2 block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                />
                <InputError message={errors.logo || errors.region_code} className="mt-2" />
            </div>

            <div className="flex flex-wrap gap-3">
                <button
                    type="submit"
                    disabled={processing || !data.logo}
                    className="rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Mengunggah...' : region.has_logo ? 'Ganti Logo' : 'Upload Logo'}
                </button>
                {region.has_logo && (
                    <button
                        type="button"
                        onClick={remove}
                        className="rounded-full border border-rose-300 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                    >
                        Hapus Logo
                    </button>
                )}
            </div>
        </form>
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

export default function Index({ filters, levels, regions }) {
    const { data, setData } = useForm({
        q: filters.q || '',
        level: filters.level || '',
    });

    const submit = (e) => {
        e.preventDefault();
        router.get(route('admin.logos.index'), pruneQuery(data), {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout>
            <Head title="Logo Wilayah" />

            <div className="space-y-6">
                <section className="rounded-[2rem] bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <div className="text-sm uppercase tracking-[0.2em] text-slate-500">Super Admin</div>
                        <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Logo Wilayah Nasional</h1>
                        <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                            Upload logo untuk provinsi, kabupaten/kota, dan kecamatan. File yang diunggah akan langsung dipakai oleh PDF laporan dan PDF rekap sesuai level wilayah admin.
                        </p>
                    </div>
                </section>

                <ZipImportForm />

                <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <form onSubmit={submit} className="grid gap-4 lg:grid-cols-[1fr_240px_auto]">
                        <input
                            type="text"
                            value={data.q}
                            onChange={(e) => setData('q', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Cari kode wilayah, nama wilayah, atau induknya..."
                        />
                        <select
                            value={data.level}
                            onChange={(e) => setData('level', e.target.value)}
                            className="rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">Semua level</option>
                            {levels.map((level) => (
                                <option key={level.value} value={level.value}>
                                    {level.label}
                                </option>
                            ))}
                        </select>
                        <div className="flex gap-3 lg:justify-end">
                            <button type="submit" className="rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
                                Terapkan
                            </button>
                            <Link
                                href={route('admin.logos.index')}
                                className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Reset
                            </Link>
                        </div>
                    </form>
                </section>

                <section className="grid gap-4 lg:grid-cols-2">
                    {regions.data.map((region) => (
                        <LogoRowForm key={region.code} region={region} />
                    ))}

                    {regions.data.length === 0 && (
                        <section className="rounded-[2rem] border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm lg:col-span-2">
                            Tidak ada wilayah yang cocok dengan filter saat ini.
                        </section>
                    )}
                </section>

                <PaginationLinks links={regions.links} />
            </div>
        </AdminLayout>
    );
}
