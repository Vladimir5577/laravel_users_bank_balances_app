<?php

namespace App\Services;

use App\Models\Balance;
use App\Repositories\BalanceRepository;

class BalanceService
{
    public function __construct(
        private readonly BalanceRepository $balanceRepository
    ) {
    }

    public function getBalanceByUserId($userId)
    {
        return $this->balanceRepository->getBalanceByUserId($userId);
    }

    public function deposit($userId, $amount, $comment = null)
    {
        $balance = $this->balanceRepository->createOrUpdateBalance($userId, $amount);

        $this->balanceRepository->createTransaction($userId, 'deposit', $amount, $comment);

        return $balance;
    }

    public function withdraw($userId, $amount, $comment = null)
    {
        $balance = $this->balanceRepository->getBalanceByUserId($userId);

        if (!$balance || $balance->balance < $amount) {
            throw new \Exception('Insufficient funds', 409);
        }

        $balance->balance -= $amount;
        $balance->save();

        $this->balanceRepository->createTransaction($userId, 'withdraw', $amount, $comment);

        return $balance;
    }

    public function transfer($fromUserId, $toUserId, $amount, $comment = null)
    {
        if ($fromUserId == $toUserId) {
            throw new \Exception('You can not transfer to yourself', 409);
        }
        return $this->balanceRepository->transferFunds($fromUserId, $toUserId, $amount);
    }

    public function getBalance($userId)
    {
        return $this->balanceRepository->getBalanceByUserId($userId);
    }
}
