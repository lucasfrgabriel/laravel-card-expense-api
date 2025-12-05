<?php

namespace App\Providers;

use App\Models\Card;
use App\Models\Expense;
use App\Models\User;
use App\Policies\CardPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Card::class => CardPolicy::class,
        Expense::class => ExpensePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // $this->registerPolicies();
    }
}
