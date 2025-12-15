<?php

namespace App\Repositories;

use App\Models\Card;
use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findUserById(?int $id): ?User
    {
        if (is_null($id)) {
            return null;
        }
        return User::find($id);
    }

    public function findUserByCardId(int $cardId): ?User
    {
        $card = Card::find($cardId);

        if ($card) {
            return $card->user;
        }

        return null;
    }
}
