<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Models\User;
use App\Support\RegionAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureSuperAdmin($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN_PROVINSI,
                User::ROLE_ADMIN_KABUPATEN_KOTA,
                User::ROLE_ADMIN_KECAMATAN,
            ])],
        ]);

        $query = $this->filteredQuery($filters);

        $users = $query
            ->with('region')
            ->orderBy('role')
            ->orderBy('region_code')
            ->orderBy('username')
            ->paginate(50)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'role_label' => RegionAdmin::roleLabel($user->role),
                'region_code' => $user->region_code,
                'region_name' => $user->region?->name,
                'region_label' => $user->region
                    ? RegionAdmin::administrativeName($user->region->level, $user->region->name)
                    : 'Super Admin Nasional',
                'created_at_human' => $user->created_at?->locale(app()->getLocale())->translatedFormat('d M Y H:i'),
            ]);

        $counts = collect($this->filteredQuery($filters)
            ->selectRaw('role, COUNT(*) AS total')
            ->groupBy('role')
            ->pluck('total', 'role'));

        return Inertia::render('Admin/Accounts/Index', [
            'filters' => [
                'q' => $filters['q'] ?? '',
                'role' => $filters['role'] ?? '',
            ],
            'users' => $users,
            'roles' => collect([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN_PROVINSI,
                User::ROLE_ADMIN_KABUPATEN_KOTA,
                User::ROLE_ADMIN_KECAMATAN,
            ])->map(fn (string $role) => [
                'value' => $role,
                'label' => RegionAdmin::roleLabel($role),
            ])->values(),
            'counts' => [
                'TOTAL' => (int) $counts->sum(),
                User::ROLE_SUPER_ADMIN => (int) $counts->get(User::ROLE_SUPER_ADMIN, 0),
                User::ROLE_ADMIN_PROVINSI => (int) $counts->get(User::ROLE_ADMIN_PROVINSI, 0),
                User::ROLE_ADMIN_KABUPATEN_KOTA => (int) $counts->get(User::ROLE_ADMIN_KABUPATEN_KOTA, 0),
                User::ROLE_ADMIN_KECAMATAN => (int) $counts->get(User::ROLE_ADMIN_KECAMATAN, 0),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureSuperAdmin($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN_PROVINSI,
                User::ROLE_ADMIN_KABUPATEN_KOTA,
                User::ROLE_ADMIN_KECAMATAN,
            ])],
        ]);

        $query = $this->filteredQuery($filters)
            ->with('region')
            ->orderBy('role')
            ->orderBy('region_code')
            ->orderBy('username');

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'username',
                'name',
                'role',
                'role_label',
                'region_code',
                'region_level',
                'region_name',
            ]);

            foreach ($query->cursor() as $user) {
                fputcsv($output, [
                    $user->username,
                    $user->name,
                    $user->role,
                    RegionAdmin::roleLabel($user->role),
                    $user->region_code,
                    $user->region?->level,
                    $user->region?->name,
                ]);
            }

            fclose($output);
        }, 'akun_admin_wilayah_nasional.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validated();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password akun berhasil diperbarui.');
    }

    private function filteredQuery(array $filters)
    {
        $query = User::query();

        if (filled($filters['role'] ?? null)) {
            $query->where('role', $filters['role']);
        }

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $like = '%'.$search.'%';

                $builder
                    ->where('username', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('region_code', 'like', $like)
                    ->orWhereHas('region', fn ($regionQuery) => $regionQuery->where('name', 'like', $like));
            });
        }

        return $query;
    }

    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
    }
}
