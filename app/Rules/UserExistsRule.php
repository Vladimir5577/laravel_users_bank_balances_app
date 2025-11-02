<?php

namespace App\Rules;

use Closure;
use App\Services\UserService;
use Illuminate\Contracts\Validation\ValidationRule;

class UserExistsRule implements ValidationRule
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
            return;
        }

        $user = $this->userService->getById($value);

        if (!$user) {
            $fail('User not found.');
        }
    }
}

