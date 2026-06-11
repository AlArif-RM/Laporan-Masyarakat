<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRegionLogoZipRequest;
use App\Http\Requests\StoreRegionLogoRequest;
use App\Models\Region;
use App\Support\RegionAdmin;
use App\Support\RegionBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

class RegionLogoController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureSuperAdmin($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', Rule::in(RegionAdmin::ADMIN_LEVELS)],
        ]);

        $query = Region::query()
            ->whereIn('level', RegionAdmin::ADMIN_LEVELS)
            ->with('parent');

        if (filled($filters['level'] ?? null)) {
            $query->where('level', $filters['level']);
        }

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $like = '%'.$search.'%';

                $builder
                    ->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('name', 'like', $like));
            });
        }

        $regions = $query
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString()
            ->through(function (Region $region) {
                $logoUrl = RegionBranding::publicUrlForRegion($region);

                return [
                    'code' => $region->code,
                    'name' => $region->name,
                    'level' => $region->level,
                    'level_label' => RegionAdmin::administrativeName($region->level, $region->name),
                    'parent_name' => $region->parent?->name,
                    'logo_url' => $logoUrl,
                    'has_logo' => $logoUrl !== null,
                ];
            });

        return Inertia::render('Admin/Logos/Index', [
            'filters' => [
                'q' => $filters['q'] ?? '',
                'level' => $filters['level'] ?? '',
            ],
            'levels' => collect(RegionAdmin::ADMIN_LEVELS)->map(fn (string $level) => [
                'value' => $level,
                'label' => match ($level) {
                    Region::LEVEL_PROVINCE => 'Provinsi',
                    Region::LEVEL_REGENCY => 'Kabupaten/Kota',
                    Region::LEVEL_DISTRICT => 'Kecamatan',
                },
            ])->values(),
            'regions' => $regions,
        ]);
    }

    public function store(StoreRegionLogoRequest $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validated();
        $region = Region::query()->findOrFail($validated['region_code']);
        $extension = strtolower($request->file('logo')->getClientOriginalExtension() ?: $request->file('logo')->extension() ?: 'png');
        $this->replaceRegionLogo($region, $extension, file_get_contents($request->file('logo')->getRealPath()) ?: '');

        return back()->with('success', 'Logo wilayah berhasil diperbarui.');
    }

    public function import(ImportRegionLogoZipRequest $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $archivePath = $request->file('archive')->getRealPath();
        $zip = new ZipArchive();

        if ($archivePath === false || $zip->open($archivePath) !== true) {
            return back()->with('error', 'File ZIP tidak dapat dibuka.');
        }

        $imported = 0;
        $skipped = 0;
        $warnings = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entryName = $zip->getNameIndex($index);

            if (! is_string($entryName) || $entryName === '' || str_ends_with($entryName, '/')) {
                continue;
            }

            $normalizedName = str_replace('\\', '/', $entryName);
            $basename = pathinfo($normalizedName, PATHINFO_BASENAME);
            $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $regionCode = pathinfo($basename, PATHINFO_FILENAME);

            if (! in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $skipped++;
                $warnings[] = $basename.' dilewati karena bukan file logo yang didukung.';

                continue;
            }

            $region = Region::query()
                ->whereIn('level', RegionAdmin::ADMIN_LEVELS)
                ->find($regionCode);

            if (! $region) {
                $skipped++;
                $warnings[] = $basename.' dilewati karena kode wilayah tidak dikenali.';

                continue;
            }

            $stream = $zip->getStream($entryName);

            if (! is_resource($stream)) {
                $skipped++;
                $warnings[] = $basename.' dilewati karena tidak bisa dibaca dari ZIP.';

                continue;
            }

            $contents = stream_get_contents($stream);
            fclose($stream);

            if ($contents === false || $contents === '') {
                $skipped++;
                $warnings[] = $basename.' dilewati karena isinya kosong.';

                continue;
            }

            $this->replaceRegionLogo($region, $extension, $contents);
            $imported++;
        }

        $zip->close();

        $redirect = back()->with('success', "Import logo ZIP selesai. Berhasil {$imported} file, dilewati {$skipped} file.");

        if ($warnings !== []) {
            $redirect->with('warning', collect($warnings)->take(5)->implode(' '));
        }

        return $redirect;
    }

    public function destroy(Request $request, Region $region): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $relativePath = RegionBranding::relativePathForRegion($region, $extension);

            if ($relativePath !== null && is_file(public_path($relativePath))) {
                File::delete(public_path($relativePath));
            }
        }

        return back()->with('success', 'Logo wilayah berhasil dihapus.');
    }

    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
    }

    private function replaceRegionLogo(Region $region, string $extension, string $contents): void
    {
        $folder = RegionBranding::folderForLevel($region->level);

        abort_if($folder === null, 422);

        $directory = public_path('assets/logos/'.$folder);
        File::ensureDirectoryExists($directory);

        foreach (['png', 'jpg', 'jpeg', 'webp'] as $existingExtension) {
            $existing = RegionBranding::relativePathForRegion($region, $existingExtension);

            if ($existing !== null && is_file(public_path($existing))) {
                File::delete(public_path($existing));
            }
        }

        $target = RegionBranding::relativePathForRegion($region, $extension);

        abort_if($target === null, 422);

        File::put(public_path($target), $contents);
    }
}
