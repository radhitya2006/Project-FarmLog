<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPlantingSeasonActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->routeIs('*.finish') ? 'finish' : ($this->routeIs('*.reopen') ? 'reopen' : 'delete');

        return $this->user()->can('view', $this->route('land'))
            && $this->user()->can($ability, $this->route('planting_season'));
    }

    public function rules(): array
    {
        return ['confirmed' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return [
            'confirmed.required' => 'Konfirmasikan tindakan melalui dialog yang tersedia.',
            'confirmed.accepted' => 'Konfirmasi tindakan diperlukan sebelum melanjutkan.',
        ];
    }
}
