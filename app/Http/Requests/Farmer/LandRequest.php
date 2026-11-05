<?php

namespace App\Http\Requests\Farmer;

use App\Models\Land;
use Illuminate\Foundation\Http\FormRequest;

class LandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $land = $this->route('land');

        return $land instanceof Land
            ? $this->user()->can('update', $land)
            : $this->user()->can('create', Land::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'area_hectares' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,99999999.99'],
            'location' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lahan wajib diisi.',
            'name.string' => 'Nama lahan harus berupa teks.',
            'name.max' => 'Nama lahan maksimal 150 karakter.',
            'area_hectares.required' => 'Luas lahan wajib diisi.',
            'area_hectares.numeric' => 'Luas lahan harus berupa angka.',
            'area_hectares.decimal' => 'Luas lahan maksimal dua angka di belakang desimal.',
            'area_hectares.between' => 'Luas lahan harus antara 0,01 dan 99.999.999,99 hektare.',
            'location.required' => 'Lokasi lahan wajib diisi.',
            'location.string' => 'Lokasi lahan harus berupa teks.',
            'location.max' => 'Lokasi lahan maksimal 255 karakter.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 5.000 karakter.',
            'user_id.missing' => 'Pemilik lahan tidak dapat ditentukan atau diubah melalui formulir.',
        ];
    }
}
