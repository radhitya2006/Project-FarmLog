<?php

namespace App\Policies;

use App\Models\PlantingSeason;
use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function create(User $user, PlantingSeason $season): bool
    {
        return (new PlantingSeasonPolicy)->view($user, $season);
    }

    public function update(User $user, Sale $record): bool
    {
        return $this->create($user, $record->plantingSeason);
    }

    public function delete(User $user, Sale $record): bool
    {
        return $this->update($user, $record);
    }
}
