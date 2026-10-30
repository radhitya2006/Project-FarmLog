<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    protected $fillable = ['name', 'scheduled_for', 'status', 'notes'];

    /** @return BelongsTo<PlantingSeason, $this> */
    public function plantingSeason(): BelongsTo
    {
        return $this->belongsTo(PlantingSeason::class);
    }

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'status' => ActivityStatus::class,
        ];
    }
}
