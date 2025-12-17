<?php

namespace App\Listeners;

use App\Enums\UserTypeEnum;
use App\Events\NewExpenseEvent;
use App\Mail\NewExpenseAlert;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendEmailNewExpense
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewExpenseEvent $event): void
    {

        $expense = $event->expense;
        $card = $expense->card;
        $user = $card->user;

        $cardOwnerEmail = $user->email;
        $adminEmails = User::where('type', UserTypeEnum::Admin)->pluck('email')->toArray();

        $expenseData = [
            'amount'         => $expense->amount,
            'description'    => $expense->description,
            'date'           => $expense->date,
            'last4digits'    => substr($card->number, -4),
            'name'           => $user->name,
            'cardOwnerEmail' => $user->email,
        ];

        Mail::to($cardOwnerEmail)->cc($adminEmails)->send(new NewExpenseAlert($expenseData));
    }
}
