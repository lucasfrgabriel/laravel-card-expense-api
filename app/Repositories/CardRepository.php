<?php

namespace App\Repositories;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Models\Card;

class CardRepository
{
    public function create(string $number, CardStatusEnum $status, CardBrandEnum $brand, int $user_id): Card
    {
        $data = [
            'number' => $number,
            'status' => $status,
            'brand' => $brand,
            'user_id' => $user_id,
            'expenses' => [],
            'balance' => 0
        ];

        return Card::create($data);
    }

    public function find(int $id): ?Card
    {
        return Card::find($id);
    }
}
