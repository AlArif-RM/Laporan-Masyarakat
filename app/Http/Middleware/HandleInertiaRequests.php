<?php

namespace App\Http\Middleware;

use App\Support\RegionAdmin;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'username' => $request->user()->username,
                        'role' => $request->user()->role,
                        'role_label' => RegionAdmin::roleLabel($request->user()->role),
                        'region' => $request->user()->region
                            ? [
                                'code' => $request->user()->region->code,
                                'name' => $request->user()->region->name,
                                'level' => $request->user()->region->level,
                                'label' => RegionAdmin::administrativeName($request->user()->region->level, $request->user()->region->name),
                            ]
                            : null,
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
