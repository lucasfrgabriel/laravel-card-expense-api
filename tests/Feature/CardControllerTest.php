<?php

namespace Tests\Feature;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use function Psy\debug;

class CardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_invalid_card(): void
    {
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $cardData = ['number' => '1234567812345670', 'status' => 'invalido', 'brand' => 'nenhuma', 'user_id' => null];

        $response = $this->actingAs($admin)
            ->postJson('/api/cards', $cardData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
                'brand',
                'user_id'
            ])
            ->assertJsonCount(3, 'errors');
    }

    public function test_store_card_with_invalid_number(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $cardData = ['number' => '1234567812345678', 'status' => CardStatusEnum::Ativo, 'brand' => CardBrandEnum::Visa, 'user_id' => $user->id];

        $response = $this->actingAs($admin)
            ->postJson('/api/cards', $cardData);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O número do cartão não é válido.',
            ]);
    }

    public function test_admin_can_store_valid_card_to_any_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $cardData = ['number' => '1234567812345670', 'status' => CardStatusEnum::Ativo, 'brand' => CardBrandEnum::Visa, 'user_id' => $user->id];

        $response = $this->actingAs($admin)
            ->postJson('/api/cards', $cardData);

        $this->assertCreated($response, $cardData);
    }

    public function test_comum_can_store_valid_card_to_own_account(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $cardData = ['number' => '1234567812345670', 'status' => CardStatusEnum::Ativo, 'brand' => CardBrandEnum::Visa, 'user_id' => $user->id];

        $response = $this->actingAs($user)
            ->postJson('/api/cards', $cardData);

        $this->assertCreated($response, $cardData);
    }

    public function test_comum_cannot_add_card_to_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();

        $cardData = ['number' => '1234567812345670', 'status' => CardStatusEnum::Ativo, 'brand' => CardBrandEnum::Visa, 'user_id' => $otherUser->id];

        $response = $this->actingAs($user)
            ->postJson('/api/cards', $cardData);

        $this->assertForbidden($response);
    }

    public function test_update_invalid_card_status(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $cardNewStatus = ['status' => 'invalido'];

        $response = $this->actingAs($admin)
            ->patchJson('/api/cards/' . $card->id . '/status', $cardNewStatus);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status')
            ->assertJsonCount(1, 'errors');
    }

    public function test_admin_can_update_card_status_for_any_user(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $cardNewStatus = ['status' => CardStatusEnum::Cancelado];

        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'balance' => $card->balance, 'status' => $cardNewStatus['status'], 'brand' => $card->brand];

        $response = $this->actingAs($admin)
            ->patchJson('/api/cards/' . $card->id . '/status', $cardNewStatus);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_can_update_own_card_status(): void{
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $cardNewStatus = ['status' => CardStatusEnum::Cancelado];

        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'balance' => $card->balance, 'status' => $cardNewStatus['status'], 'brand' => $card->brand];

        $response = $this->actingAs($user)
            ->patchJson('/api/cards/' . $card->id . '/status', $cardNewStatus);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_cannot_update_other_user_card_status(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $otherUser->id, 'status' => CardStatusEnum::Ativo]);
        $cardNewStatus = ['status' => CardStatusEnum::Cancelado];

        $response = $this->actingAs($user)
            ->patchJson('/api/cards/' . $card->id . '/status', $cardNewStatus);

        $this->assertForbidden($response);
    }

    public function test_deposit_fails_with_invalid_amount(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $newDeposit = ['amount' => -10];

        $response = $this->actingAs($user)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('amount')
            ->assertJsonCount(1, 'errors');
    }

    public function test_deposit_fails_when_card_is_blocked(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Bloqueado]);
        $newDeposit = ['amount' => 10];

        $response = $this->actingAs($user)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        Log::debug($response->getContent());

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O cartão não está ativo e não pode ser utilizado para novas transações.',
            ]);
    }

    public function test_deposit_fails_when_card_is_canceled(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Cancelado]);
        $newDeposit = ['amount' => 10];

        $response = $this->actingAs($user)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O cartão não está ativo e não pode ser utilizado para novas transações.',
            ]);
    }

    public function test_admin_can_deposit_to_any_user_card(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $newDeposit = ['amount' => 100];

        $newBalance = $newDeposit['amount'] + $card->balance;
        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'balance' => $newBalance, 'status' => $card->status, 'brand' => $card->brand];

        $response = $this->actingAs($admin)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_can_deposit_to_own_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id, 'status' => CardStatusEnum::Ativo]);
        $newDeposit = ['amount' => 100];

        $newBalance = $newDeposit['amount'] + $card->balance;
        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'balance' => $newBalance, 'status' => $card->status, 'brand' => $card->brand];

        $response = $this->actingAs($user)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_cannot_deposit_to_other_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $otherUser->id]);
        $newDeposit = ['amount' => 100];

        $newBalance = $newDeposit['amount'] + $card->balance;
        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'balance' => $newBalance, 'status' => $card->status, 'brand' => $card->brand];

        $response = $this->actingAs($user)
            ->postJson('/api/cards/' . $card->id . '/deposit', $newDeposit);

        $this->assertForbidden($response);
    }

    public function test_update_card_with_invalid_card_number(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $card = Card::factory()->create(['number' => '1234567812345670', 'user_id' => $user->id]);
        $newNumber = ['number' => '1234567812345678'];

        $response = $this->actingAs($user)
            ->patchJson('/api/cards/' . $card->id, $newNumber);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'O número do cartão não é válido.',
            ]);
    }

    public function test_admin_can_view_all_cards(): void
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
        });

        $response = $this->actingAs($admin)
            ->getJson('/api/cards');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => $this->cardJsonStructure()
            ]);
    }

    public function test_comum_cannot_view_all_cards(): void
    {
        $users = User::factory(2)->create();
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $cardNumbers = [
            '1234567812345670',
            '5128976451784323',
        ];

        $users->each(function (User $user, int $key) use ($cardNumbers) {
            $number = $cardNumbers[$key];
            $card = Card::factory()->create(['user_id' => $user->id,'number' => $number,]);
        });

        $response = $this->actingAs($user)
            ->getJson('/api/cards');

        $this->assertForbidden($response);
    }

    public function test_admin_can_view_any_user_card(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $card = Card::factory()->create(['user_id' => $user->id, 'number' => '1234567812345670']);

        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'brand' => $card->brand, 'status' => $card->status];

        $response = $this->actingAs($admin)
            ->getJson('/api/cards/' . $card->id);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_can_view_own_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id, 'number' => '1234567812345670']);

        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'brand' => $card->brand, 'status' => $card->status];

        $response = $this->actingAs($user)
            ->getJson('/api/cards/' . $card->id);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_cannot_view_other_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();
        $card = Card::factory()->create(['user_id' => $otherUser->id, 'number' => '1234567812345670']);

        $expectedData = ['number' => $card->number, 'user_id' => $card->user_id, 'brand' => $card->brand, 'status' => $card->status];

        $response = $this->actingAs($user)
            ->getJson('/api/cards/' . $card->id);

        $this->assertForbidden($response);
    }

    public function test_admin_can_delete_any_user_card(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $card = Card::factory()->create(['user_id' => $user->id, 'number' => '1234567812345670']);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/cards/' . $card->id);

        $response->assertStatus(204);
    }

    public function test_comum_can_delete_own_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $card = Card::factory()->create(['user_id' => $user->id, 'number' => '1234567812345670']);

        $response = $this->actingAs($user)
            ->deleteJson('/api/cards/' . $card->id);

        $response->assertStatus(204);
    }

    public function test_comum_cannot_delete_any_user_card(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create();
        $card = Card::factory()->create(['user_id' => $otherUser->id, 'number' => '1234567812345670']);

        $response = $this->actingAs($user)
            ->deleteJson('/api/cards/' . $card->id);

        $this->assertForbidden($response);
    }

    private function cardJsonStructure(): array
    {
        return [
            'id',
            'user_id',
            'number',
            'balance',
            'status',
            'brand',
            'expenses',
        ];
    }

    private function assertCardValues(AssertableJson $json, array $expectedData): AssertableJson
    {
        $balanceValue = $expectedData['balance'] ?? 0;
        $balance = number_format($balanceValue, 2, ',', '');

        $json->where('id', $expectedData['id'] ?? 1)
            ->where('user_id', $expectedData['user_id'])
            ->where('balance', $balance)
            ->where('number', $expectedData['number'])
            ->where('status', $expectedData['status'])
            ->where('brand', $expectedData['brand'])
            ->where('expenses', []);

        return $json;
    }

    private function assertForbidden(TestResponse $response): TestResponse
    {
        return $response->assertStatus(403)
            ->assertJsonFragment([
                'error' => 'This action is unauthorized.',
            ]);
    }

    private function assertOk(TestResponse $response, array $expectedData): TestResponse
    {
        return $response->assertStatus(200)
            ->assertJsonStructure($this->cardJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertCardValues($json, $expectedData)
            );
    }

    private function assertCreated(TestResponse $response, array $expectedData): TestResponse
    {
        return $response->assertStatus(201)
            ->assertJsonStructure($this->cardJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertCardValues($json, $expectedData)
            );
    }
}
