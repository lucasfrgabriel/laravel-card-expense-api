<?php

namespace App\Services;

use App\Enums\CardStatusEnum;
use App\Exceptions\CardNotCreatedException;
use App\Exceptions\DepositFailedException;
use App\Exceptions\InactiveCardException;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\InvalidCardNumberException;
use App\Models\Card;
use App\Repositories\CardRepository;
use Illuminate\Support\Facades\DB;

class CardService
{
    private CardRepository $cardRepository;
    public function __construct(CardRepository $cardRepository){
        $this->cardRepository = $cardRepository;
    }

    public function store(array $data): Card
    {
        $cardNumber = $data['number'];

        if(!Utils::isCardValid($cardNumber)) {
            throw new InvalidCardNumberException();
        }

        try {
            DB::beginTransaction();

            $newCard = $this->cardRepository->create($data);

            DB::commit();

            return $newCard->load('expenses');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new CardNotCreatedException($e);
        }
    }

    public function deposit (Card $card, float $amount): Card
    {
        if($card->status != CardStatusEnum::Ativo){
            throw new InactiveCardException();
        }

        if($amount <= 0){
            throw new InvalidAmountException();
        }

        try{
            DB::beginTransaction();

            $card->balance += $amount;
            $card->save();

            DB::commit();

            return $card->load('expenses');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new DepositFailedException($e);
        }
    }

    public function changeStatus(Card $card, array $data): Card
    {
        $newStatus = CardStatusEnum::from($data['status']);
        try{
            DB::beginTransaction();

            $card->status = $newStatus;
            $card->save();

            DB::commit();

            return $card->load('expenses');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new CardNotUpdatedException($e);
        }


        return $card;
    }

    /**
     * @throws \Exception
     */
    public function update(Card $card, array $data): Card
    {
        if($data['number']){

            $cardNumber = $data['number'];
            if(!Utils::isCardValid($cardNumber)) {
                throw new InvalidCardNumberException();
            }
        }

        $card->update($data);
        return $card;
    }
}
