<?php

namespace App\Models;

use App\Enums\HarvestUnit;
use Database\Factories\HarvestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    /** @use HasFactory<HarvestFactory> */
    use HasFactory;

    protected $fillable = ['harvested_at', 'quantity', 'unit', 'notes'];

    /** @return BelongsTo<PlantingSeason, $this> */
    public function plantingSeason(): BelongsTo
    {
        return $this->belongsTo(PlantingSeason::class);
    }

    protected function casts(): array
    {
        return [
            'harvested_at' => 'date',
            'quantity' => 'decimal:2',
            'unit' => HarvestUnit::class,
        ];
    }
}
