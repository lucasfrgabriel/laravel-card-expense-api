<?php

namespace App\Services;

use App\Enums\CardStatusEnum;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\InvalidCardNumberException;
use App\Models\Card;
use App\Repositories\CardRepository;

class CardService
{
    private CardRepository $cardRepository;
    public function __construct(CardRepository $cardRepository){
        $this->cardRepository = $cardRepository;
    }

    /**
     * @throws InvalidCardNumberException
     */
    public function store(array $data): Card
    {
        $cardNumber = $data['number'];

        if(!Utils::luhnCheck($cardNumber)) {
            throw new InvalidCardNumberException('O número do cartão não é válido.');
        }

        return $this->cardRepository->create($data);
    }

    /**
     * @throws InvalidAmountException
     */
    public function deposit (Card $card, float $amount): Card
    {
        if($amount <= 0){
            throw new InvalidAmountException('O valor de depósito não pode ser menor ou igual a 0');
        }

        $card->balance += $amount;
        $card->save();

        return $card;
    }

    public function changeStatus(Card $card, CardStatusEnum $status): Card
    {
        $card->status  = $status;
        $card->save();

        return $card;
    }

    /**
     * @throws \Exception
     */
    public function update(Card $card, array $data): Card
    {
        if($data['number']){

            $cardNumber = $data['number'];
            if(!Utils::luhnCheck($cardNumber)) {
                throw new InvalidCardNumberException('O número do cartão não é válido.');
            }
        }

        $card->update($data);
        return $card;
    }
}
