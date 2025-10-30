<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Transaction;
use App\Validations\UserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    // Пополнение счета
    public function deposit(Request $request, UserValidation $userValidation)
    {
        $userValidation->validateRequest($request->all());

        DB::beginTransaction();

        try {
            $balance = Balance::firstOrCreate(['user_id' => $request->user_id]);
            $balance->balance += $request->amount;
            $balance->save();

            Transaction::create([
                'user_id' => $request->user_id,
                'status' => 'deposit',
                'amount' => $request->amount,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return response()->json(['balance' => $balance->balance], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при пополнении'], 500);
        }
    }

    // Снятие средств
    public function withdraw(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $balance = Balance::where('user_id', $request->user_id)->first();

            if (!$balance || $balance->balance < $request->amount) {
                return response()->json(['error' => 'Недостаточно средств'], 409);
            }

            $balance->balance -= $request->amount;
            $balance->save();

            Transaction::create([
                'user_id' => $request->user_id,
                'status' => 'withdraw',
                'amount' => $request->amount,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return response()->json(['balance' => $balance->balance], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при снятии'], 500);
        }
    }

    // Перевод между пользователями
    public function transfer(Request $request)
    {
        $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $fromBalance = Balance::where('user_id', $request->from_user_id)->first();
            $toBalance = Balance::where('user_id', $request->to_user_id)->first();

            if (!$fromBalance || $fromBalance->balance < $request->amount) {
                return response()->json(['error' => 'Недостаточно средств'], 409);
            }

            // Списываем средства с отправителя
            $fromBalance->balance -= $request->amount;
            $fromBalance->save();

            // Добавляем средства получателю
            if (!$toBalance) {
                $toBalance = Balance::create(['user_id' => $request->to_user_id, 'balance' => 0]);
            }
            $toBalance->balance += $request->amount;
            $toBalance->save();

            // Записываем транзакции
            Transaction::create([
                'user_id' => $request->from_user_id,
                'status' => 'transfer_out',
                'amount' => $request->amount,
                'comment' => $request->comment,
            ]);

            Transaction::create([
                'user_id' => $request->to_user_id,
                'status' => 'transfer_in',
                'amount' => $request->amount,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return response()->json([
                'from_balance' => $fromBalance->balance,
                'to_balance' => $toBalance->balance,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при переводе'], 500);
        }
    }

    // Получение баланса
    public function getBalance($user_id)
    {
        $balance = Balance::where('user_id', $user_id)->first();

        if (!$balance) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        return response()->json(['user_id' => $user_id, 'balance' => $balance->balance], 200);
    }
}
