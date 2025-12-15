<?php

namespace App\Services;

use App\Enums\CardStatusEnum;
use App\Exceptions\InactiveCardException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Card;
use App\Models\Expense;
use App\Repositories\CardRepository;
use App\Repositories\ExpenseRepository;
use Exception;

class ExpenseService
{
    private ExpenseRepository $expenseRepository;
    private CardRepository $cardRepository;
    public function __construct(ExpenseRepository $expenseRepository, CardRepository $cardRepository){
        $this->expenseRepository = $expenseRepository;
        $this->cardRepository = $cardRepository;
    }

    /**
     * @throws InactiveCardException
     * @throws InsufficientBalanceException
     */
    public function store(array $data): Expense
    {
        $card = $this->cardRepository->find($data['card_id']);
        $amount = $data['amount'];

        if($card->status != CardStatusEnum::Ativo){
            throw new InactiveCardException();
        }

        if(!$this->hasBalance($card, $amount)) {
            throw new InsufficientBalanceException();
        }

        $expense = $this->expenseRepository->create($data);

        $card->balance -= $amount;
        $card->save();

        return $expense;
    }

    public function hasBalance(Card $card, float $amount): bool{
        return $card->balance >= $amount;
    }
}
