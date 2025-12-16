<?php

namespace App\Services;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Exceptions\CardNotCreatedException;
use App\Exceptions\CardNotUpdatedException;
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

    public function store(string $number, CardStatusEnum $status, CardBrandEnum $brand, int $user_id): Card
    {
        if(!Utils::isCardValid($number)) {
            throw new InvalidCardNumberException();
        }

        try {
            DB::beginTransaction();

            $newCard = $this->cardRepository->create($number, $status, $brand, $user_id);

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

    public function changeStatus(Card $card, CardStatusEnum $newStatus): Card
    {
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
    }

    /**
     * @throws \Exception
     */
    public function update(Card $card, string|null $number, CardStatusEnum|null $status, CardBrandEnum|null $brand): Card
    {
        $data = [];
        if($number){
            if(!Utils::isCardValid($number)) {
                throw new InvalidCardNumberException();
            }
            $data['number'] = $number;
        }
        if($status){
            $data['status'] = $status;
        }
        if($brand){
            $data['brand'] = $brand;
        }

        try{
            DB::beginTransaction();

            $card->update($data);

            DB::commit();

            return $card->load('expenses');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new CardNotUpdatedException($e);
        }
    }
}
