<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository
{
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }
}
