<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ){}
    public function getById($id): User|null
    {
        return $this->userRepository->getById($id);
    }
}
