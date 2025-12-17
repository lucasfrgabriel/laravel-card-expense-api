<?php

namespace Tests\Unit\Services;

use App\Enums\CardStatusEnum;
use App\Events\NewExpenseEvent;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Card;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\CardRepository;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExpenseService $service;
    private ExpenseRepository&MockObject $mockExpenseRepository;
    private CardRepository&MockObject $mockCardRepository;

    public function setUp(): void
    {
        parent::setUp();
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
        Event::fake();

        $user = User::factory()->create();
        $card = Card::factory()->create([
            'user_id' => $user->id,
            'number'  => '5599406865348101',
            'balance' => 100.00,
            'status'  => CardStatusEnum::Ativo
        ]);

        $amount = 90.00;
        $description = 'test valid expense';

        $this->mockCardRepository->expects($this->once())
            ->method('find')
            ->with($card->id)
            ->willReturn($card);

        $expectedExpense = new Expense([
            'card_id'     => $card->id,
            'amount'      => $amount,
            'description' => $description
        ]);

        $this->mockExpenseRepository->expects($this->once())
            ->method('create')
            ->with($card->id, $amount, $description)
            ->willReturn($expectedExpense);

        $createdExpense = $this->service->store($card->id, $amount, $description);

        $this->assertEquals($expectedExpense->amount, $createdExpense->amount);
        $this->assertEquals($expectedExpense->description, $createdExpense->description);
        $this->assertEquals($card->id, $createdExpense->card_id);

        $this->assertEquals(10.00, $card->balance);

        Event::assertDispatched(NewExpenseEvent::class);
    }

    public function test_store_invalid_expense(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'user_id' => $user->id,
            'number'  => '5599406865348101',
            'balance' => 100.00,
            'status'  => CardStatusEnum::Ativo
        ]);

        $amount = 200.00;
        $description = 'test valid expense';

        $this->mockCardRepository->expects($this->once())
            ->method('find')
            ->with($card->id)
            ->willReturn($card);

        $this->expectException(InsufficientBalanceException::class);
        $this->expectExceptionMessage('Saldo insuficiente.');

        $this->service->store($card->id, $amount, $description);
    }
}
