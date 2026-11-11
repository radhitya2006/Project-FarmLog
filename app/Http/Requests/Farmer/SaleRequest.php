<?php

namespace App\Http\Requests\Farmer;

use App\Models\Sale;

class SaleRequest extends FinancialRecordRequest
{
    public function authorize(): bool
    {
        $record = $this->route('sale');

        return $record instanceof Sale
            ? $this->user()->can('update', $record)
            : $this->user()->can('create', [Sale::class, $this->route('planting_season')]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'received_on' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
