<?php

namespace App\Http\Controllers;

use App\Enums\CardStatusEnum;
use App\Http\Requests\CardDepositRequest;
use App\Http\Requests\CardStatusRequest;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    protected CardService $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Card::class);
        $cards = Card::with('expenses')->get();
        return CardResource::collection($cards);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CreateCardRequest $request)
    {
        $this->authorize('create', Card::class);
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $data = array_merge($validated, ['expenses' => []], ['balance' => 0]);

            $card = $this->cardService->store($data);
            $card->load('expenses');

            DB::commit();
            return new CardResource($card);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    /**
     * Realiza um depósito no cartão
     * @throws \Throwable
     */
    public function deposit(CardDepositRequest $request, Card $card): JsonResponse|CardResource
    {
        $this->authorize('deposit', $card);
        DB::beginTransaction();

        try{
            $validated = $request->validated();

            $card = $this->cardService->deposit($card, $validated['amount']);
            $card->load('expenses');

            DB::commit();
            return new CardResource($card);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing deposit.',
                'error' => $e->getMessage()
            ],400);
        }
    }

    /**
     * @throws \Throwable
     */
    public function changeStatus(CardStatusRequest $request, Card $card): JsonResponse|CardResource
    {
        $this->authorize('changeStatus', $card);
        DB::beginTransaction();

        try{
            $validated = $request->validated();
            $newStatus = CardStatusEnum::from($validated['status']);

            $card = $this->cardService->changeStatus($card, $newStatus);
            $card->load('expenses');

            DB::commit();
            return new CardResource($card);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing change status.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Card $card)
    {
        $this->authorize('view', $card);
        $card->load('expenses');
        return new CardResource($card);
    }


    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(UpdateCardRequest $request, Card $card)
    {
        $this->authorize('update', $card);
        DB::beginTransaction();

        try{
            $validated = $request->validated();

            $card = $this->cardService->update($card, $validated);
            $card->load('expenses');

            DB::commit();
            return new CardResource($card);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Error processing update.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Card $card)
    {
        $this->authorize('delete', $card);
        $card->delete();
        return response()->noContent();
    }
}
