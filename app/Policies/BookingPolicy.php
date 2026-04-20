<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;

class BookingPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('user') || $user->hasRole('provider') || $user->hasRole('admin');
    }

    public function view(User $user, Booking $booking)
    {
        return $user->hasRole('admin') ||
               $booking->user_id === $user->id ||
               $booking->bookable->provider_id === $user->id;
    }

    public function create(User $user)
    {
        return $user->hasRole('user');
    }

    public function update(User $user, Booking $booking)
    {
        return $user->hasRole('admin') ||
               $booking->user_id === $user->id ||
               $booking->bookable->provider_id === $user->id;
    }

    public function delete(User $user, Booking $booking)
    {
        return $user->hasRole('admin') || $booking->user_id === $user->id;
    }

    public function approve(User $user, Booking $booking)
    {
        return $user->hasRole('admin') || $booking->bookable->provider_id === $user->id;
    }

    public function reject(User $user, Booking $booking)
    {
        return $user->hasRole('admin') || $booking->bookable->provider_id === $user->id;
    }
}


