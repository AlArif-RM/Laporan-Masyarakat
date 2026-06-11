import axios from 'axios';
import InputError from '@/Components/InputError';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const emptyRegionOptions = {
    provinces: [],
    regencies: [],
    districts: [],
    villages: [],
};

const emptyRegionLoading = {
    provinces: false,
    regencies: false,
    districts: false,
    villages: false,
};

export default function Create({ categories, regionRoutes }) {
    const { data, setData, post, processing, errors } = useForm({
        reporter_name: '',
        phone: '',
        title: '',
        category_id: '',
        other_category: '',
        description: '',
        photo: null,
        province_code: '',
        regency_code: '',
        district_code: '',
        village_code: '',
        rt: '',
        rw: '',
        location_text: '',
    });
    const [previewUrl, setPreviewUrl] = useState(null);
    const [regionOptions, setRegionOptions] = useState(emptyRegionOptions);
    const [regionLoading, setRegionLoading] = useState(emptyRegionLoading);

    const selectedCategory = categories.find(
        (category) => String(category.id) === String(data.category_id),
    );
    const isOtherCategory = Boolean(selectedCategory?.is_other);

    useEffect(() => {
        if (!isOtherCategory && data.other_category) {
            setData('other_category', '');
        }
    }, [isOtherCategory]);

    useEffect(() => {
        if (!data.photo) {
            setPreviewUrl(null);
            return undefined;
        }

        const url = URL.createObjectURL(data.photo);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [data.photo]);

    useEffect(() => {
        let isActive = true;

        setRegionLoading((current) => ({ ...current, provinces: true }));

        axios
            .get(regionRoutes.provinces)
            .then(({ data: response }) => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    provinces: response.data || [],
                }));
            })
            .catch(() => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    provinces: [],
                }));
            })
            .finally(() => {
                if (!isActive) {
                    return;
                }

                setRegionLoading((current) => ({ ...current, provinces: false }));
            });

        return () => {
            isActive = false;
        };
    }, [regionRoutes.provinces]);

    useEffect(() => {
        if (!data.province_code) {
            setRegionOptions((current) => ({
                ...current,
                regencies: [],
                districts: [],
                villages: [],
            }));
            setRegionLoading((current) => ({
                ...current,
                regencies: false,
                districts: false,
                villages: false,
            }));

            return undefined;
        }

        let isActive = true;

        setRegionLoading((current) => ({ ...current, regencies: true }));

        axios
            .get(regionRoutes.regencies, {
                params: { province_code: data.province_code },
            })
            .then(({ data: response }) => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    regencies: response.data || [],
                }));
            })
            .catch(() => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    regencies: [],
                }));
            })
            .finally(() => {
                if (!isActive) {
                    return;
                }

                setRegionLoading((current) => ({ ...current, regencies: false }));
            });

        return () => {
            isActive = false;
        };
    }, [data.province_code, regionRoutes.regencies]);

    useEffect(() => {
        if (!data.regency_code) {
            setRegionOptions((current) => ({
                ...current,
                districts: [],
                villages: [],
            }));
            setRegionLoading((current) => ({
                ...current,
                districts: false,
                villages: false,
            }));

            return undefined;
        }

        let isActive = true;

        setRegionLoading((current) => ({ ...current, districts: true }));

        axios
            .get(regionRoutes.districts, {
                params: { regency_code: data.regency_code },
            })
            .then(({ data: response }) => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    districts: response.data || [],
                }));
            })
            .catch(() => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    districts: [],
                }));
            })
            .finally(() => {
                if (!isActive) {
                    return;
                }

                setRegionLoading((current) => ({ ...current, districts: false }));
            });

        return () => {
            isActive = false;
        };
    }, [data.regency_code, regionRoutes.districts]);

    useEffect(() => {
        if (!data.district_code) {
            setRegionOptions((current) => ({
                ...current,
                villages: [],
            }));
            setRegionLoading((current) => ({
                ...current,
                villages: false,
            }));

            return undefined;
        }

        let isActive = true;

        setRegionLoading((current) => ({ ...current, villages: true }));

        axios
            .get(regionRoutes.villages, {
                params: { district_code: data.district_code },
            })
            .then(({ data: response }) => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    villages: response.data || [],
                }));
            })
            .catch(() => {
                if (!isActive) {
                    return;
                }

                setRegionOptions((current) => ({
                    ...current,
                    villages: [],
                }));
            })
            .finally(() => {
                if (!isActive) {
                    return;
                }

                setRegionLoading((current) => ({ ...current, villages: false }));
            });

        return () => {
            isActive = false;
        };
    }, [data.district_code, regionRoutes.villages]);

    const submit = (e) => {
        e.preventDefault();
        post(route('reports.store'), { forceFormData: true });
    };

    const handleProvinceChange = (value) => {
        setData('province_code', value);
        setData('regency_code', '');
        setData('district_code', '');
        setData('village_code', '');
        setRegionOptions((current) => ({
            ...current,
            regencies: [],
            districts: [],
            villages: [],
        }));
    };

    const handleRegencyChange = (value) => {
        setData('regency_code', value);
        setData('district_code', '');
        setData('village_code', '');
        setRegionOptions((current) => ({
            ...current,
            districts: [],
            villages: [],
        }));
    };

    const handleDistrictChange = (value) => {
        setData('district_code', value);
        setData('village_code', '');
        setRegionOptions((current) => ({
            ...current,
            villages: [],
        }));
    };

    return (
        <PublicLayout>
            <Head title="Buat Laporan" />

            <div className="mx-auto max-w-4xl">
                <div className="rounded-[2rem] bg-gradient-to-br from-blue-600 to-indigo-700 px-6 py-7 text-white shadow-xl sm:px-8">
                    <h1 className="text-3xl font-semibold tracking-tight">Buat Laporan Baru</h1>
                    <p className="mt-3 max-w-2xl text-sm leading-7 text-blue-100/90 sm:text-base">
                        Jelaskan masalah secara ringkas, jelas, dan sesuai fakta. Jika ingin tetap anonim,
                        nama pelapor boleh dikosongkan.
                    </p>
                </div>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div className="mb-6">
                            <h2 className="text-lg font-semibold text-slate-900">1. Data Pelapor</h2>
                            <p className="mt-1 text-sm text-slate-500">Nomor HP bersifat opsional dan hanya dipakai untuk verifikasi internal.</p>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">Nama lengkap</label>
                                <input
                                    type="text"
                                    value={data.reporter_name}
                                    onChange={(e) => setData('reporter_name', e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    placeholder="Anonim / boleh dikosongkan"
                                />
                                <InputError message={errors.reporter_name} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">No. HP / WhatsApp</label>
                                <input
                                    type="text"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value.replace(/[^0-9+]/g, ''))}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    placeholder="08xx atau +62xx"
                                />
                                <InputError message={errors.phone} className="mt-2" />
                            </div>
                        </div>
                    </section>

                    <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div className="mb-6">
                            <h2 className="text-lg font-semibold text-slate-900">2. Detail Masalah</h2>
                            <p className="mt-1 text-sm text-slate-500">Pilih kategori paling sesuai dan jelaskan dampak yang dirasakan warga.</p>
                        </div>

                        <div className="space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">Judul laporan</label>
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    placeholder="Contoh: Jalan berlubang di depan pasar"
                                />
                                <InputError message={errors.title} className="mt-2" />
                            </div>

                            <div className="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">Kategori</label>
                                    <select
                                        value={data.category_id}
                                        onChange={(e) => setData('category_id', e.target.value)}
                                        className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    >
                                        <option value="">Pilih kategori</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.category_id} className="mt-2" />
                                </div>

                                {isOtherCategory && (
                                    <div>
                                        <label className="block text-sm font-medium text-slate-700">Kategori lainnya</label>
                                        <input
                                            type="text"
                                            value={data.other_category}
                                            onChange={(e) => setData('other_category', e.target.value)}
                                            className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            placeholder="Jelaskan kategori spesifik"
                                        />
                                        <InputError message={errors.other_category} className="mt-2" />
                                    </div>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">Deskripsi lengkap</label>
                                <textarea
                                    rows="6"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    className="mt-2 block w-full rounded-3xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    placeholder="Ceritakan kronologi, kondisi saat ini, dampak yang dirasakan, dan informasi penting lain."
                                />
                                <InputError message={errors.description} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">Foto bukti</label>
                                <input
                                    type="file"
                                    accept="image/*,.webp"
                                    onChange={(e) => setData('photo', e.target.files[0] || null)}
                                    className="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-sm outline-none transition file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                                />
                                <InputError message={errors.photo} className="mt-2" />

                                {previewUrl && (
                                    <div className="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                                        <div className="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">Preview foto</div>
                                        <img src={previewUrl} alt="Preview lampiran" className="h-64 w-full rounded-2xl object-cover" />
                                    </div>
                                )}
                            </div>
                        </div>
                    </section>

                    <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div className="mb-6">
                            <h2 className="text-lg font-semibold text-slate-900">3. Lokasi Kejadian</h2>
                            <p className="mt-1 text-sm text-slate-500">Pilih wilayah secara bertingkat, lalu tambahkan patokan lokasi agar petugas lebih mudah menemukan titik masalah.</p>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">Provinsi</label>
                                <select
                                    value={data.province_code}
                                    onChange={(e) => handleProvinceChange(e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    disabled={regionLoading.provinces}
                                >
                                    <option value="">{regionLoading.provinces ? 'Memuat provinsi...' : 'Pilih provinsi'}</option>
                                    {regionOptions.provinces.map((province) => (
                                        <option key={province.code} value={province.code}>
                                            {province.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.province_code} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">Kabupaten / kota</label>
                                <select
                                    value={data.regency_code}
                                    onChange={(e) => handleRegencyChange(e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    disabled={!data.province_code || regionLoading.regencies}
                                >
                                    <option value="">
                                        {!data.province_code
                                            ? 'Pilih provinsi dulu'
                                            : regionLoading.regencies
                                              ? 'Memuat kabupaten/kota...'
                                              : 'Pilih kabupaten/kota'}
                                    </option>
                                    {regionOptions.regencies.map((regency) => (
                                        <option key={regency.code} value={regency.code}>
                                            {regency.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.regency_code} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">Kecamatan</label>
                                <select
                                    value={data.district_code}
                                    onChange={(e) => handleDistrictChange(e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    disabled={!data.regency_code || regionLoading.districts}
                                >
                                    <option value="">
                                        {!data.regency_code
                                            ? 'Pilih kabupaten/kota dulu'
                                            : regionLoading.districts
                                              ? 'Memuat kecamatan...'
                                              : 'Pilih kecamatan'}
                                    </option>
                                    {regionOptions.districts.map((district) => (
                                        <option key={district.code} value={district.code}>
                                            {district.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.district_code} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">Desa / kelurahan</label>
                                <select
                                    value={data.village_code}
                                    onChange={(e) => setData('village_code', e.target.value)}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    disabled={!data.district_code || regionLoading.villages}
                                >
                                    <option value="">
                                        {!data.district_code
                                            ? 'Pilih kecamatan dulu'
                                            : regionLoading.villages
                                              ? 'Memuat desa/kelurahan...'
                                              : 'Pilih desa/kelurahan'}
                                    </option>
                                    {regionOptions.villages.map((village) => (
                                        <option key={village.code} value={village.code}>
                                            {village.name}
                                        </option>
                                    ))}
                                </select>
                                <p className="mt-2 text-xs text-slate-500">Opsional jika pelapor belum tahu desa/kelurahannya.</p>
                                <InputError message={errors.village_code} className="mt-2" />
                            </div>
                        </div>

                        <div className="mt-5 grid gap-5 sm:grid-cols-2">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">RT</label>
                                <input
                                    type="text"
                                    value={data.rt}
                                    onChange={(e) => setData('rt', e.target.value.replace(/[^0-9]/g, ''))}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                />
                                <InputError message={errors.rt} className="mt-2" />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">RW</label>
                                <input
                                    type="text"
                                    value={data.rw}
                                    onChange={(e) => setData('rw', e.target.value.replace(/[^0-9]/g, ''))}
                                    className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                />
                                <InputError message={errors.rw} className="mt-2" />
                            </div>
                        </div>

                        <div className="mt-5">
                            <label className="block text-sm font-medium text-slate-700">Patokan atau detail lokasi</label>
                            <input
                                type="text"
                                value={data.location_text}
                                onChange={(e) => setData('location_text', e.target.value)}
                                className="mt-2 block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                placeholder="Contoh: Sebelah utara masjid raya, depan toko biru"
                            />
                            <InputError message={errors.location_text} className="mt-2" />
                        </div>
                    </section>

                    <div className="flex flex-wrap items-center justify-end gap-3">
                        <Link
                            href={route('home')}
                            className="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Menyimpan...' : 'Kirim laporan'}
                        </button>
                    </div>
                </form>
            </div>
        </PublicLayout>
    );
}
