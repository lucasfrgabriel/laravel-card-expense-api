<?php

namespace Tests\Feature;

use App\Enums\CardStatusEnum;
use App\Enums\UserTypeEnum;
use App\Mail\NewExpenseAlert;
use App\Models\Card;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    //index, store e destroy

    public function test_admin_can_view_all_expenses(): void
    {
        $users = User::factory(2)->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $cardNumbers = [
            '1234567812345670',
            '5128976451784323',
        ];

        $users->each(function (User $user, int $key) use ($cardNumbers) {
            $number = $cardNumbers[$key];
            $card = Card::factory()->create(['user_id' => $user->id,'number' => $number,]);
            $expense = Expense::factory()->create(['card_id' => $card->id]);
        });

        $response = $this->actingAs($admin)
            ->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => $this->expenseJsonStructure()
            ]);
    }

    public function test_comum_cannot_view_all_expenses(): void
    {
        $users = User::factory(2)->create();
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $cardNumbers = [
            '1234567812345670',
            '5128976451784323',
        ];

        $users->each(function (User $user, int $key) use ($cardNumbers) {
            $number = $cardNumbers[$key];
            $card = Card::factory()->create(['user_id' => $user->id,'number' => $number]);
            $expense = Expense::factory()->create(['card_id' => $card->id]);
        });

        $response = $this->actingAs($user)
            ->getJson('/api/expenses');

        $this->assertForbidden($response);
    }

    public function test_admin_can_delete_any_user_expenses(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670]);
        $expense = Expense::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(204);
    }

    public function test_comum_can_delete_own_expenses(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670]);
        $expense = Expense::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(204);
    }

    public function test_comum_cannot_delete_any_user_expenses(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();

        $card = Card::factory()->create(['user_id' => $otherUser->id,'number' => 1234567812345670]);
        $expense = Expense::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/expenses/' . $expense->id);

        $this->assertForbidden($response);
    }

    public function test_store_expense_fails_when_amount_exceeds_balance(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 10, 'status' => CardStatusEnum::Ativo]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'Saldo insuficiente.',
            ]);
    }

    public function test_store_expense_fails_when_card_is_blocked(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 10, 'status' => CardStatusEnum::Bloqueado]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O cartão não está ativo e não pode ser utilizado para novas transações.',
            ]);
    }

    public function test_store_expense_fails_when_card_is_canceled(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 10, 'status' => CardStatusEnum::Cancelado]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O cartão não está ativo e não pode ser utilizado para novas transações.',
            ]);
    }

    public function test_store_expense_fails_with_invalid_card_id(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $expenseData = ['card_id' => -1, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('card_id')
            ->assertJsonCount(1, 'errors');
    }

    public function test_admin_can_store_expense_for_any_user(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 100, 'status' => CardStatusEnum::Ativo]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($admin)
            ->postJson('/api/expenses', $expenseData);

        $this->assertCreated($response, $expenseData);
    }

    public function test_comum_can_store_expense_for_own_card(): void
    {
        Mail::fake();

        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 100, 'status' => CardStatusEnum::Ativo]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $this->assertCreated($response, $expenseData);
    }

    public function test_comum_cannot_store_expense_for_other_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();
        $card = Card::factory()->create(['user_id' => $otherUser->id,'number' => 1234567812345670, 'balance' => 100, 'status' => CardStatusEnum::Ativo]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        $this->assertForbidden($response);
    }

    public function test_store_expense_dispatches_email_event(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id,'number' => 1234567812345670, 'balance' => 100, 'status' => CardStatusEnum::Ativo]);

        $expenseData = ['card_id' => $card->id, 'amount' => 20, 'description' => 'teste de despesa'];

        $response = $this->actingAs($user)
            ->postJson('/api/expenses', $expenseData);

        Mail::assertSentCount(1);
        Mail::assertSent(NewExpenseAlert::class, function (NewExpenseAlert $mail) use ($user, $admin) {

            $isToOwner = $mail->hasTo($user->email);
            $isCcAdmin = $mail->hasCc($admin->email);

            return $isToOwner && $isCcAdmin;
        });
    }


    private function expenseJsonStructure(): array
    {
        return [
            'id',
            'card_id',
            'amount',
            'description',
            'date',
        ];
    }

    private function assertExpenseValues(AssertableJson $json, array $expectedData): AssertableJson
    {
        $amount = number_format($expectedData['amount'], 2, ',', '');
        $date = now()->format('Y-m-d');

        $json->where('id', $expectedData['id'] ?? 1)
            ->where('card_id', $expectedData['card_id'])
            ->where('amount', $amount)
            ->where('description', $expectedData['description'])
            ->where('date', $date);

        return $json;
    }

    private function assertForbidden(TestResponse $response): TestResponse
    {
        return $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'This action is unauthorized.',
            ]);
    }

    private function assertOk(TestResponse $response, array $expectedData): TestResponse
    {
        return $response->assertStatus(200)
            ->assertJsonStructure($this->expenseJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertExpenseValues($json, $expectedData)
            );
    }

    private function assertCreated(TestResponse $response, array $expectedData): TestResponse
    {
        return $response->assertStatus(201)
            ->assertJsonStructure($this->expenseJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertExpenseValues($json, $expectedData)
            );
    }
}
