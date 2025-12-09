<?php

namespace Tests\Unit\Services;

use App\Enums\CardStatusEnum;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Card;
use App\Models\Expense;
use App\Repositories\CardRepository;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExpenseServiceTest extends TestCase
{
    private ExpenseService $service;
    private ExpenseRepository&MockObject $mockExpenseRepository;
    private CardRepository&MockObject $mockCardRepository;

    public function setUp(): void
    {
        $this->mockExpenseRepository = $this->createMock(ExpenseRepository::class);
        $this->mockCardRepository = $this->createMock(CardRepository::class);
        $this->service = new ExpenseService($this->mockExpenseRepository, $this->mockCardRepository);
    }

    public function test_has_balance_function_when_card_has_enough_balance(): void
    {
        $card = new Card();
        $cardBalance = 100;
        $expenseAmount = 90;

        $card->balance = $cardBalance;

        $this->assertTrue($this->service->hasBalance($card, $expenseAmount));
    }

    public function test_has_balance_function_when_card_does_not_have_enough_balance(): void
    {
        $card = new Card();
        $cardBalance = 100;
        $expenseAmount = 200;

        $card->balance = $cardBalance;

        $this->assertFalse($this->service->hasBalance($card, $expenseAmount));
    }

    public function test_store_valid_expense(): void
    {
        $mockCard = $this->getMockBuilder(Card::class)
            ->onlyMethods(['save'])->getMock();

        $mockCard->id = 1;
        $mockCard->balance = 100;
        $mockCard->status = CardStatusEnum::Ativo;

        $expenseData = ['card_id' => $mockCard->id, 'amount' => 90, 'description' => 'test valid expense'];
        $expectedExpense = new Expense($expenseData);

        $this->mockCardRepository->expects($this->once())
            ->method('find')
            ->with($expectedExpense['card_id'])
            ->willReturn($mockCard);

        $this->mockExpenseRepository->expects($this->once())
            ->method('create')
            ->with($expenseData)
            ->willReturn($expectedExpense);

        $mockCard->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $createdExpense = $this->service->store($expenseData);

        $this->assertEquals($expectedExpense, $createdExpense);
        $this->assertEquals($expectedExpense->card_id, $createdExpense->card_id);
        $this->assertEquals($expectedExpense->amount, $createdExpense->amount);
        $this->assertEquals($expectedExpense->description, $createdExpense-> description);
    }

    public function test_store_invalid_expense(): void
    {
        $mockCard = $this->getMockBuilder(Card::class)
            ->onlyMethods(['save'])->getMock();

        $mockCard->id = 1;
        $mockCard->balance = 100;
        $mockCard->status = CardStatusEnum::Ativo;

        $expenseData = ['card_id' => $mockCard->id, 'amount' => 200, 'description' => 'test valid expense'];
        $expectedExpense = new Expense($expenseData);

        $this->mockCardRepository->expects($this->once())
            ->method('find')
            ->with($expectedExpense['card_id'])
            ->willReturn($mockCard);

        $this->expectException(InsufficientBalanceException::class);
        $this->expectExceptionMessage('Saldo insuficiente.');

        $this->service->store($expenseData);
    }
}
