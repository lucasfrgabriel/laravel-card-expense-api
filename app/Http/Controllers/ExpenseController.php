<?php

namespace App\Http\Controllers;

use App\Events\NewExpenseEvent;
use App\Http\Requests\Expenses\CreateExpenseRequest;
use App\Http\Requests\Expenses\DeleteExpenseRequest;
use App\Http\Requests\Expenses\ViewAllExpensesRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Repositories\UserRepository;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;
    protected UserRepository $userRepository;
    public function __construct(ExpenseService $expenseService, UserRepository $userRepository){
        $this->expenseService = $expenseService;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ViewAllExpensesRequest $request)
    {
        $paginate = $request->getPaginate();
        $expenses = Expense::paginate($paginate);
        return ExpenseResource::collection($expenses->getCollection());
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CreateExpenseRequest $request): JsonResponse|ExpenseResource
    {
        $expense = $this->expenseService->store($request->getCardId(), $request->getAmount(), $request->getDescription());
        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteExpenseRequest $request, Expense $expense)
    {
        $expense->delete();
        return response()->noContent();
    }
}
