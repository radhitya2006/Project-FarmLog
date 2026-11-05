<?php

namespace App\Http\Requests\Farmer;

use App\Models\PlantingSeason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PlantingSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $land = $this->route('land');
        $season = $this->route('planting_season');

        return $this->user()->can('view', $land) && ($season instanceof PlantingSeason
            ? $this->user()->can('update', $season)
            : $this->user()->can('create', [PlantingSeason::class, $land]));
    }

    public function rules(): array
    {
        return [
            'crop_name' => ['required', 'string', 'max:150'],
            'planted_at' => ['required', 'date_format:Y-m-d'],
            'expected_harvest_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:planted_at'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'land_id' => ['missing'],
            'user_id' => ['missing'],
            'status' => ['missing'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $season = $this->route('planting_season');

            if ($season instanceof PlantingSeason && ! $validator->errors()->has('planted_at')
                && $season->harvests()->whereDate('harvested_at', '<', $this->input('planted_at'))->exists()) {
                $validator->errors()->add('planted_at', 'Tanggal tanam tidak boleh setelah tanggal panen yang sudah tercatat.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'crop_name.required' => 'Komoditas wajib diisi.',
            'crop_name.string' => 'Komoditas harus berupa teks.',
            'crop_name.max' => 'Komoditas maksimal 150 karakter.',
            'planted_at.required' => 'Tanggal tanam wajib diisi.',
            'planted_at.date_format' => 'Tanggal tanam harus berupa tanggal yang valid.',
            'expected_harvest_at.required' => 'Perkiraan tanggal panen wajib diisi.',
            'expected_harvest_at.date_format' => 'Perkiraan panen harus berupa tanggal yang valid.',
            'expected_harvest_at.after_or_equal' => 'Perkiraan panen tidak boleh sebelum tanggal tanam.',
            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan maksimal 5.000 karakter.',
            'land_id.missing' => 'Lahan ditentukan melalui halaman lahan dan tidak dapat dipindahkan.',
            'user_id.missing' => 'Pemilik musim tanam tidak dapat ditentukan melalui formulir.',
            'status.missing' => 'Gunakan aksi selesaikan atau aktifkan kembali untuk mengubah status.',
        ];
    }
}
