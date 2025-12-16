<?php

namespace Tests\Unit\Services;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Exceptions\Cards\InvalidCardNumberException;
use App\Exceptions\InvalidAmountException;
use App\Models\Card;
use App\Repositories\CardRepository;
use App\Services\CardService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CardServiceTest extends TestCase
{
    private CardService $service;
    private CardRepository&MockObject $mockCardRepository;

    protected function setUp(): void{
        $this->mockCardRepository = $this->createMock(CardRepository::class);
        $this->service = new CardService($this->mockCardRepository);
    }

    public function test_deposit_fails_with_invalid_amount(): void
    {
        $card  = new Card();
        $card->balance = 0;
        $card->status = CardStatusEnum::Ativo;

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('O valor de depósito não pode ser menor ou igual a 0');

        $depositAmount = -2;

        $this->service->deposit($card, $depositAmount);
    }

    public function test_deposit_valid_amount(): void
    {
        $depositAmount = 200;

        $mockCard = $this->getMockBuilder(Card::class)
            ->onlyMethods(['save'])->getMock();

        $mockCard->balance = 0;
        $mockCard->status = CardStatusEnum::Ativo;

        $mockCard->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $card = $this->service->deposit($mockCard, $depositAmount);
        $this->assertEquals($depositAmount, $card->balance);
    }

    public function test_store_card_fails_with_invalid_card_number(): void
    {
        $this->expectException(InvalidCardNumberException::class);
        $this->expectExceptionMessage('O número do cartão não é válido.');

        $invalidCardNumber = 1234567812345678;
        $data = ['number' => $invalidCardNumber];

        $this->service->store($data);
    }
/*
    public function test_store_card_with_valid_card_number(): void
    {
        $cardNumber = "1234567812345670";
        $data = ['number' => $cardNumber];

        $expectedCard = new Card(['number' => '1234567812345670', 'status' => CardStatusEnum::Ativo]);

        $this->mockCardRepository->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn($expectedCard);

        $createdCard = $this->service->store($data);

        $this->assertEquals($cardNumber, $createdCard->number);
    }

    public function test_store_valid_card(): void
    {
        $cardNumber = "1234567812345670";
        $balance = 100;
        $status = CardStatusEnum::Ativo;
        $brand = CardBrandEnum::Visa;
        $user_id = 1;
        $data = ['number' => $cardNumber, 'balance' => $balance, 'status' => $status, 'brand' => $brand, 'user_id' => $user_id];

        $expectedCard = new Card($data);

        $this->mockCardRepository->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn($expectedCard);

        $createdCard = $this->service->store($data);

        $this->assertEquals($cardNumber, $createdCard->number);
        $this->assertEquals($balance, $createdCard->balance);
        $this->assertEquals($status, $createdCard->status);
        $this->assertEquals($brand, $createdCard->brand);
        $this->assertEquals($user_id, $createdCard->user_id);
    }
*/

    public function test_change_card_status(): void
    {
        $initialStatus = CardStatusEnum::Ativo;
        $newStatus = CardStatusEnum::Bloqueado;

        $mockCard = $this->getMockBuilder(Card::class)
            ->onlyMethods(['save'])->getMock();

        $mockCard->status = $initialStatus;

        $mockCard->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->service->changeStatus($mockCard, $newStatus);

        $this->assertEquals($newStatus, $mockCard->status);
    }

    public function test_update_card(): void
    {
        $initialCardData = ['number' => "5128976451784323", 'balance' => 100, 'status' => CardStatusEnum::Ativo, 'brand' => CardBrandEnum::Visa, 'user_id' => 1];
        $newCardData = ['number' => "5359338613583525", 'status' => CardStatusEnum::Bloqueado, 'brand' => CardBrandEnum::MasterCard];
        $expectedData = ['number' => "5359338613583525", 'balance' => 100, 'status' => CardStatusEnum::Bloqueado, 'brand' => CardBrandEnum::MasterCard, 'user_id' => 1];

        $expectedCard = new Card($expectedData);

        $mockCard = $this->getMockBuilder(Card::class)
            ->onlyMethods(['update'])->getMock();

        foreach ($initialCardData as $key => $value) {
            $mockCard->{$key} = $value;
        }

        $mockCard->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $mockCard->number = $newCardData['number'];
        $mockCard->status = $newCardData['status'];
        $mockCard->brand = $newCardData['brand'];

        $updatedCard = $this->service->update($mockCard, $newCardData);

        $this->assertSame($mockCard, $updatedCard);

        $this->assertEquals($expectedCard->number, $updatedCard->number, 'O número deve ser atualizado.');
        $this->assertEquals($expectedCard->status, $updatedCard->status, 'O status deve ser atualizado.');
        $this->assertEquals($expectedCard->brand, $updatedCard->brand, 'A bandeira deve ser atualizada.');

        $this->assertEquals($expectedCard->balance, $updatedCard->balance, 'O saldo não deve mudar.');
        $this->assertEquals($expectedCard->user_id, $updatedCard->user_id, 'O ID do usuário não deve mudar.');
    }
}
