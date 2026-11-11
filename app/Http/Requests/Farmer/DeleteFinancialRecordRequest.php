<?php

namespace App\Http\Requests\Farmer;

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFinancialRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('expense') ?? $this->route('sale');

        return ($record instanceof Expense || $record instanceof Sale) && $this->user()->can('delete', $record);
    }

    public function rules(): array
    {
        return ['confirmed' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return ['confirmed.required' => 'Konfirmasikan penghapusan melalui dialog yang tersedia.',
            'confirmed.accepted' => 'Konfirmasi diperlukan untuk menghapus transaksi.'];
    }
}
