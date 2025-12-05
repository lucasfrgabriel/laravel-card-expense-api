<?php

namespace Tests\Feature;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
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
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'type',
                'cards',
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', 1)
                ->where('name', $userData['name'])
                ->where('email', $userData['email'])
                ->where('type', $userData['type'])
                ->where('cards', [])
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
                '*' => [
                    'id',
                    'name',
                    'email',
                    'type',
                    'cards',
                ]
            ]);
    }

    public function test_comum_type_cannot_view_all_users(): void
    {
        User::factory(2)->create();
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_admin_type_can_view_any_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'type',
                'cards',
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $user->id)
                ->where('name', $user->name)
                ->where('email', $user->email)
                ->where('type', $user->type)
                ->where('cards', [])
            );
    }

    public function test_comum_type_can_view_own_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'type',
                'cards',
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $user->id)
                ->where('name', $user->name)
                ->where('email', $user->email)
                ->where('type', $user->type)
                ->where('cards', [])
            );
    }

    public function test_comum_type_cannot_view_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->getJson('/api/users/' . $otherUser->id);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'This action is unauthorized.',
            ]);
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

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_admin_type_can_update_any_user(): void
    {
        $admin = User::factory()->create(['type' => UserTypeEnum::Admin]);
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $response = $this->actingAs($admin)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $user->id, $userNewData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'type',
                'cards',
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $user->id)
                ->where('name', $userNewData['name'])
                ->where('email', $user->email)
                ->where('type', $user->type)
                ->where('cards', [])
            );
    }

    public function test_comum_type_can_update_own_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $user->id, $userNewData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'type',
                'cards',
            ])
            ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $user->id)
                ->where('name', $userNewData['name'])
                ->where('email', $user->email)
                ->where('type', $user->type)
                ->where('cards', [])
            );
    }

    public function test_comum_type_cannot_update_other_user(): void
    {
        $user = User::factory()->create(['type' => UserTypeEnum::Comum]);
        $otherUser = User::factory()->create(['type' => UserTypeEnum::Comum]);

        $userNewData = ['name' => 'Nome atualizado'];

        $response = $this->actingAs($user)
            ->withSession(['banned' => false])
            ->patchJson('/api/users/' . $otherUser->id, $userNewData);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'This action is unauthorized.',
            ]);
    }

}
