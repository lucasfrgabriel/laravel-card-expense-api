<?php

namespace App\Services;

use App\Enums\CardStatusEnum;
use App\Events\NewExpenseEvent;
use App\Exceptions\Cards\InactiveCardException;
use App\Exceptions\Expenses\ExpenseNotCreatedException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Card;
use App\Models\Expense;
use App\Repositories\CardRepository;
use App\Repositories\ExpenseRepository;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    private ExpenseRepository $expenseRepository;
    private CardRepository $cardRepository;
    public function __construct(ExpenseRepository $expenseRepository, CardRepository $cardRepository){
        $this->expenseRepository = $expenseRepository;
        $this->cardRepository = $cardRepository;
    }

    public function store(int $card_id, float $amount, string $description): Expense
    {
        $card = $this->cardRepository->find($card_id);

        if($card->status != CardStatusEnum::Ativo){
            throw new InactiveCardException();
        }

        if(!$this->hasBalance($card, $amount)) {
            throw new InsufficientBalanceException();
        }

        try{
            DB::beginTransaction();

            $expense = $this->expenseRepository->create($card_id, $amount, $description);

            $card->balance -= $amount;
            $card->save();

            DB::commit();

            $expense->load('card.user');
            event(new NewExpenseEvent($expense));

            return $expense;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new ExpenseNotCreatedException($e);
        }
    }

    public function hasBalance(Card $card, float $amount): bool{
        return $card->balance >= $amount;
    }
}
