<?php

namespace App\Policies;

use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;

class PlantingSeasonPolicy
{
    public function viewAny(User $user): bool
    {
        return (new LandPolicy)->viewAny($user);
    }

    public function create(User $user, Land $land): bool
    {
        return (new LandPolicy)->view($user, $land);
    }

    public function view(User $user, PlantingSeason $season): bool
    {
        return $this->viewAny($user) && $user->lands()
            ->whereHas('plantingSeasons', fn ($query) => $query->whereKey($season->id))->exists();
    }

    public function update(User $user, PlantingSeason $season): bool
    {
        return $this->view($user, $season);
    }

    public function delete(User $user, PlantingSeason $season): bool
    {
        return $this->view($user, $season);
    }

    public function finish(User $user, PlantingSeason $season): bool
    {
        return $this->view($user, $season);
    }

    public function reopen(User $user, PlantingSeason $season): bool
    {
        return $this->view($user, $season);
    }
}
