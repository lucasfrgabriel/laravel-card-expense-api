<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository
{
    public function create(int $card_id, float $amount, string $description): Expense
    {
        $data = [
            'card_id' => $card_id,
            'amount' => $amount,
            'description' => $description,
            'date' => date_create(now())->format('Y-m-d'),
        ];

        return Expense::create($data);
    }
}
