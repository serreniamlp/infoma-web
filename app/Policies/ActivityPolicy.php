<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;

class ActivityPolicy
{
    public function viewAny(User $user)
    {
        return true; // Anyone can view activities list
    }

    public function view(User $user, Activity $activity)
    {
        return $activity->is_active || $user->hasRole('admin') || $activity->provider_id === $user->id;
    }

    public function create(User $user)
    {
        return $user->hasRole('provider') || $user->hasRole('admin');
    }

    public function update(User $user, Activity $activity)
    {
        return $user->hasRole('admin') || $activity->provider_id === $user->id;
    }

    public function delete(User $user, Activity $activity)
    {
        return $user->hasRole('admin') || $activity->provider_id === $user->id;
    }
}


