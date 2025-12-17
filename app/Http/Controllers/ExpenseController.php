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
     * Lista todas as despesas (aceita paginação, por padrão exibe 10)
     */
    public function index(ViewAllExpensesRequest $request)
    {
        $paginate = $request->getPaginate();
        $expenses = Expense::paginate($paginate);
        return ExpenseResource::collection($expenses->getCollection());
    }

    /**
     * Cria uma nova despesa
     * @throws \Throwable
     */
    public function store(CreateExpenseRequest $request): JsonResponse|ExpenseResource
    {
        $expense = $this->expenseService->store($request->getCardId(), $request->getAmount(), $request->getDescription());
        return new ExpenseResource($expense);
    }

    /**
     * Deleta uma despesa específica
     */
    public function destroy(DeleteExpenseRequest $request, Expense $expense)
    {
        $expense->delete();
        return response()->noContent();
    }
}
