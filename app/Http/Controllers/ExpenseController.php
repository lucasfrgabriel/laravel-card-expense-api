<?php

namespace App\Http\Controllers;

use App\Events\NewExpenseEvent;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;
    public function __construct(ExpenseService $expenseService){
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Expense::class);
        $expenses = Expense::all();
        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(ExpenseRequest $request): JsonResponse|ExpenseResource
    {
        $this->authorize('create', Expense::class);
        DB::beginTransaction();

        try{
            $validated = $request->validated();
            $data = array_merge($validated, ['date' => date_create(now())->format('Y-m-d')]);

            $expense = $this->expenseService->store($data);

            event(new NewExpenseEvent($data));

            DB::commit();

            return new ExpenseResource($expense);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing expense.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->noContent();
    }
}
