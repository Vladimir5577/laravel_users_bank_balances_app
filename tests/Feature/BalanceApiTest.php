<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Balance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_deposit_money_to_a_user_account()
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ];

        $response = $this->postJson('/api/deposit', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('balances', ['user_id' => $user->id, 'balance' => 500.00]);
    }

    #[Test]
    public function it_cannot_deposit_to_non_existent_user()
    {
        $data = [
            'user_id' => 999, // Некорректный ID пользователя
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ];

        $response = $this->postJson('/api/deposit', $data);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_withdraw_money_from_user_account()
    {
        $user = User::factory()->create();
        $balance = Balance::create(['user_id' => $user->id, 'balance' => 500.00]);

        $data = [
            'user_id' => $user->id,
            'amount' => 200.00,
            'comment' => 'Покупка подписки',
        ];

        $response = $this->postJson('/api/withdraw', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('balances', ['user_id' => $user->id, 'balance' => 300.00]);
    }

    #[Test]
    public function it_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        Balance::create(['user_id' => $user->id, 'balance' => 100.00]);

        $data = [
            'user_id' => $user->id,
            'amount' => 200.00,
            'comment' => 'Покупка подписки',
        ];

        $response = $this->postJson('/api/withdraw', $data);

        $response->assertStatus(409);
    }

    #[Test]
    public function it_can_transfer_money_between_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Balance::create(['user_id' => $user1->id, 'balance' => 500.00]);

        $data = [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 100.00,
            'comment' => 'Перевод другу',
        ];

        $response = $this->postJson('/api/transfer', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('balances', ['user_id' => $user1->id, 'balance' => 400.00]);
        $this->assertDatabaseHas('balances', ['user_id' => $user2->id, 'balance' => 100.00]);
    }

    #[Test]
    public function it_cannot_transfer_more_than_balance()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Balance::create(['user_id' => $user1->id, 'balance' => 100.00]);

        $data = [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 200.00,
            'comment' => 'Перевод другу',
        ];

        $response = $this->postJson('/api/transfer', $data);

        $response->assertStatus(409);
    }

    #[Test]
    public function it_can_get_user_balance()
    {
        $user = User::factory()->create();
        Balance::create(['user_id' => $user->id, 'balance' => 500.00]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson(['user_id' => $user->id, 'balance' => 500.00]);
    }
}
