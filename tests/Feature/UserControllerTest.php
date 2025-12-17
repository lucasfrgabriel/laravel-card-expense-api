<?php

namespace Tests\Feature;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_new_valid_user(): void
    {
        $userData = ['name' => 'Usuario teste', 'email' => 'teste@email.com', 'password' => '12345678', 'type' => UserTypeEnum::Comum];

        $response = $this->actingAsGuest()->postJson('/api/users/register', $userData);

        $response
            ->assertStatus(201)
            ->assertJsonStructure($this->userJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertUserValues($json, $userData)
            );
    }

    public function test_store_new_invalid_user(): void
    {

        $userData = ['name' => 'Usuario teste', 'email' => 'teste.email.com', 'password' => '1234', 'type' => 'invalid'];

        $response = $this->actingAsGuest()->postJson('/api/users/register', $userData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
                'type'
            ])
            ->assertJsonCount(3, 'errors');
    }

    public function test_admin_type_can_view_all_users(): void
    {
        User::factory(2)->create();
        $user = User::factory()->create(['type' => UserTypeEnum::Admin]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => $this->userJsonStructure()
            ]);
    }

    public function test_comum_type_cannot_view_all_users(): void
    {
        User::factory(2)->create();
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users');

        $this->assertForbidden($response);
    }

    public function test_admin_type_can_view_any_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $user->id);

        $expectedData = ['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type];

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_type_can_view_own_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $expectedData = ['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type];

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $user->id);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_type_cannot_view_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $otherUser->id);

        $this->assertForbidden($response);
    }

    public function test_admin_type_can_delete_any_user(): void
    {
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($admin)
            ->withSession(['banned' => false])
            ->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(204);
    }

    public function test_comum_type_can_delete_own_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(204);
    }

    public function test_comum_type_cannot_delete_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->deleteJson('/api/users/' . $otherUser->id);

        $this->assertForbidden($response);
    }

    public function test_admin_type_can_update_any_user(): void
    {
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $expectedData = ['user_id' => $user->id, 'name' => $userNewData['name'], 'email' => $user->email, 'type' => $user->type];

        $response = $this->actingAs($admin)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $user->id, $userNewData);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_type_can_update_own_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $expectedData = ['user_id' => $user->id, 'name' => $userNewData['name'], 'email' => $user->email, 'type' => $user->type];

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $user->id, $userNewData);

        $this->assertOk($response, $expectedData);
    }

    public function test_comum_type_cannot_update_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $otherUser->id, $userNewData);

        $this->assertForbidden($response);
    }

    private function userJsonStructure(): array
    {
        return [
            'id',
            'name',
            'email',
            'type',
            'cards',
        ];
    }

    private function assertUserValues(AssertableJson $json, array $expectedData): AssertableJson
    {
        $json->where('id', $expectedData['user_id'] ?? 1)
            ->where('name', $expectedData['name'])
            ->where('email', $expectedData['email'])
            ->where('type', $expectedData['type'])
            ->where('cards', []);

        return $json;
    }

    private function assertOk(TestResponse $response, array $expectedData): TestResponse
    {
        return $response->assertStatus(200)
            ->assertJsonStructure($this->userJsonStructure())
            ->assertJson(fn (AssertableJson $json) =>
            $this->assertUserValues($json, $expectedData)
            );
    }

    private function assertForbidden(TestResponse $response): TestResponse
    {
        return $response->assertStatus(403)
            ->assertJsonFragment([
                'error' => 'This action is unauthorized.',
            ]);
    }

}
