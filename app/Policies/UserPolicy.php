<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\User;

class UserPolicy
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
    public function view(User $user, User $model): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $model->is($user)
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $model->is($user)
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $model->is($user)
        );
    }
}
