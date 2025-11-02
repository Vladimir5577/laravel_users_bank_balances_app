<?php

namespace App\Validations;

use App\Rules\UserExistsRule;
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
            'user_id' => ['required', new UserExistsRule($this->userService)],
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
    public function validateWithdrawRequest(array $data)
    {
        $rules = [
            'user_id' => ['required', new UserExistsRule($this->userService)],
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
            'from_user_id' => ['required', new UserExistsRule($this->userService)],
            'to_user_id' => ['required', new UserExistsRule($this->userService)],
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules, self::CUSTOM_ERROR_MESSAGES);

        if ($validator->fails()) {
            $this->handleValidationFailure($validator);
        }
    }

    /**
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws ValidationException
     */
    protected function handleValidationFailure($validator)
    {
        $errors = $validator->errors();
        $statusCode = 422;

        $userIdFields = ['user_id', 'from_user_id', 'to_user_id'];

        foreach ($userIdFields as $field) {
            if ($errors->has($field)) {
                $fieldErrors = $errors->get($field);
                if (in_array(self::USER_NOT_FOUND_ERROR_MESSAGE, $fieldErrors)) {
                    $statusCode = 404;
                    break;
                }
            }
        }

        throw new ValidationException($validator, response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], $statusCode));
    }
}
