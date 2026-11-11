<?php

namespace App\Http\Requests\Farmer;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FinancialRecordRequest
{
    public function authorize(): bool
    {
        $record = $this->route('expense');

        return $record instanceof Expense
            ? $this->user()->can('update', $record)
            : $this->user()->can('create', [Expense::class, $this->route('planting_season')]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'spent_on' => ['required', 'date_format:Y-m-d'],
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
        ];
    }
}
