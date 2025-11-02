<?php

namespace App\Repositories;

use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BalanceRepository implements BalanceRepositoryInterface
{
    public function getBalanceByUserId(int $userId): ?Balance
    {
        return Balance::where('user_id', $userId)->first();
    }

    public function createOrUpdateBalance(int $userId, float $amount): Balance
    {
        DB::beginTransaction();

        try {
            $balance = Balance::firstOrCreate(['user_id' => $userId]);
            $balance->balance += $amount;
            $balance->save();

            DB::commit();

            return $balance;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createTransaction(int $userId, string $status, float $amount, ?string $comment): Transaction
    {
        return Transaction::create([
            'user_id' => $userId,
            'status' => $status,
            'amount' => $amount,
            'comment' => $comment,
        ]);
    }

    public function transferFunds(int $fromUserId, int $toUserId, float $amount): array
    {
        DB::beginTransaction();

        try {
            $fromBalance = $this->getBalanceByUserId($fromUserId);
            $toBalance = $this->getBalanceByUserId($toUserId);

            if (!$fromBalance) {
                throw new ModelNotFoundException("User with id = $fromUserId do not nave a balance.", 404);
            }

            if (!$toBalance) {
                $toBalance = Balance::create(['user_id' => $toUserId, 'balance' => 0]);
            }

            if ($fromBalance->balance < $amount) {
                throw new \Exception('Insufficient funds', 409);
            }

            $fromBalance->balance -= $amount;
            $fromBalance->save();

            $toBalance->balance += $amount;
            $toBalance->save();

            $this->createTransaction($fromUserId, 'transfer_out', $amount, 'Transfer to user ' . $toUserId);
            $this->createTransaction($toUserId, 'transfer_in', $amount, 'Transfer from user ' . $fromUserId);

            DB::commit();

            return ['from_balance' => $fromBalance->balance, 'to_balance' => $toBalance->balance];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
