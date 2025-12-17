<?php

namespace Tests\Unit\Services;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Exceptions\Cards\InvalidCardNumberException;
use App\Exceptions\InvalidAmountException;
use App\Models\Card;
use App\Models\User;
use App\Repositories\CardRepository;
use App\Services\CardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CardServiceTest extends TestCase
{

    use RefreshDatabase;
    private CardService $service;
    private CardRepository&MockObject $mockCardRepository;

    protected function setUp(): void{
        parent::setUp();
        $this->mockCardRepository = $this->createMock(CardRepository::class);
        $this->service = new CardService($this->mockCardRepository);
    }

    public function test_deposit_fails_with_invalid_amount(): void
    {
        $card  = new Card();
        $card->balance = 0;
        $card->status = CardStatusEnum::Ativo;

        $this->expectException(InvalidAmountException::class);

        $depositAmount = -2;

        $this->service->deposit($card, $depositAmount);
    }

    public function test_deposit_valid_amount(): void
    {
        $depositAmount = 200;

        $user = User::factory()->create();
        $card = Card::factory()->create([
            'number' => '5599406865348101',
            'user_id' => $user->id,
            'status' => CardStatusEnum::Ativo
        ]);

        $card = $this->service->deposit($card, $depositAmount);
        $this->assertEquals($depositAmount, $card->balance);
    }

    public function test_store_card_fails_with_invalid_card_number(): void
    {
        $this->expectException(InvalidCardNumberException::class);
        $this->expectExceptionMessage('O número do cartão não é válido.');

        $invalidCardNumber = 1234567812345678;

        $this->service->store($invalidCardNumber, CardStatusEnum::Ativo, CardBrandEnum::Visa, 1);
    }

    public function test_store_card_with_valid_card_number(): void
    {
        $user = User::factory()->create();
        $cardNumber = "1234567812345670";
        $status = CardStatusEnum::Ativo;
        $brand  = CardBrandEnum::Visa;

        $expectedCard = new Card(['number' => $cardNumber, 'status' => $status, 'brand' => $brand]);

        $this->mockCardRepository->expects($this->once())
            ->method('create')
            ->with(
                $cardNumber,
                $status,
                $brand,
                $user->id,
            )
            ->willReturn($expectedCard);

        $createdCard = $this->service->store($cardNumber, $status, $brand, $user->id);

        $this->assertEquals($cardNumber, $createdCard->number);
    }

    public function test_store_valid_card(): void
    {
        $cardNumber = "1234567812345670";
        $balance = 100;
        $status = CardStatusEnum::Ativo;
        $brand = CardBrandEnum::Visa;
        $user_id = 1;

        $expectedCard = new Card(['number' => $cardNumber, 'balance' => $balance, 'status' => $status, 'brand' => $brand, 'user_id' => $user_id]);

        $this->mockCardRepository->expects($this->once())
            ->method('create')
            ->with(
                $cardNumber,
                $status,
                $brand,
                $user_id,
            )
            ->willReturn($expectedCard);

        $createdCard = $this->service->store($cardNumber, $status, $brand, $user_id);

        $this->assertEquals($cardNumber, $createdCard->number);
        $this->assertEquals($balance, $createdCard->balance);
        $this->assertEquals($status, $createdCard->status);
        $this->assertEquals($brand, $createdCard->brand);
        $this->assertEquals($user_id, $createdCard->user_id);
    }


    public function test_change_card_status(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'number' => '5599406865348101',
            'user_id' => $user->id,
            'status' => CardStatusEnum::Ativo
        ]);

        $newStatus = CardStatusEnum::Bloqueado;

        $updatedCard = $this->service->changeStatus($card, $newStatus);

        $this->assertEquals($newStatus, $updatedCard->status);
        $this->assertTrue($updatedCard->relationLoaded('expenses'));
    }

    public function test_update_card(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'user_id' => $user->id,
            'number' => "5128976451784323",
            'status' => CardStatusEnum::Ativo,
            'brand' => CardBrandEnum::Visa
        ]);

        $newNumber = "5359338613583525";
        $newStatus = CardStatusEnum::Bloqueado;
        $newBrand = CardBrandEnum::MasterCard;

        $updatedCard = $this->service->update($card, $newNumber, $newStatus, $newBrand);

        $this->assertEquals($newNumber, $updatedCard->number, 'O número deve ser atualizado.');
        $this->assertEquals($newStatus, $updatedCard->status, 'O status deve ser atualizado.');
        $this->assertEquals($newBrand, $updatedCard->brand, 'A bandeira deve ser atualizada.');
    }
}
