<?php

namespace App\Repositories;

use App\Models\Card;

class CardRepository
{
    public function create(array $data): Card
    {
        return Card::create($data);
    }

    public function find(int $id): ?Card
    {
        return Card::find($id);
    }
}
