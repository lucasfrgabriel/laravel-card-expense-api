<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->type == UserTypeEnum::Admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Card $card): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $card->user->is($user)
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Card $card): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $card->user->is($user)
        );
    }

    /**
     * Determine whether the user can deposit in the card.
     */
    public function deposit(User $user, Card $card): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $card->user->is($user)
        );
    }

    public function changeStatus(User $user, Card $card): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $card->user->is($user)
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Card $card): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $card->user->is($user)
        );
    }
}
