<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

abstract class FinancialRecordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,9999999999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_id' => ['missing'], 'land_id' => ['missing'], 'planting_season_id' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'name.max' => 'Nama atau keterangan maksimal 150 karakter.',
            'notes.max' => 'Catatan maksimal 5.000 karakter.',
            'date_format' => ':attribute harus berupa tanggal yang valid.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.decimal' => 'Nominal maksimal dua angka desimal.',
            'amount.between' => 'Nominal harus antara Rp0,01 dan Rp9.999.999.999.999,99.',
            'category.enum' => 'Pilih kategori pengeluaran yang tersedia.',
            'missing' => 'Pemilik dan musim transaksi ditentukan oleh server.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Nama atau keterangan', 'amount' => 'Nominal', 'notes' => 'Catatan',
            'spent_on' => 'Tanggal pengeluaran', 'received_on' => 'Tanggal penerimaan', 'category' => 'Kategori'];
    }
}
