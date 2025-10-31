<?php

namespace App\Validations;

use App\Services\BalanceService;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BalanceRequestValidation
{
    const USER_NOT_FOUND_ERROR_MESSAGE = 'User not found.';
    const USER_ID_IS_REQUIRED_ERROR_MESSAGE = 'User id is required.';

    const CUSTOM_ERROR_MESSAGES  = [
        'user_id.required' => self::USER_ID_IS_REQUIRED_ERROR_MESSAGE,
        'user_id.exists' => self::USER_NOT_FOUND_ERROR_MESSAGE,
        'amount.required' => 'The amount field is required.',
        'amount.numeric' => 'The amount must be a numeric value.',
        'amount.min' => 'The amount must be at least 0.01.',
        'comment.string' => 'The comment must be a string.',
    ];

    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateDepositRequest(array $data)
    {
        $rules = [
            'user_id' => ['required', function ($attribute, $value, $fail) {
                $this->validateUserExist($attribute, $value, $fail);
            }],
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ];

        // Create a validator instance
        $validator = Validator::make($data, $rules, self::CUSTOM_ERROR_MESSAGES);

        // Check if validation fails
        if ($validator->fails()) {
            $this->handleValidationFailure($validator);
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateWithdrawRequest(array $data)
    {
        $rules = [
            'user_id' => ['required', function ($attribute, $value, $fail) {
                $this->validateUserExist($attribute, $value, $fail);
            }],
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules, self::CUSTOM_ERROR_MESSAGES);

        if ($validator->fails()) {
            $this->handleValidationFailure($validator);
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public function validateTransferRequest(array $data)
    {
        $rules = [
            'from_user_id' => ['required', function ($attribute, $value, $fail) {
                $this->validateUserExist($attribute, $value, $fail);
            }],
            'to_user_id' => ['required', function ($attribute, $value, $fail) {
                $this->validateUserExist($attribute, $value, $fail);
            }],
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules, self::CUSTOM_ERROR_MESSAGES);

        if ($validator->fails()) {
            $this->handleValidationFailure($validator);
        }
    }

    /**
     * @param $validator
     * @return mixed
     * @throws ValidationException
     */
    protected function handleValidationFailure($validator)
    {
        $errors = $validator->errors();
        $firstError = $errors->first();

        $statusCode = 422;

        if (str_contains($firstError, self::USER_ID_IS_REQUIRED_ERROR_MESSAGE)) {
            $statusCode = 422;
        }
        elseif (str_contains($firstError, self::USER_NOT_FOUND_ERROR_MESSAGE)) {
            $statusCode = 404;
        }

        throw new ValidationException($validator, response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], $statusCode));
    }

    public function validateUserExist($attribute, $value, $fail)
    {
        $userExist = $this->userService->getById($value);
        if (!$userExist) {
            return $fail(self::USER_NOT_FOUND_ERROR_MESSAGE);
        }
    }
}
