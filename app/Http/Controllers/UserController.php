<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    protected UserService $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('cards')->get();
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CreateUserRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $user = $this->userService->store($validated);
            $user->load('cards');

            DB::commit();
            return new UserResource($user);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load('cards');
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        DB::beginTransaction();

        try{
            $validated = $request->validated();
            $this->userService->update($user, $validated);
            $user->load('cards');

            DB::commit();
            return new UserResource($user);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing update.',
                'errors' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->noContent();
    }
}
