<?php

namespace App\Repositories;

use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BalanceRepository implements BalanceRepositoryInterface
{
    public function getBalanceByUserId($userId)
    {
        return Balance::where('user_id', $userId)->first();
    }

    public function createOrUpdateBalance($userId, $amount)
    {
        $balance = Balance::firstOrCreate(['user_id' => $userId]);
        $balance->balance += $amount;
        $balance->save();

        return $balance;
    }

    public function createTransaction($userId, $status, $amount, $comment)
    {
        return Transaction::create([
            'user_id' => $userId,
            'status' => $status,
            'amount' => $amount,
            'comment' => $comment,
        ]);
    }

    public function transferFunds($fromUserId, $toUserId, $amount)
    {
        $fromBalance = $this->getBalanceByUserId($fromUserId);
        $toBalance = $this->getBalanceByUserId($toUserId);

        if (!$fromBalance) {
            throw new ModelNotFoundException("User with id = $fromUserId do not nave a balance.", 404);
        }

        if (!$toBalance) {
            // Create balance for the recipient if not exists
            $toBalance = Balance::create(['user_id' => $toUserId, 'balance' => 0]);
        }

        if ($fromBalance->balance < $amount) {
            throw new \Exception('Insufficient funds', 409);
        }

        // Perform the transfer
        $fromBalance->balance -= $amount;
        $fromBalance->save();

        $toBalance->balance += $amount;
        $toBalance->save();

        // Create transactions
        $this->createTransaction($fromUserId, 'transfer_out', $amount, 'Transfer to user ' . $toUserId);
        $this->createTransaction($toUserId, 'transfer_in', $amount, 'Transfer from user ' . $fromUserId);

        return ['from_balance' => $fromBalance->balance, 'to_balance' => $toBalance->balance];
    }
}
