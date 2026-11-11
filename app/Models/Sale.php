<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected $fillable = ['received_on', 'name', 'amount', 'notes'];

    /** @return BelongsTo<PlantingSeason, $this> */
    public function plantingSeason(): BelongsTo
    {
        return $this->belongsTo(PlantingSeason::class);
    }

    protected function casts(): array
    {
        return ['received_on' => 'date', 'amount' => 'decimal:2'];
    }
}
