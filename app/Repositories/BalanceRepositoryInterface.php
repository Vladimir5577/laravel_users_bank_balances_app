<?php

namespace App\Repositories;

interface BalanceRepositoryInterface
{
    public function getBalanceByUserId($userId);

    public function createOrUpdateBalance($userId, $amount);

    // Transfer funds between two users
    public function transferFunds($fromUserId, $toUserId, $amount);
}
