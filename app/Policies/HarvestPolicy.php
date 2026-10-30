<?php

namespace App\Policies;

use App\Enums\PlantingSeasonStatus;
use App\Models\Harvest;
use App\Models\PlantingSeason;
use App\Models\User;

class HarvestPolicy
{
    public function viewAny(User $user): bool
    {
        return (new LandPolicy)->viewAny($user);
    }

    public function create(User $user, PlantingSeason $season): bool
    {
        return (new PlantingSeasonPolicy)->view($user, $season)
            && $season->status === PlantingSeasonStatus::Active;
    }

    public function view(User $user, Harvest $resource): bool
    {
        return $this->viewAny($user) && $user->lands()
            ->whereHas('plantingSeasons.harvests', fn ($query) => $query->whereKey($resource->id))->exists();
    }

    public function update(User $user, Harvest $resource): bool
    {
        return $this->view($user, $resource)
            && $resource->plantingSeason()->where('status', PlantingSeasonStatus::Active)->exists();
    }

    public function delete(User $user, Harvest $resource): bool
    {
        return $this->update($user, $resource);
    }
}
