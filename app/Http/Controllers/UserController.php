<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\ViewAllUsersRequest;
use App\Http\Requests\Users\ViewSpecifiedUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{

    protected UserService $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    /**
     * Lista todos os usuários (aceita paginação, por padrão exibe 10)
     */
    public function index(ViewAllUsersRequest $request)
    {
        $paginate = $request->getPaginate();
        $users = User::with('cards')->paginate($paginate);
        return UserResource::collection($users->getCollection());
    }

    /**
     * Cria um novo usuário
     * @throws \Throwable
     */
    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->store($request->getName(), $request->getEmail(), $request->getPassword(), $request->getType());
        return new UserResource($user);
    }

    /**
     * Mostra um usuário específico
     */
    public function show(ViewSpecifiedUserRequest $request, User $user)
    {
        return new UserResource($user->load('cards'));
    }

    /**
     * Atualiza um usuário específico
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->getName(), $request->getEmail(), $request->getPassword(), $request->getType());
        return new UserResource($user);
    }

    /**
     * Deleta um usuário específico
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}
