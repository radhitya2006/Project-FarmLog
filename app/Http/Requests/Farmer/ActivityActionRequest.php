<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class ActivityActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->routeIs('*.destroy') ? 'delete' : 'update', $this->route('activity'));
    }

    public function rules(): array
    {
        return $this->routeIs('*.destroy') ? ['confirmed' => ['required', 'accepted']] : [];
    }

    public function messages(): array
    {
        return [
            'confirmed.required' => 'Konfirmasikan penghapusan kegiatan melalui dialog yang tersedia.',
            'confirmed.accepted' => 'Konfirmasi diperlukan untuk menghapus kegiatan.',
        ];
    }
}
