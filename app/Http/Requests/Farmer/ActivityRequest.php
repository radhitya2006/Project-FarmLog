<?php

namespace App\Http\Requests\Farmer;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity instanceof Activity
            ? $this->user()->can('update', $activity)
            : $this->user()->can('create', [Activity::class, $this->route('planting_season')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'scheduled_for' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['missing'],
            'land_id' => ['missing'],
            'planting_season_id' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kegiatan wajib diisi.',
            'name.string' => 'Nama kegiatan harus berupa teks.',
            'name.max' => 'Nama kegiatan maksimal 150 karakter.',
            'scheduled_for.required' => 'Tanggal kegiatan wajib diisi.',
            'scheduled_for.date_format' => 'Tanggal kegiatan harus berupa tanggal yang valid.',
            'status.required' => 'Status kegiatan wajib dipilih.',
            'status.enum' => 'Pilih status direncanakan atau selesai.',
            'notes.string' => 'Keterangan harus berupa teks.',
            'notes.max' => 'Keterangan maksimal 5.000 karakter.',
            'user_id.missing' => 'Pemilik kegiatan ditentukan oleh server.',
            'land_id.missing' => 'Lahan kegiatan tidak dapat diubah melalui formulir.',
            'planting_season_id.missing' => 'Musim tanam kegiatan tidak dapat diubah melalui formulir.',
        ];
    }
}
