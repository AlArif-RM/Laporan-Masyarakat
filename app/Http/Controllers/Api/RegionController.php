<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()->json([
            'data' => $this->optionsFor(Region::query()->byLevel(Region::LEVEL_PROVINCE)->ordered()->get()),
        ]);
    }

    public function regencies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_code' => ['required', 'string', Rule::exists('regions', 'code')->where('level', Region::LEVEL_PROVINCE)],
        ]);

        return response()->json([
            'data' => $this->optionsFor(
                Region::query()
                    ->byLevel(Region::LEVEL_REGENCY)
                    ->where('parent_code', $validated['province_code'])
                    ->ordered()
                    ->get(),
            ),
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_code' => ['required', 'string', Rule::exists('regions', 'code')->where('level', Region::LEVEL_REGENCY)],
        ]);

        return response()->json([
            'data' => $this->optionsFor(
                Region::query()
                    ->byLevel(Region::LEVEL_DISTRICT)
                    ->where('parent_code', $validated['regency_code'])
                    ->ordered()
                    ->get(),
            ),
        ]);
    }

    public function villages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_code' => ['required', 'string', Rule::exists('regions', 'code')->where('level', Region::LEVEL_DISTRICT)],
        ]);

        return response()->json([
            'data' => $this->optionsFor(
                Region::query()
                    ->byLevel(Region::LEVEL_VILLAGE)
                    ->where('parent_code', $validated['district_code'])
                    ->ordered()
                    ->get(),
            ),
        ]);
    }

    private function optionsFor($regions): array
    {
        return $regions->map(fn (Region $region) => [
            'code' => $region->code,
            'name' => $region->name,
        ])->values()->all();
    }
}
