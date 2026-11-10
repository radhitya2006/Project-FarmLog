<?php

namespace App\Http\Requests\Farmer;

use App\Enums\HarvestUnit;
use App\Models\Harvest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HarvestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $harvest = $this->route('harvest');

        return $harvest instanceof Harvest ? $this->user()->can('update', $harvest)
            : $this->user()->can('create', [Harvest::class, $this->route('planting_season')]);
    }

    public function rules(): array
    {
        return [
            'harvested_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$this->route('planting_season')->planted_at->toDateString()],
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,9999999999.99'],
            'unit' => ['required', Rule::enum(HarvestUnit::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['missing'], 'land_id' => ['missing'], 'planting_season_id' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'harvested_at.required' => 'Tanggal panen wajib diisi.',
            'harvested_at.date_format' => 'Tanggal panen harus berupa tanggal yang valid.',
            'harvested_at.after_or_equal' => 'Tanggal panen tidak boleh sebelum tanggal tanam.',
            'quantity.required' => 'Jumlah hasil panen wajib diisi.',
            'quantity.numeric' => 'Jumlah hasil panen harus berupa angka.',
            'quantity.decimal' => 'Jumlah panen maksimal dua angka di belakang desimal.',
            'quantity.between' => 'Jumlah panen harus antara 0,01 dan 9.999.999.999,99.',
            'unit.required' => 'Satuan panen wajib dipilih.',
            'unit.enum' => 'Pilih satuan kg atau ton.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 5.000 karakter.',
            'user_id.missing' => 'Pemilik panen ditentukan oleh server.',
            'land_id.missing' => 'Lahan panen tidak dapat diubah melalui formulir.',
            'planting_season_id.missing' => 'Musim tanam panen tidak dapat diubah melalui formulir.',
        ];
    }
}
