<?php

namespace App\Models;

use Database\Factories\LandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Land extends Model
{
    /** @use HasFactory<LandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'area_hectares', 'location', 'notes'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PlantingSeason, $this> */
    public function plantingSeasons(): HasMany
    {
        return $this->hasMany(PlantingSeason::class);
    }

    protected function casts(): array
    {
        return ['area_hectares' => 'decimal:2'];
    }
}
