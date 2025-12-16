<?php

namespace App\Services;

use App\Enums\UserTypeEnum;
use App\Exceptions\Users\UserNotCreatedException;
use App\Exceptions\Users\UserNotUpdatedException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class UserService
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function store(string $name, string $email, string $password, UserTypeEnum $type): User
    {
        try{
            DB::beginTransaction();

            $user = $this->userRepository->create($name, $email, $password, $type);

            DB::commit();

            return $user->load('cards');
        } catch(\Throwable $e){
            DB::rollBack();
            throw new UserNotCreatedException($e);
        }
    }

    public function update(User $user, string|null $name, string|null $email, string|null $password, UserTypeEnum|null $type): User
    {
        $data = [];

        if($name){
            $data['name'] = $name;
        }
        if($email){
            $data['email'] = $email;
        }
        if($password){
            $data['password'] = $password;
        }
        if($type){
            $data['type'] = $type;
        }

        try{
            DB::beginTransaction();

            $user->update($data);

            DB::commit();

            return $user->load('cards');
        } catch(\Throwable $e){
            DB::rollBack();
            throw new UserNotUpdatedException($e);
        }
    }
}
