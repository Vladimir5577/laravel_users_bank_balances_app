<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceService;
use App\Validations\BalanceRequestValidation;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly BalanceRequestValidation $userValidation,
    ) {
    }

    public function deposit(Request $request)
    {
        $this->userValidation->validateDepositRequest($request->all());

        try {
            $balance = $this->balanceService->deposit($request->user_id, $request->amount, $request->comment);
            return response()->json(['balance' => $balance->balance], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при пополнении'], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $this->userValidation->validateWithdrawRequest($request->all());

        try {
            $balance = $this->balanceService->withdraw($request->user_id, $request->amount, $request->comment);
            return response()->json(['balance' => $balance->balance], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Недостаточно средств'], 409);
        }
    }

    // Transfer money between users
    public function transfer(Request $request)
    {
        $this->userValidation->validateTransferRequest($request->all());

        try {
            $transfer = $this->balanceService->transfer(
                $request->from_user_id,
                $request->to_user_id,
                $request->amount,
                $request->comment
            );

            return response()->json($transfer, 200);
        } catch (\Exception $e) {
            if ($e->getCode() !== 500) {
                return response()->json(['error' => $e->getMessage()], $e->getCode());
            }
            return response()->json(['error' => 'Ошибка при переводе'], 500);
        }
    }

    public function getBalance($user_id)
    {
        $balance = $this->balanceService->getBalance($user_id);

        if (!$balance) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        return response()->json(['user_id' => $user_id, 'balance' => $balance->balance], 200);
    }
}
