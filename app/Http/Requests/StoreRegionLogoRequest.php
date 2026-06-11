<?php

namespace App\Http\Requests;

use App\Models\Region;
use App\Support\RegionAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegionLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_code' => [
                'required',
                'string',
                'max:13',
                Rule::exists('regions', 'code')->whereIn('level', RegionAdmin::ADMIN_LEVELS),
            ],
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}
