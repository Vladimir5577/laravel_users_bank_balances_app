<?php

namespace App\Rules;

use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserExists implements ValidationRule
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string, \Closure): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Let 'required' rule handle empty values
        }
        
        $user = $this->userService->getById($value);
        
        if (!$user) {
            $fail('User not found.');
        }
    }
}

