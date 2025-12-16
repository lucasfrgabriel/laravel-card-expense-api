<?php

namespace App\Repositories;

use App\Enums\UserTypeEnum;
use App\Models\Card;
use App\Models\User;

class UserRepository
{
    public function create(string $name, string $email, string $password, UserTypeEnum $type): User
    {
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'type' => $type
        ];

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
