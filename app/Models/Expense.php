<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $fillable = ['spent_on', 'name', 'category', 'amount', 'notes'];

    /** @return BelongsTo<PlantingSeason, $this> */
    public function plantingSeason(): BelongsTo
    {
        return $this->belongsTo(PlantingSeason::class);
    }

    protected function casts(): array
    {
        return ['spent_on' => 'date', 'amount' => 'decimal:2', 'category' => ExpenseCategory::class];
    }
}
