<?php

namespace App\Http\Requests;

use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reporter_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'other_category' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'province_code' => ['required', 'string', 'max:13', Rule::exists('regions', 'code')->where('level', Region::LEVEL_PROVINCE)],
            'regency_code' => ['required', 'string', 'max:13', Rule::exists('regions', 'code')->where('level', Region::LEVEL_REGENCY)],
            'district_code' => ['required', 'string', 'max:13', Rule::exists('regions', 'code')->where('level', Region::LEVEL_DISTRICT)],
            'village_code' => ['nullable', 'string', 'max:13', Rule::exists('regions', 'code')->where('level', Region::LEVEL_VILLAGE)],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'location_text' => ['required', 'string', 'max:255'],
        ];
    }
}
