<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function store(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }
}
