<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class DeleteHarvestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('delete', $this->route('harvest'));
    }

    public function rules(): array
    {
        return ['confirmed' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return ['confirmed.required' => 'Konfirmasikan penghapusan panen melalui dialog yang tersedia.',
            'confirmed.accepted' => 'Konfirmasi diperlukan untuk menghapus catatan panen.'];
    }
}
