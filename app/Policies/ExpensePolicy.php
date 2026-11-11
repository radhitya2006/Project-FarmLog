<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\PlantingSeason;
use App\Models\User;

class ExpensePolicy
{
    public function create(User $user, PlantingSeason $season): bool
    {
        return (new PlantingSeasonPolicy)->view($user, $season);
    }

    public function update(User $user, Expense $record): bool
    {
        return $this->create($user, $record->plantingSeason);
    }

    public function delete(User $user, Expense $record): bool
    {
        return $this->update($user, $record);
    }
}
