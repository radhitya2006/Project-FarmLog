<?php

namespace App\Models;

use App\Enums\HarvestUnit;
use App\Enums\PlantingSeasonStatus;
use Database\Factories\PlantingSeasonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingSeason extends Model
{
    /** @use HasFactory<PlantingSeasonFactory> */
    use HasFactory;

    protected $fillable = ['crop_name', 'planted_at', 'expected_harvest_at', 'status', 'notes'];

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithHarvestTotals(Builder $query): Builder
    {
        return $query->withSum(['harvests as total_kg' => fn ($query) => $query->where('unit', HarvestUnit::Kilogram)], 'quantity')
            ->withSum(['harvests as total_ton' => fn ($query) => $query->where('unit', HarvestUnit::Ton)], 'quantity');
    }

    /** @return BelongsTo<Land, $this> */
    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** @return HasMany<Harvest, $this> */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    protected function casts(): array
    {
        return [
            'planted_at' => 'date',
            'expected_harvest_at' => 'date',
            'status' => PlantingSeasonStatus::class,
        ];
    }
}
