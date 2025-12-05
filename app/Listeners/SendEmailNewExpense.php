<?php

namespace App\Listeners;

use App\Enums\UserTypeEnum;
use App\Events\NewExpenseEvent;
use App\Mail\NewExpenseAlert;
use App\Models\Card;
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

        $data = $event->data;
        $card = Card::find($data['card_id']);

        if(!$card){
            throw new \Exception('Card not found');
        }

        $user = User::find($card->user_id);

        if(!$user){
            throw new \Exception('User not found');
        }

        $last4Digits = substr($card->number, -4);

        $cardOwnerEmail = $user->email;

        $expenseData = array_merge(
            $data,
            ['last4digits' => $last4Digits],
            ['name' => $user->name],
            ['cardOwnerEmail' => $cardOwnerEmail],
        );

        $adminEmails = User::where('type', UserTypeEnum::Admin)->pluck('email')->toArray();

        Mail::to($cardOwnerEmail)->cc($adminEmails)->send(new NewExpenseAlert($expenseData));
    }
}
