<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
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
    public function view(User $user, Expense $expense): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $expense->card->user->is($user)
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
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return (
            $user->type == UserTypeEnum::Admin
            ||
            $expense->card->user->is($user)
        );
    }
}
