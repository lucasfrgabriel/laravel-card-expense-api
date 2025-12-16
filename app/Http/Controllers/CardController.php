<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cards\CardDepositRequest;
use App\Http\Requests\Cards\CardStatusRequest;
use App\Http\Requests\Cards\CreateCardRequest;
use App\Http\Requests\Cards\DeleteCardRequest;
use App\Http\Requests\Cards\UpdateCardRequest;
use App\Http\Requests\Cards\ViewAllCardsRequest;
use App\Http\Requests\Cards\ViewSpecifiedCardRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use App\Repositories\UserRepository;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;

class CardController extends Controller
{
    public function __construct(protected CardService $cardService, protected UserRepository $userRepository){}

    /**
     * Display a listing of the resource.
     */
    public function index(ViewAllCardsRequest $request)
    {
        $paginate = $request->getPaginate();
        $cards = Card::with('expenses')->paginate($paginate);
        return CardResource::collection($cards->getCollection());
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(CreateCardRequest $request)
    {
        $card = $this->cardService->store($request->getNumber(), $request->getStatus(), $request->getBrand(), $request->getUserId());
        return new CardResource($card);
    }

    /**
     * Realiza um depósito no cartão
     * @throws \Throwable
     */
    public function deposit(CardDepositRequest $request, Card $card): JsonResponse|CardResource
    {
        $card = $this->cardService->deposit($card, $request->getAmount());
        return new CardResource($card);
    }

    /**
     * @throws \Throwable
     */
    public function changeStatus(CardStatusRequest $request, Card $card): JsonResponse|CardResource
    {
        $card = $this->cardService->changeStatus($card, $request->getStatus());
        return new CardResource($card);
    }

    /**
     * Display the specified resource.
     */
    public function show(ViewSpecifiedCardRequest $request, Card $card)
    {
        return new CardResource($card->load('expenses'));
    }


    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(UpdateCardRequest $request, Card $card)
    {
        $card = $this->cardService->update($card, $request->getNumber(), $request->getStatus(), $request->getBrand());
        return new CardResource($card);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteCardRequest $request, Card $card)
    {
        $card->delete();
        return response()->noContent();
    }
}
