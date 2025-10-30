<?php

namespace App\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserValidation
{
    /**
     * Validate the API request and return custom error responses.
     *
     * @param  array  $data
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateRequest(array $data)
    {
        // Define your validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ];

        // Custom error messages
        $messages = [
            'user_id.required' => 'The user ID field is mandatory.',
            'user_id.exists' => 'The selected user ID is invalid.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a numeric value.',
            'amount.min' => 'The amount must be at least 0.01.',
            'comment.string' => 'The comment must be a string.',
        ];

        // Create a validator instance
        $validator = Validator::make($data, $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            $this->handleValidationFailure($validator);
        }
    }

    /**
     * Handle the validation failure and return custom error responses.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function handleValidationFailure($validator)
    {
        $errors = $validator->errors();
        $firstError = $firstError = $errors->first();

        // Default status code
        $statusCode = 422;

        // Check if the error is due to 'required' (user_id missing)
        if (str_contains($firstError, 'The user ID field is mandatory')) {
            $statusCode = 422; // Unprocessable Entity if the field is required but missing
        }
        // Check if the error is due to 'exists' rule (user_id not found in DB)
        elseif (str_contains($firstError, 'The selected user ID is invalid')) {
            $statusCode = 404; // Not Found if user_id doesn't exist in the DB
        }

        // Throw the ValidationException with a custom status code
        throw new ValidationException($validator, response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], $statusCode));
    }
}
