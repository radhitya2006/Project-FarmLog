<?php

namespace App\Models;

use App\Enums\PlantingSeasonStatus;
use Database\Factories\PlantingSeasonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingSeason extends Model
{
    /** @use HasFactory<PlantingSeasonFactory> */
    use HasFactory;

    protected $fillable = ['crop_name', 'planted_at', 'expected_harvest_at', 'status', 'notes'];

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
