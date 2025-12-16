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
     * Display a listing of the resource.
     */
    public function index(ViewAllUsersRequest $request)
    {
        $paginate = $request->getPaginate();
        $users = User::with('cards')->paginate($paginate);
        return UserResource::collection($users->getCollection());
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->store($request->getName(), $request->getEmail(), $request->getPassword(), $request->getType());
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(ViewSpecifiedUserRequest $request, User $user)
    {
        return new UserResource($user->load('cards'));
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->getName(), $request->getEmail(), $request->getPassword(), $request->getType());
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}
