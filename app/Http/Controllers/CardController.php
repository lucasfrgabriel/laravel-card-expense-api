<?php

namespace App\Http\Controllers;

use App\Enums\CardStatusEnum;
use App\Http\Requests\CardDepositRequest;
use App\Http\Requests\CardStatusRequest;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use App\Repositories\UserRepository;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function __construct(protected CardService $cardService, protected UserRepository $userRepository){}

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
        $card = $this->cardService->store($request->returnData());
        return new CardResource($card);
    }

    /**
     * Realiza um depósito no cartão
     * @throws \Throwable
     */
    public function deposit(CardDepositRequest $request, Card $card): JsonResponse|CardResource
    {
        $request = $request->returnData();
        $card = $this->cardService->deposit($card, $request['amount']);
        return new CardResource($card);
    }

    /**
     * @throws \Throwable
     */
    public function changeStatus(CardStatusRequest $request, Card $card): JsonResponse|CardResource
    {
        $request = $request->returnData();
        $card = $this->cardService->changeStatus($card, $request);
        return new CardResource($card);

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
