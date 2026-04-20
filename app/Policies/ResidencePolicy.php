<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Residence;

class ResidencePolicy
{
    public function viewAny(User $user)
    {
        return true; // Anyone can view residences list
    }

    public function view(User $user, Residence $residence)
    {
        return $residence->is_active || $user->hasRole('admin') || $residence->provider_id === $user->id;
    }

    public function create(User $user)
    {
        return $user->hasRole('provider') || $user->hasRole('admin');
    }

    public function update(User $user, Residence $residence)
    {
        return $user->hasRole('admin') || $residence->provider_id === $user->id;
    }

    public function delete(User $user, Residence $residence)
    {
        return $user->hasRole('admin') || $residence->provider_id === $user->id;
    }
}


