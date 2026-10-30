<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Land;
use App\Models\User;

class LandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::Farmer;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, Land $land): bool
    {
        return $this->viewAny($user) && $user->lands()->whereKey($land->id)->exists();
    }

    public function update(User $user, Land $land): bool
    {
        return $this->view($user, $land);
    }

    public function delete(User $user, Land $land): bool
    {
        return $this->view($user, $land);
    }
}
