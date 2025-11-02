<?php

namespace App\Repositories;

use App\Models\Balance;
use App\Models\Transaction;

interface BalanceRepositoryInterface
{
    public function getBalanceByUserId(int $userId): ?Balance;

    public function createOrUpdateBalance(int $userId, float $amount): Balance;

    public function createTransaction(int $userId, string $status, float $amount, ?string $comment): Transaction;

    public function transferFunds(int $fromUserId, int $toUserId, float $amount): array;
}
